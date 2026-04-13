<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Metadata\Model\MainDto;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;

class RuntimeResolveHelper
{
    public function __construct(
        private readonly Reflector $reflector
    ) {
    }

    public function prepareForCache(
        MainDto $mainDto
    ): MainDto {
        $classNeedsResolve = !CacheUtils::isSerializable($mainDto->constraints)
            || !CacheUtils::isSerializable($mainDto->meta);
        $anyChildFlagged = false;
        $fields = $mainDto->fields;
        $fieldsChanged = false;

        foreach ($fields as $name => $field) {
            if ($field instanceof Dto) {
                if (!CacheUtils::isSerializable($field->constraints)) {
                    $fields[$name] = new Dto(
                        name: $field->name,
                        type: $field->type,
                        bag: $field->bag,
                        path: $field->path,
                        constraints: [],
                        meta: $field->meta,
                        requiresRuntimeResolve: true
                    );
                    $anyChildFlagged = true;
                    $fieldsChanged = true;
                }

                continue;
            }

            $propertyConstraintsNeedsResolve = !CacheUtils::isSerializable($field->constraints);
            $propertyMetaNeedsResolve = !CacheUtils::isSerializable($field->meta);
            $propertyNeedsResolve = $propertyConstraintsNeedsResolve || $propertyMetaNeedsResolve;
            $virtualChanged = false;
            $virtual = $field->virtual;

            foreach ($virtual as $vName => $vField) {
                if (!$vField instanceof Property) {
                    continue;
                }

                if (!CacheUtils::isSerializable($vField->constraints)) {
                    $virtual[$vName] = new Property(
                        name: $vField->name,
                        type: $vField->type,
                        bag: $vField->bag,
                        path: $vField->path,
                        coercer: $vField->coercer,
                        constraints: [],
                        virtual: $vField->virtual,
                        meta: $vField->meta,
                        requiresRuntimeResolve: true
                    );
                    $anyChildFlagged = true;
                    $virtualChanged = true;
                }
            }

            if ($propertyNeedsResolve || $virtualChanged) {
                $fields[$name] = new Property(
                    name: $field->name,
                    type: $field->type,
                    bag: $field->bag,
                    path: $field->path,
                    coercer: $field->coercer,
                    constraints: $propertyConstraintsNeedsResolve ? [] : $field->constraints,
                    virtual: $virtual,
                    meta: $propertyMetaNeedsResolve ? [] : $field->meta,
                    objectProviderServiceId: $field->objectProviderServiceId,
                    requiresRuntimeResolve: $propertyNeedsResolve
                );

                if ($propertyNeedsResolve) {
                    $anyChildFlagged = true;
                }

                $fieldsChanged = true;
            }
        }

        if (!$classNeedsResolve && !$anyChildFlagged) {
            return $mainDto;
        }

        return new MainDto(
            fields: $fieldsChanged ? $fields : $mainDto->fields,
            constraints: $classNeedsResolve ? [] : $mainDto->constraints,
            meta: $classNeedsResolve ? [] : $mainDto->meta,
            validationGroupsServiceId: $mainDto->validationGroupsServiceId,
            requiresRuntimeResolve: $classNeedsResolve,
            childRequiresRuntimeResolve: $anyChildFlagged
        );
    }

    /**
     * @param class-string<AbstractDto> $class
     */
    public function restoreRuntimeConstraints(
        string $class,
        MainDto $mainDto
    ): MainDto {
        if (!$mainDto->requiresRuntimeResolve && !$mainDto->childRequiresRuntimeResolve) {
            return $mainDto;
        }

        $classConstraints = $mainDto->constraints;
        $classMeta = $mainDto->meta;

        if ($mainDto->requiresRuntimeResolve) {
            $classConstraints = $this->reflector->reflectClassConstraints($class);
            $classMeta = $this->reflector->reflectClassMeta($class);
        }

        $fields = $mainDto->fields;

        if ($mainDto->childRequiresRuntimeResolve) {
            foreach ($fields as $name => $field) {
                $restored = $this->restoreField($class, $name, $field);

                if (null !== $restored) {
                    $fields[$name] = $restored;
                }
            }
        }

        return new MainDto(
            fields: $fields,
            constraints: $classConstraints,
            meta: $classMeta,
            validationGroupsServiceId: $mainDto->validationGroupsServiceId
        );
    }

    /**
     * @param class-string<AbstractDto> $class
     */
    private function restoreField(
        string $class,
        string $name,
        Property|Dto $field
    ): Property|Dto|null {
        if ($field instanceof Dto) {
            if (!$field->requiresRuntimeResolve) {
                return null;
            }

            return new Dto(
                name: $field->name,
                type: $field->type,
                bag: $field->bag,
                path: $field->path,
                constraints: $this->reflector->reflectPropertyConstraints($class, $name),
                meta: $field->meta
            );
        }

        $propertyNeedsRestore = $field->requiresRuntimeResolve;
        $virtual = $field->virtual;
        $virtualChanged = false;

        foreach ($virtual as $vName => $vField) {
            if (!$vField instanceof Property || !$vField->requiresRuntimeResolve) {
                continue;
            }

            $virtual[$vName] = new Property(
                name: $vField->name,
                type: $vField->type,
                bag: $vField->bag,
                path: $vField->path,
                coercer: $vField->coercer,
                constraints: $this->reflector->reflectVirtualConstraints($class, $name, $vName),
                virtual: $vField->virtual,
                meta: $vField->meta
            );
            $virtualChanged = true;
        }

        if (!$propertyNeedsRestore && !$virtualChanged) {
            return null;
        }

        return new Property(
            name: $field->name,
            type: $field->type,
            bag: $field->bag,
            path: $field->path,
            coercer: $field->coercer,
            constraints: $propertyNeedsRestore
                ? $this->reflector->reflectPropertyConstraints($class, $name)
                : $field->constraints,
            virtual: $virtual,
            meta: $propertyNeedsRestore
                ? $this->reflector->reflectPropertyMeta($class, $name)
                : $field->meta,
            objectProviderServiceId: $field->objectProviderServiceId
        );
    }
}
