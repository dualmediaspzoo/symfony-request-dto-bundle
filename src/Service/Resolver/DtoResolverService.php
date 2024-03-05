<?php

namespace DualMedia\DtoRequestBundle\Service\Resolver;

use DualMedia\DtoRequestBundle\Attributes\Dto\ProvideValidationGroups;
use DualMedia\DtoRequestBundle\Constraints as DtoAssert;
use DualMedia\DtoRequestBundle\Exception\Dynamic\ParameterNotSupportedException;
use DualMedia\DtoRequestBundle\Exception\Type\InvalidTypeCountException;
use DualMedia\DtoRequestBundle\Interfaces\Attribute\FindComplexInterface;
use DualMedia\DtoRequestBundle\Interfaces\Attribute\FindInterface;
use DualMedia\DtoRequestBundle\Interfaces\DtoInterface;
use DualMedia\DtoRequestBundle\Interfaces\Dynamic\ResolverServiceInterface;
use DualMedia\DtoRequestBundle\Interfaces\Entity\ComplexLoaderServiceInterface;
use DualMedia\DtoRequestBundle\Interfaces\Entity\ProviderServiceInterface;
use DualMedia\DtoRequestBundle\Interfaces\Http\ActionValidatorInterface;
use DualMedia\DtoRequestBundle\Interfaces\Resolver\DtoResolverInterface;
use DualMedia\DtoRequestBundle\Interfaces\Resolver\DtoTypeExtractorInterface;
use DualMedia\DtoRequestBundle\Interfaces\Validation\GroupServiceInterface;
use DualMedia\DtoRequestBundle\Interfaces\Validation\TypeValidationInterface;
use DualMedia\DtoRequestBundle\Model\Type\Dto as DtoTypeModel;
use DualMedia\DtoRequestBundle\Model\Type\Property as PropertyTypeModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccessorBuilder;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @template T of DtoInterface
 *
 * @implements DtoResolverInterface<T>
 */
class DtoResolverService implements DtoResolverInterface
{
    private PropertyAccessorInterface $propertyAccessor;

    public function __construct(
        private readonly TypeValidationInterface $validationHelper,
        private readonly DtoTypeExtractorInterface $typeExtractor,
        private readonly ProviderServiceInterface $providerService,
        private readonly GroupServiceInterface $groupService,
        private readonly ComplexLoaderServiceInterface $complexLoaderService,
        private readonly ResolverServiceInterface $resolverService,
        private readonly ActionValidatorInterface $actionValidator,
        private readonly ValidatorInterface $validator
    ) {
        $this->propertyAccessor = (new PropertyAccessorBuilder())
            ->enableExceptionOnInvalidPropertyPath()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();
    }

    /**
     * @param class-string<T> $class
     *
     * @return T
     *
     * @throws InvalidTypeCountException
     * @throws ParameterNotSupportedException
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function resolve(
        Request $request,
        string $class
    ): DtoInterface {
        /** @noinspection PhpUnhandledExceptionInspection */
        $model = $this->typeExtractor->extract(new \ReflectionClass($class));

        /** @var T $object */
        $object = $this->internalResolve($request, $class, $model);

        $list = $this->validator->validate(
            $object,
            null,
            $this->groupService->provideGroups(
                $request,
                $object,
                array_map(
                    fn (ProvideValidationGroups $a) => $a->provider,
                    $model->getDtoAttributes(ProvideValidationGroups::class)
                )
            )
        );
        $this->fixAndAddConstraintPaths($object, $list, $model);

