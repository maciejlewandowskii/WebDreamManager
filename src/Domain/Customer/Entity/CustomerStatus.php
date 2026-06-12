<?php

declare(strict_types=1);

namespace App\Domain\Customer\Entity;

enum CustomerStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Lead = 'lead';
    case Archived = 'archived';

    public function label(): string
    {
        return match($this) {
            self::Active   => 'Active',
            self::Inactive => 'Inactive',
            self::Lead     => 'Lead',
            self::Archived => 'Archived',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active   => 'success',
            self::Inactive => 'secondary',
            self::Lead     => 'primary',
            self::Archived => 'outline',
        };
    }
}
