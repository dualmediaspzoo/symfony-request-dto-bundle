<?php

namespace DualMedia\DtoRequestBundle\Service\Http;

use DualMedia\DtoRequestBundle\Interface\Attribute\HttpActionInterface;
use DualMedia\DtoRequestBundle\Interface\Http\ActionValidatorInterface;

class ActionValidatorService implements ActionValidatorInterface
{
    /**
     * @var iterable<ActionValidatorInterface>
     */
    private iterable $validators;

    /**
     * @param \IteratorAggregate<array-key, ActionValidatorInterface> $iterator
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function __construct(
        \IteratorAggregate $iterator
    ) {
        $this->validators = iterator_to_array($iterator->getIterator());
    }

    #[\Override]
    public function supports(
        HttpActionInterface $action,
        mixed $variable
    ): bool {
        foreach ($this->validators as $validator) {
            if ($validator->supports($action, $variable)) {
                return true;
            }
        }

        return false;
    }

    #[\Override]
    public function validate(
        HttpActionInterface $action,
        mixed $variable
    ): bool {
        foreach ($this->validators as $validator) {
            if ($validator->supports($action, $variable)) {
                return $validator->validate($action, $variable);
            }
        }

        return false;
    }
}
