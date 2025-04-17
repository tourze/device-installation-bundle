<?php

namespace DeviceInstallationBundle\Service;

use DeviceBundle\Entity\Device;
use DeviceInstallationBundle\Entity\App;
use DeviceInstallationBundle\Entity\Installation;
use DeviceInstallationBundle\Entity\Version;
use DeviceInstallationBundle\Repository\AppRepository;
use DeviceInstallationBundle\Repository\InstallationRepository;
use DeviceInstallationBundle\Repository\VersionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Tourze\Symfony\Async\Attribute\Async;

class AppPackageService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AppRepository $appRepository,
        private readonly VersionRepository $versionRepository,
        private readonly InstallationRepository $installationRepository,
    )
    {
    }

    /**
     * 保存设备和安装包信息
     */
    #[Async]
    public function saveInstallPackages(Device $device, array $installedPackages): void
    {
        $installations = $this->installationRepository->findBy([
            'device' => $device,
        ]);
        foreach ($installations as $installation) {
            $installation->setValid(false);
            $this->installationRepository->persist($installation);
        }

        foreach ($installedPackages as $installedPackage) {
            // APP包
            $app = $this->appRepository->findOneBy([
                'packageName' => $installedPackage['packageName'],
            ]);
            if (!$app) {
                $app = new App();
                $app->setPackageName($installedPackage['packageName']);
            }
            if (!$app->getAppName()) {
                $app->setAppName($installedPackage['appName'] ?? '');
            }
            $this->entityManager->persist($app);
            $this->entityManager->flush();

            // 版本
            $versionName = $installedPackage['versionName'] ?? '';
            $versionCode = $installedPackage['versionCode'] ?? 0;
            $version = $this->versionRepository->findOneBy([
                'app' => $app,
                'versionName' => $versionName,
                'versionCode' => $versionCode,
            ]);
            if (!$version) {
                $version = new Version();
                $version->setApp($app);
                $version->setVersionName($versionName);
                $version->setVersionCode($versionCode);
            }
            $this->entityManager->persist($version);
            $this->entityManager->flush();

            $installation = $this->installationRepository->findOneBy([
                'device' => $device,
                'version' => $version,
            ]);
            if (!$installation) {
                $installation = new Installation();
                $installation->setDevice($device);
                $installation->setVersion($version);
            }
            $installation->setInstallDate($installedPackage['installDate'] ?? 0);
            $installation->setValid(true);
            $this->installationRepository->persist($installation);
        }

        $this->installationRepository->flush();
    }
}
