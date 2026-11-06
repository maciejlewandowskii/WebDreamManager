<?php

declare(strict_types=1);

namespace App\Infrastructure\Validator;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class UniqueFieldValidator extends ConstraintValidator
{
    private readonly PropertyAccessorInterface $propertyAccessor;

    public function __construct(
        private readonly ManagerRegistry $registry,
    ) {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueField) {
            throw new UnexpectedTypeException($constraint, UniqueField::class);
        }

        if (!is_object($value)) {
            throw new UnexpectedValueException($value, 'object');
        }

        $fieldValue = $this->propertyAccessor->getValue($value, $constraint->field);
        if ($fieldValue === null || $fieldValue === '') {
            return;
        }

        $manager = $this->registry->getManagerForClass($constraint->entityClass);
        if ($manager === null) {
            return;
        }

        $existing = $manager->getRepository($constraint->entityClass)->findOneBy([$constraint->field => $fieldValue]);
        if ($existing === null) {
            return;
        }

        $ownId = $this->propertyAccessor->getValue($value, $constraint->idProperty);
        $existingId = $this->propertyAccessor->getValue($existing, 'id');
        if (is_scalar($ownId) && is_scalar($existingId) && (string) $ownId === (string) $existingId) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->atPath($constraint->field)
            ->setInvalidValue($fieldValue)
            ->addViolation();
    }
}
