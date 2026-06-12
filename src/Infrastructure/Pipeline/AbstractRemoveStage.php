<?php

declare(strict_types=1);

namespace App\Infrastructure\Pipeline;

use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractRemoveStage implements PipelineHandlerInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof HasRemovableEntity);

        $this->em->remove($payload->getEntityToRemove());
        $this->em->flush();

        return $payload;
    }
}
