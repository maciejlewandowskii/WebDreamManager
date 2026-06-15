<?php

declare(strict_types=1);

namespace App\UI\Controller\Project;

use App\Domain\Identity\Entity\User;
use App\Domain\Logging\Application\LoggerService;
use App\Domain\Logging\Entity\LogLevel;
use App\Domain\Project\Entity\Project;
use DateTimeImmutable;
use DirectoryIterator;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/projects/{projectId}/files', name: 'app_project_files_')]
final class FileManagerController extends AbstractController
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/var/project_files')] private readonly string $filesBaseDir,
        private readonly LoggerService $logger,
    ) {}

    #[Route('', name: 'base', methods: ['OPTIONS'])]
    public function base(): Response
    {
        return new Response();
    }

    #[Route('/files', name: 'list', methods: ['GET'])]
    public function list(#[MapEntity(id: 'projectId')] Project $project, Request $request): Response
    {
        $basePath = $this->ensureProjectPath($project->getId());
        $search   = (string) $request->query->get('text', '');

        return $this->json($this->listDirectory($basePath, '/', $search));
    }

    #[Route('/info', name: 'info', methods: ['GET'])]
    public function info(#[MapEntity(id: 'projectId')] Project $project): Response
    {
        $path  = $this->ensureProjectPath($project->getId());
        $total = (int) (disk_total_space($path) ?: 0);
        $free  = (int) (disk_free_space($path) ?: 0);

        return $this->json([
            'stats' => [
                'total'     => $total,
                'used'      => $total - $free,
                'available' => $free,
            ],
        ]);
    }

    #[Route('/upload', name: 'upload', methods: ['POST'])]
    public function upload(#[MapEntity(id: 'projectId')] Project $project, Request $request): Response
    {
        $file = $request->files->get('file');
        if (!$file instanceof UploadedFile) {
            return $this->json(['error' => 'No file uploaded'], 400);
        }

        $parentId   = (string) $request->query->get('id', '/');
        $basePath   = $this->ensureProjectPath($project->getId());
        $parentPath = $this->resolveSafePath($basePath, $parentId);

        $name = basename((string) ($request->request->get('name') ?: $file->getClientOriginalName()));
        if (file_exists($parentPath . '/' . $name)) {
            $name = $this->uniqueName($parentPath, $name);
        }

        $file->move($parentPath, $name);
        $id = $this->pathToId($basePath, $parentPath . '/' . $name);

        $actor = $this->getUser();
        $this->logger->userAction(LogLevel::Info, 'File uploaded: ' . $name . ' in project ' . $project->getName(), $actor instanceof User ? $actor->getId() : null, $actor instanceof User ? $actor->getFullName() : null, 'projects', ['project_id' => $project->getId(), 'file' => $name]);

        return $this->json(['result' => ['id' => $id, 'name' => $name]]);
    }

    #[Route('/files/{path<.*>}', name: 'create', methods: ['POST'])]
    public function create(#[MapEntity(id: 'projectId')] Project $project, string $path, Request $request): Response
    {
        $body     = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        /** @var array{name?: string, type?: string} $body */
        $parentId = $path === '' ? '/' : $this->decodePath($path);
        $name     = basename($body['name'] ?? '');
        $type     = $body['type'] ?? 'folder';

        if ($name === '') {
            return $this->json(['error' => 'Name is required'], 400);
        }

        $basePath   = $this->ensureProjectPath($project->getId());
        $parentPath = $this->resolveSafePath($basePath, $parentId);
        $targetPath = $parentPath . '/' . $name;

        if ($type === 'folder') {
            if (!is_dir($targetPath) && (!mkdir($targetPath, 0755, true) && !is_dir($targetPath))) {
                return $this->json(['error' => 'Failed to create folder'], 500);
            }
        } else {
            file_put_contents($targetPath, '');
        }

        $id = $this->pathToId($basePath, $targetPath);

        $actor = $this->getUser();
        $this->logger->userAction(LogLevel::Info, ($type === 'folder' ? 'Folder' : 'File') . ' created: ' . $name . ' in project ' . $project->getName(), $actor instanceof User ? $actor->getId() : null, $actor instanceof User ? $actor->getFullName() : null, 'projects', ['project_id' => $project->getId(), 'name' => $name, 'type' => $type]);

        return $this->json(['result' => ['id' => $id, 'name' => $name]]);
    }

    #[Route('/files/{path<.*>}', name: 'update', methods: ['PUT'])]
    public function update(#[MapEntity(id: 'projectId')] Project $project, string $path, Request $request): Response
    {
        $body      = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        /** @var array{operation?: string, target?: string, ids?: string[], name?: string} $body */
        $operation = $body['operation'] ?? '';
        $basePath  = $this->ensureProjectPath($project->getId());

        if ($operation === 'move') {
            return $this->doMove($basePath, $body);
        }
        if ($operation === 'copy') {
            return $this->doCopy($basePath, $body);
        }

        $fsPath = $this->resolveSafePath($basePath, $this->decodePath($path));

        return match ($operation) {
            'rename' => $this->doRename($basePath, $fsPath, $body),
            default  => $this->json(['error' => "Unknown operation '$operation'"], 400),
        };
    }

    #[Route('/files', name: 'delete', methods: ['DELETE'])]
    public function delete(#[MapEntity(id: 'projectId')] Project $project, Request $request): Response
    {
        $body     = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        /** @var array{ids?: string[]} $body */
        $basePath = $this->ensureProjectPath($project->getId());

        $actor = $this->getUser();
        foreach ($body['ids'] ?? [] as $id) {
            if ($this->isProtectedInvoicePath($id)) {
                return $this->json(['error' => 'Invoice PDFs cannot be deleted via the file manager.'], 403);
            }
            $fsPath = $this->resolveSafePath($basePath, $id);
            $this->removeRecursive($fsPath);
            $this->logger->userAction(LogLevel::Info, 'File/folder deleted: ' . $id . ' in project ' . $project->getName(), $actor instanceof User ? $actor->getId() : null, $actor instanceof User ? $actor->getFullName() : null, 'projects', ['project_id' => $project->getId(), 'path' => $id]);
        }

        return $this->json(['result' => true]);
    }

    #[Route('/files/{path<.+>}', name: 'read', methods: ['GET'], priority: -1)]
    public function read(#[MapEntity(id: 'projectId')] Project $project, string $path, Request $request): Response
    {
        $basePath = $this->ensureProjectPath($project->getId());
        $fsPath   = $this->resolveSafePath($basePath, $this->decodePath($path));
        $search   = (string) $request->query->get('text', '');

        if (is_dir($fsPath)) {
            return $this->json($this->listDirectory($basePath, $this->pathToId($basePath, $fsPath), $search));
        }

        if (!is_file($fsPath)) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $disposition = $request->query->has('download')
            ? ResponseHeaderBag::DISPOSITION_ATTACHMENT
            : ResponseHeaderBag::DISPOSITION_INLINE;

        $response = new BinaryFileResponse($fsPath);
        $response->setContentDisposition($disposition, basename($fsPath));

        return $response;
    }

    /** @param array<string, mixed> $body */
    private function doRename(string $basePath, string $fsPath, array $body): Response
    {
        $id = $this->pathToId($basePath, $fsPath);
        if ($this->isProtectedInvoicePath($id)) {
            return $this->json(['error' => 'Invoice PDFs cannot be renamed via the file manager.'], 403);
        }

        $newName = basename(is_string($body['name'] ?? null) ? $body['name'] : '');
        if ($newName === '') {
            return $this->json(['error' => 'Name is required'], 400);
        }
        $newPath = dirname($fsPath) . '/' . $newName;
        if (!rename($fsPath, $newPath)) {
            return $this->json(['error' => 'Rename failed'], 500);
        }
        return $this->json(['result' => ['id' => $this->pathToId($basePath, $newPath), 'name' => $newName]]);
    }

    private function isProtectedInvoicePath(string $relativePath): bool
    {
        return str_contains($relativePath, '/invoices/') || str_contains($relativePath, '/quotes/');
    }

    /** @param array<string, mixed> $body */
    private function doMove(string $basePath, array $body): Response
    {
        $targetPath = $this->resolveSafePath($basePath, is_string($body['target'] ?? null) ? $body['target'] : '/');
        $results    = [];
        $rawIds = $body['ids'] ?? null;
        foreach (is_array($rawIds) ? $rawIds : [] as $id) {
            if (!is_string($id)) {
                continue;
            }
            $src  = $this->resolveSafePath($basePath, $id);
            $dest = $targetPath . '/' . basename($src);
            rename($src, $dest);
            $results[] = ['id' => $this->pathToId($basePath, $dest), 'name' => basename($dest)];
        }
        return $this->json(['result' => $results]);
    }

    /** @param array<string, mixed> $body */
    private function doCopy(string $basePath, array $body): Response
    {
        $targetPath = $this->resolveSafePath($basePath, is_string($body['target'] ?? null) ? $body['target'] : '/');
        $results    = [];
        $rawIds = $body['ids'] ?? null;
        foreach (is_array($rawIds) ? $rawIds : [] as $id) {
            if (!is_string($id)) {
                continue;
            }
            $src  = $this->resolveSafePath($basePath, $id);
            $dest = $targetPath . '/' . basename($src);
            $this->copyRecursive($src, $dest);
            $results[] = ['id' => $this->pathToId($basePath, $dest), 'name' => basename($dest)];
        }
        return $this->json(['result' => $results]);
    }

    /** @return array<int, array<string, mixed>> */
    private function listDirectory(string $basePath, string $relativePath, string $search = ''): array
    {
        $dirPath = $relativePath === '/' ? $basePath : $basePath . '/' . ltrim($relativePath, '/');

        if (!is_dir($dirPath)) {
            return [];
        }

        $items = [];

        if ($search !== '') {
            $iter = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST,
            );
        } else {
            $iter = new DirectoryIterator($dirPath);
        }

        foreach ($iter as $item) {
            /** @var \DirectoryIterator $item */
            if ($item->isDot() || str_starts_with($item->getFilename(), '.')) {
                continue;
            }
            if ($search !== '' && stripos($item->getFilename(), $search) === false) {
                continue;
            }

            $absPath  = $item->getPathname();
            $id       = $this->pathToId($basePath, $absPath);
            $fileItem = [
                'id'   => $id,
                'name' => $item->getFilename(),
                'date' => (new DateTimeImmutable('@' . $item->getMTime()))->format('Y-m-d H:i:s'),
            ];

            if ($item->isDir()) {
                $children         = array_filter(scandir($absPath) ?: [], fn($f) => !str_starts_with($f, '.'));
                $count            = max(0, count($children) - 2);
                $fileItem['type']  = 'folder';
                $fileItem['count'] = $count;
                $fileItem['lazy']  = $count > 0;
            } else {
                $fileItem['type'] = 'file';
                $fileItem['size'] = $item->getSize();
            }

            $items[] = $fileItem;
        }

        usort($items, static function (array $a, array $b): int {
            if ($a['type'] !== $b['type']) {
                return $a['type'] === 'folder' ? -1 : 1;
            }
            return strcasecmp($a['name'], $b['name']);
        });

        return $items;
    }

    private function ensureProjectPath(string $projectId): string
    {
        $path = $this->filesBaseDir . '/' . $projectId;
        if (!is_dir($path) && (!mkdir($path, 0755, true) && !is_dir($path))) {
            throw new RuntimeException('Cannot create project files directory');
        }
        return $path;
    }

    private function resolveSafePath(string $basePath, string $relativePath): string
    {
        $full = $basePath . '/' . ltrim($relativePath, '/');
        $real = realpath($full);

        if ($real !== false) {
            $realBase = realpath($basePath);
            if ($realBase === false || !str_starts_with($real, $realBase)) {
                throw new RuntimeException('Access denied');
            }
            return $real;
        }

        // path doesn't exist yet (creating a new file) — string check
        $normalised = rtrim(str_replace(['\\', '/../', '/./'], ['/', '/', '/'], $full), '/');
        if (!str_starts_with($normalised, $basePath)) {
            throw new RuntimeException('Access denied');
        }

        return $normalised;
    }

    private function pathToId(string $basePath, string $absPath): string
    {
        $rel = str_replace('\\', '/', substr($absPath, strlen($basePath)));
        return '/' . ltrim($rel, '/');
    }

    private function decodePath(string $urlPath): string
    {
        $decoded = urldecode($urlPath);
        return str_starts_with($decoded, '/') ? $decoded : '/' . $decoded;
    }

    private function uniqueName(string $dir, string $name): string
    {
        $ext  = pathinfo($name, PATHINFO_EXTENSION);
        $base = pathinfo($name, PATHINFO_FILENAME);
        $i    = 1;
        do {
            $newName = $base . '_' . $i . ($ext !== '' ? '.' . $ext : '');
            $i++;
        } while (file_exists($dir . '/' . $newName));
        return $newName;
    }

    private function removeRecursive(string $path): void
    {
        if (is_dir($path)) {
            foreach (new DirectoryIterator($path) as $item) {
                if (!$item->isDot()) {
                    $this->removeRecursive($item->getPathname());
                }
            }
            rmdir($path);
        } elseif (is_file($path)) {
            unlink($path);
        }
    }

    private function copyRecursive(string $src, string $dst): void
    {
        if (is_dir($src)) {
            if (!is_dir($dst) && (!mkdir($dst, 0755, true) && !is_dir($dst))) {
                throw new RuntimeException('Cannot create directory during copy');
            }
            foreach (new DirectoryIterator($src) as $item) {
                if (!$item->isDot()) {
                    $this->copyRecursive($item->getPathname(), $dst . '/' . $item->getFilename());
                }
            }
        } else {
            copy($src, $dst);
        }
    }
}
