<?php

declare(strict_types=1);

namespace App\UI\Component;

use Symfony\Component\Validator\Validator\ValidatorInterface;

trait DtoValidationTrait
{
    /** @var array<string, list<string>> */
    public array $validationErrors = [];

    private function validateDto(ValidatorInterface $validator, object $dto): bool
    {
        $this->validationErrors = [];
        foreach ($validator->validate($dto) as $violation) {
            $this->validationErrors[$violation->getPropertyPath()][] = (string) $violation->getMessage();
        }

        return $this->validationErrors === [];
    }

    /** @return list<string> */
    public function getErrors(string $path): array
    {
        return $this->validationErrors[$path] ?? [];
    }

    public function hasErrors(): bool
    {
        return $this->validationErrors !== [];
    }
}
