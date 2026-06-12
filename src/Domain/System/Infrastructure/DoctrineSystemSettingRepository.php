<?php

declare(strict_types=1);

namespace App\Domain\System\Infrastructure;

use App\Domain\System\Entity\SystemSetting;
use App\Domain\System\Repository\SystemSettingRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SystemSetting>
 */
class DoctrineSystemSettingRepository extends ServiceEntityRepository implements SystemSettingRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SystemSetting::class);
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $setting = $this->find($key);

        return $setting?->getValue() ?? $default;
    }

    public function set(string $key, ?string $value): void
    {
        $setting = $this->find($key);

        if ($setting === null) {
            $setting = new SystemSetting($key, $value);
            $this->getEntityManager()->persist($setting);
        } else {
            $setting->setValue($value);
        }

        $this->getEntityManager()->flush();
    }

    public function all(): array
    {
        return $this->findAll();
    }
}
