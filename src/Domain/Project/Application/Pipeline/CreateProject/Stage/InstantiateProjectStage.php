<?php

declare(strict_types=1);

namespace App\Domain\Project\Application\Pipeline\CreateProject\Stage;

use App\Domain\Project\Application\Pipeline\CreateProject\CreateProjectCommand;
use App\Domain\Project\Entity\Project;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.project.create', attributes: ['priority' => 200])]
final class InstantiateProjectStage implements PipelineHandlerInterface
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateProjectCommand);
        assert($payload->data->customer !== null);

        $project = new Project($payload->data->name, $payload->data->customer);
        $payload->data->applyTo($project);
        $payload->result = $project;

        return $payload;
    }
}
