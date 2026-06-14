<?php

declare(strict_types=1);

namespace App\UI\Controller\Admin;

use App\Domain\Authorization\Entity\Permission;
use App\Domain\Integration\Application\Pipeline\SaveIntegrationSettings\SaveIntegrationSettingsCommand;
use App\Domain\Integration\Port\IntegrationInterface;
use App\Domain\System\Repository\SystemSettingRepositoryInterface;
use App\Infrastructure\Pipeline\PipelineProcessor;
use App\UI\Controller\AppController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Permission::SystemView->value)]
#[Route('/admin/system/integrations', name: 'app_admin_system_integration_')]
final class IntegrationController extends AppController
{
    /** @var array<string, IntegrationInterface> */
    private array $integrations;

    public function __construct(
        #[AutowireIterator('app.integration')] iterable $integrations,
        private readonly SystemSettingRepositoryInterface $settings,
        #[AutowireIterator('app.integration.save')] private readonly iterable $saveHandlers,
    ) {
        foreach ($integrations as $integration) {
            $this->integrations[$integration->getKey()] = $integration;
        }
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        $data = [];
        foreach ($this->integrations as $key => $integration) {
            $enabled = $this->settings->get('INTEGRATION_' . strtoupper($key) . '_ENABLED', '0') === '1';
            $fields  = [];
            foreach ($integration->getFields() as $settingKey => $field) {
                $stored          = $this->settings->get($settingKey) ?? $_ENV[$settingKey] ?? '';
                $fields[$settingKey] = [
                    'label'    => $field->label,
                    'secret'   => $field->secret,
                    'hasValue' => $stored !== '',
                    'value'    => $field->secret ? '' : $stored,
                ];
            }
            $data[$key] = [
                'integration' => $integration,
                'enabled'     => $enabled,
                'fields'      => $fields,
            ];
        }

        return $this->render('views/admin/system/index.html.twig', [
            'section'      => 'integrations',
            'integrations' => $data,
        ]);
    }

    #[IsGranted(Permission::SystemManage->value)]
    #[Route('/{key}/save', name: 'save', methods: ['POST'])]
    public function save(string $key, Request $request): Response
    {
        if (!isset($this->integrations[$key])) {
            throw $this->createNotFoundException();
        }

        $integration = $this->integrations[$key];
        $enabled     = $request->request->getBoolean('enabled');
        $posted      = $request->request->all('settings');

        $values = [];
        foreach (array_keys($integration->getFields()) as $settingKey) {
            $values[$settingKey] = isset($posted[$settingKey]) && is_string($posted[$settingKey])
                ? trim($posted[$settingKey])
                : '';
        }

        $command = new SaveIntegrationSettingsCommand($integration, $enabled, $values);
        new PipelineProcessor($this->saveHandlers)->run($command);

        $this->addFlash('success', $integration->getName() . ' settings saved.');

        return $this->redirectToRoute('app_admin_system_integration_index');
    }
}
