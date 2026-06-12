<?php

declare(strict_types=1);

namespace App\Infrastructure\Pipeline;

use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractPersistStage implements PipelineHandlerInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof PipelineCommandInterface);

        $this->em->persist($payload->getEntityToSave());
        $this->em->flush();

        return $payload;
    }
}