        return $object;
    }

    /**
     * @phpstan-ignore-next-line
     */
    private function fixAndAddConstraintPaths(
        DtoInterface $dto,
        ConstraintViolationListInterface $list,
        DtoTypeModel $model
    ): void {
        if (!$list->count()) {
            return;
        }

        /** @var ConstraintViolationInterface $violation */
        foreach ($list as $violation) {
            if ('' !== $violation->getPropertyPath()) {
                $propPath = new PropertyPath($violation->getPropertyPath());

                // no replacement is needed, object was pre-fixed
                if (!isset($model[$propPath->getElement(0)])) {
                    $dto->getConstraintViolationList()->add($violation);

                    continue;
                }

                $path = $model[$propPath->getElement(0)]->fixPropertyPath($propPath);

                // no replacement is needed
                if ($path === $violation->getPropertyPath()) {
                    $dto->getConstraintViolationList()->add($violation);

                    continue;
                }
            } else {
                $path = 'root'; // special case
            }

            $dto->getConstraintViolationList()->add(new ConstraintViolation(
                $violation->getMessage(),
                $violation->getMessageTemplate(),
                $violation->getParameters(),
                $violation->getRoot(),
                $path,
                $violation->getInvalidValue(),
                $violation->getPlural(),
                $violation->getCode(),
                method_exists($violation, 'getConstraint') ? $violation->getConstraint() : null,
                method_exists($violation, 'getCause') ? $violation->getCause() : null
            ));
        }
    }

    /**
     * @param class-string<T> $class
     *
     * @return T
     *
     * @throws InvalidTypeCountException
     * @throws ParameterNotSupportedException
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function internalResolve(
        Request $request,
        string $class,
        DtoTypeModel $model,
        string $parentPath = '',
        string $replaceParentPropertyPath = '',
        DtoInterface|null $parent = null
    ): DtoInterface {
        $object = new $class();
        $object->setParentDto($parent);

        /** @var PropertyTypeModel $property */
        foreach ($model as $property) {
            $propertyPath = $parentPath;
            $replacePath = $replaceParentPropertyPath;

            if (mb_strlen($property->getRealPath())) {
                $propertyPath .= '['.str_replace('.', '][', $property->getRealPath()).']'; // fixes custom deep paths
                $replacePath .= '.'.$property->getRealPath();
            }

            if (null !== $property->getFindAttribute()) {
                $this->processFind($request, $property, $object, $parentPath, $replaceParentPropertyPath);
            } elseif ($property instanceof DtoTypeModel) {
                $this->processDto($request, $property, $object, $propertyPath, ltrim($replacePath, '.'));
            } else {
                $this->processProperty($request, $property, $object, $propertyPath, ltrim($replacePath, '.'));
            }
        }

        return $object;
    }

    private function processProperty(
        Request $request,
        PropertyTypeModel $property,
        DtoInterface $dto,
        string $propertyPath,
        string $replacePath
    ): void {
        try {
            $tmp = $this->safeGetPath($request, $property, $propertyPath);
            $dto->visit($property->getName());

            // this will be modified by reference
            $values = [$replacePath => $tmp];
            $list = $this->validationHelper->validateType($values, [$replacePath => $property]);

            if (0 !== $list->count()) {
                $dto->getHighestParentDto()->getConstraintViolationList()->addAll($list);
            } else {
                $dto->{$property->getName()} = $values[$replacePath];
                $dto->preValidate($property->getName());
            }
        } catch (AccessException) {
            // noop
        } finally {
            $this->attemptToSaveHttpAction($property, $dto, $dto->{$property->getName()});
        }
    }

    /**
     * @throws ParameterNotSupportedException
     * @throws InvalidTypeCountException
     */
    private function processDto(
        Request $request,
        DtoTypeModel $property,
        DtoInterface $dto,
        string $propertyPath,
        string $replacePath
    ): void {
        /** @var class-string<T> $childClass */
        $childClass = $property->getFqcn();

        if ($property->isCollection()) {
            $dto->{$property->getName()} = [];

            try {
                $list = $this->validator->validate([
                    $replacePath => $tmp = $this->safeGetPath($request, $property, $propertyPath),
                ], new DtoAssert\ObjectCollection([
                    $replacePath => new DtoAssert\ArrayAll(),
                ]));

                if ($list->count()) {
                    $dto->getHighestParentDto()->getConstraintViolationList()->addAll($list);

                    return;
                }
                $count = count($tmp); // @phpstan-ignore-line

                for ($i = 0; $i < $count; $i++) {
                    $child = $this->internalResolve(
                        $request,
                        $childClass,
                        $property,
                        $propertyPath.'['.$i.']',
                        $replacePath.'['.$i.']',
                        $dto
                    );
                    $dto->{$property->getName()}[] = $child;

                    $child->setParentDto($dto);
                }

                $dto->visit($property->getName());
            } catch (AccessException) {
                // noop
            } finally {
                $dto->preValidate($property->getName()); // allow full dto checks
            }
        } else {
            $child = $this->internalResolve(
                $request,
                $childClass,
                $property,
                $propertyPath,
                $replacePath,
                $dto
            );

            $dto->{$property->getName()} = $child;

            $dto->visit($property->getName());
            $dto->preValidate($property->getName());
        }
    }

    /**
     * @throws ParameterNotSupportedException
     */
    private function processFind(
        Request $request,
        PropertyTypeModel $property,
        DtoInterface $dto,
        string $propertyPath,
        string $replacePath
    ): void {
        /** @var FindInterface $find */
        $find = $property->getFindAttribute();

        // validate fields
        $visitedAnyRequestProps = false; // we cannot load the entity if there was no user input
        /** @var array<string, mixed> $fields */
        $fields = []; // database search values
        /** @var array<string, mixed> $dynamic */
        $dynamic = []; // dynamic service values
        /** @var array<string, string> $inputPaths */
        $inputPaths = [];

        foreach ($find->getFields() as $key => $param) {
            if (str_starts_with($param, '$')) { // dynamic param
                $dynamic[$key] = $this->resolverService->resolveParameter(ltrim($param, '$'));
            } else { // PropertyAccess
                $requestPath = $propertyPath.'['.$param.']';
                $inputPaths[$key] = ltrim($replacePath.'.'.$param, '.');

                try {
                    $tmp = null;
                    $tmp = $this->safeGetPath($request, $property, $requestPath);

                    $dto->visit($property->getName(), $key);
                    $visitedAnyRequestProps = true;
                } catch (AccessException) {
                    // noop
                } finally {
                    if ($property->isCollection()) {
                        $tmp ??= [];
                    }

                    $fields[$key] = $tmp;
                }
            }
        }

        if ($visitedAnyRequestProps) {
            $dto->visit($property->getName());
        }
        $mapped = [];
        $models = [];

        foreach ($fields as $key => $val) {
            $mapped[$inputPaths[$key]] = $val;
            $models[$inputPaths[$key]] = $property[$key];
        }
        /** @var array<string, PropertyTypeModel> $models */
        $list = $this->validationHelper->validateType($mapped, $models, true);
        $violated = false;

        if ($list->count()) {
            $dto->getHighestParentDto()->getConstraintViolationList()->addAll($list);
            $violated = true;
        }

        $finalConstraints = [];

        foreach ($inputPaths as $key => $path) {
            $finalConstraints[$path] = $property[$key]?->getConstraints() ?? [];
        }

        if (!empty($finalConstraints)) {
            $list = $this->validator->validate($mapped, new DtoAssert\ObjectCollection($finalConstraints));

            if ($list->count()) {
                $dto->getHighestParentDto()->getConstraintViolationList()->addAll($list);
                $violated = true;
            }
        }

        if ($violated) {
            $this->attemptToSaveHttpAction($property, $dto, $dto->{$property->getName()});

            return;
        }

        // reload changed values
        foreach ($fields as $key => $v) {
            $fields[$key] = $mapped[$inputPaths[$key]];
        }
        $criteria = array_merge($find->getStatic(), $fields, $dynamic);

        /** @var class-string $class */
        $class = $property->getFqcn();

        if ($find instanceof FindComplexInterface) {
            $dto->{$property->getName()} = $this->complexLoaderService->loadComplex($class, $find, $criteria);
        } elseif ($find->isCollection()) {
            $dto->{$property->getName()} = $this->providerService->getProvider(
                $class,
                $find->getProviderId()
            )->findBy(
                $criteria,
                $find->getOrderBy()
            );
        } else {
            $dto->{$property->getName()} = $this->providerService->getProvider(
                $class,
                $find->getProviderId()
            )->findOneBy(
                $criteria,
                $find->getOrderBy()
            );
        }

        $this->attemptToSaveHttpAction($property, $dto, $dto->{$property->getName()});
    }

    private function attemptToSaveHttpAction(
        PropertyTypeModel $property,
        DtoInterface $dto,
        mixed $variable
    ): void {
        if (null === ($action = $property->getHttpAction())
            || !$this->actionValidator->validate($action, $variable)) {
            return;
        }

        $dto->getHighestParentDto()->setHttpAction($action);
    }

    /**
     * @return mixed|null
     */
    private function safeGetPath(
        Request $request,
        PropertyTypeModel $property,
        string $propertyPath
    ): mixed {
        if ('' === $propertyPath) {
            return $request->{$property->getBag()->bag->value}->all();
        }

        if ($property->getBag()->bag->isHeaders()) {
            $propertyPath = mb_strtolower($propertyPath);
        }

        $value = $this->propertyAccessor->getValue($request->{$property->getBag()->bag->value}->all(), $propertyPath);

        if (!$property->getBag()->bag->isHeaders()) {
            return $value;
        }

        return $value[0] ?? null; // special header handling @phpstan-ignore-line
    }
}
