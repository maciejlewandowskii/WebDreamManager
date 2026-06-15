<?php

declare(strict_types=1);

namespace App\UI\Controller\Admin;

use App\Domain\Logging\Application\Data\LogFilter;
use App\Domain\Logging\Repository\LogRepositoryInterface;
use App\Domain\System\Application\Pipeline\TriggerWatchtowerUpdate\TriggerWatchtowerUpdateCommand;
use App\Domain\System\Application\SystemVersionService;
use App\Domain\System\Repository\SystemSettingRepositoryInterface;
use App\Domain\Authorization\Entity\Permission;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use App\Infrastructure\Pipeline\PipelineProcessor;
use App\UI\Controller\AppController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Permission::SystemView->value)]
#[Route('/admin/system', name: 'app_admin_system_')]
final class SystemController extends AppController
{
    /** @var string[] Key suffixes */
    private const array SECRET_SUFFIXES = ['SECRET', 'KEY', 'DSN', 'TOKEN'];

    /** @var array<string, array<string, string>> General settings exposed in the Configuration tab */
    private const array EDITABLE_SETTINGS = [
        'company' => [
            'COMPANY_NAME'    => 'Company Name',
            'COMPANY_ADDRESS' => 'Company Address',
        ],
    ];

    /**
     * @param iterable<PipelineHandlerInterface> $watchtowerTriggerHandlers
     */
    public function __construct(
        private readonly SystemVersionService $versionService,
        private readonly SystemSettingRepositoryInterface $settings,
        private readonly LogRepositoryInterface $logRepository,
        #[AutowireIterator('app.system.watchtower_trigger')] private readonly iterable $watchtowerTriggerHandlers,
    ) {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_admin_system_version');
    }

    #[Route('/version', name: 'version')]
    public function version(): Response
    {
        return $this->render('views/admin/system/index.html.twig', [
            'section'            => 'version',
            'current_version'    => $this->versionService->getCurrentVersion(),
            'latest_version'     => $this->versionService->getLatestVersion(),
            'update_available'   => $this->versionService->isUpdateAvailable(),
            'auto_update'        => $this->settings->get('SYS_AUTO_UPDATE', '0') === '1',
            'github_repo'        => $this->settings->get('SYS_GITHUB_REPO', ''),
            'watchtower_enabled' => $this->settings->get('INTEGRATION_WATCHTOWER_ENABLED', '0') === '1',
        ]);
    }

    #[IsGranted(Permission::SystemManage->value)]
    #[Route('/version/check', name: 'version_check', methods: ['POST'])]
    public function checkUpdate(): Response
    {
        $this->versionService->invalidateCache();

        $this->addFlash(
            $this->versionService->isUpdateAvailable() ? 'success' : 'info',
            $this->versionService->isUpdateAvailable()
                ? 'A new version is available: ' . $this->versionService->getLatestVersion()
                : 'You are running the latest version.',
        );

        return $this->redirectToRoute('app_admin_system_version');
    }

    #[IsGranted(Permission::SystemManage->value)]
    #[Route('/version/auto-update', name: 'version_auto_update', methods: ['POST'])]
    public function saveAutoUpdate(Request $request): Response
    {
        $enabled    = $request->request->getBoolean('auto_update');
        $githubRepo = trim($request->request->getString('github_repo'));

        $this->settings->set('SYS_AUTO_UPDATE', $enabled ? '1' : '0');
        $this->settings->set('SYS_GITHUB_REPO', $githubRepo !== '' ? $githubRepo : null);

        $this->addFlash('success', 'Auto-update settings saved.');

        return $this->redirectToRoute('app_admin_system_version');
    }

    #[IsGranted(Permission::SystemManage->value)]
    #[Route('/version/watchtower-trigger', name: 'version_watchtower_trigger', methods: ['POST'])]
    public function triggerWatchtowerUpdate(): Response
    {
        $url   = $this->settings->get('WATCHTOWER_URL') ?? '';
        $token = $this->settings->get('WATCHTOWER_TOKEN') ?? '';

        if ($url === '') {
            $this->addFlash('error', 'Watchtower URL is not configured.');

            return $this->redirectToRoute('app_admin_system_version');
        }

        $command = new TriggerWatchtowerUpdateCommand($url, $token);
        new PipelineProcessor($this->watchtowerTriggerHandlers)->run($command);

        $this->addFlash(
            $command->triggered ? 'success' : 'error',
            $command->triggered
                ? 'Watchtower update check triggered successfully.'
                : 'Failed to reach Watchtower. Check the URL and token.',
        );

        return $this->redirectToRoute('app_admin_system_version');
    }

    #[Route('/settings', name: 'settings')]
    public function settings(): Response
    {
        $current = [];
        foreach (self::EDITABLE_SETTINGS as $group => $keys) {
            foreach ($keys as $key => $label) {
                $isSecret = $this->isSecretKey($key);
                $stored   = $this->settings->get($key) ?? $_ENV[$key] ?? '';
                $current[$group][$key] = [
                    'label'     => $label,
                    'secret'    => $isSecret,
                    'hasValue'  => $stored !== '',
                    // never send the actual secret value to the browser
                    'value'     => $isSecret ? '' : $stored,
                ];
            }
        }

        return $this->render('views/admin/system/index.html.twig', [
            'section'      => 'settings',
            'groups'       => $current,
            'group_labels' => [
                'company' => 'Company',
            ],
        ]);
    }

    #[IsGranted(Permission::SystemManage->value)]
    #[Route('/settings', name: 'settings_save', methods: ['POST'])]
    public function settingsSave(Request $request): Response
    {
        $posted = $request->request->all('settings');

        foreach (self::EDITABLE_SETTINGS as $keys) {
            foreach (array_keys($keys) as $key) {
                if (!array_key_exists($key, $posted)) {
                    continue;
                }
                $value = is_string($posted[$key]) ? trim($posted[$key]) : '';
                if ($value === '' && $this->isSecretKey($key)) {
                    continue;
                }
                $this->settings->set($key, $value !== '' ? $value : null);
            }
        }

        $this->addFlash('success', 'Settings saved. Some changes may require a service restart to take effect.');

        return $this->redirectToRoute('app_admin_system_settings');
    }

    #[Route('/maintenance', name: 'maintenance')]
    public function maintenance(): Response
    {
        return $this->render('views/admin/system/index.html.twig', [
            'section'   => 'maintenance',
            'log_count' => $this->logRepository->countByFilter(new LogFilter()),
        ]);
    }

    private function isSecretKey(string $key): bool
    {
        return array_any(self::SECRET_SUFFIXES, static fn(string $suffix) => str_ends_with($key, $suffix));
    }
}
