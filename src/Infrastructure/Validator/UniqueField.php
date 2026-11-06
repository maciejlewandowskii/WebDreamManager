<?php

declare(strict_types=1);

namespace App\Infrastructure\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class UniqueField extends Constraint
{
    /**
     * @param class-string  $entityClass
     * @param string[]|null $groups
     */
    public function __construct(
        public readonly string $entityClass,
        public readonly string $field,
        public readonly string $idProperty = 'id',
        public readonly string $message = 'This value is already used.',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
