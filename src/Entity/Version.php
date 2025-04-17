<?php

namespace DeviceInstallationBundle\Entity;

use DeviceInstallationBundle\Repository\VersionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;

#[ORM\Entity(repositoryClass: VersionRepository::class)]
#[ORM\Table(name: 'ims_device_app_version', options: ['comment' => 'APP包'])]
#[ORM\UniqueConstraint(name: 'ims_device_app_version_idx_uniq', columns: ['app_id', 'version_name', 'version_code'])]
class Version
{
    #[ListColumn(order: -1)]
    #[ExportColumn]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[ORM\ManyToOne(inversedBy: 'versions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?App $app = null;

    #[ORM\Column(length: 60)]
    private string $versionName;

    #[ORM\Column]
    private int $versionCode;

    /**
     * @var Collection<int, Installation>
     */
    #[ORM\OneToMany(targetEntity: Installation::class, mappedBy: 'version', orphanRemoval: true)]
    private Collection $installations;

    #[IndexColumn]
    #[ListColumn(order: 98, sorter: true)]
    #[ExportColumn]
    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeInterface $createTime = null;

    public function __construct()
    {
        $this->installations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApp(): ?App
    {
        return $this->app;
    }

    public function setApp(?App $app): static
    {
        $this->app = $app;

        return $this;
    }

    public function getVersionName(): string
    {
        return $this->versionName;
    }

    public function setVersionName(string $versionName): static
    {
        $this->versionName = $versionName;

        return $this;
    }

    public function getVersionCode(): int
    {
        return $this->versionCode;
    }

    public function setVersionCode(int $versionCode): static
    {
        $this->versionCode = $versionCode;

        return $this;
    }

    /**
     * @return Collection<int, Installation>
     */
    public function getInstallations(): Collection
    {
        return $this->installations;
    }

    public function addInstallation(Installation $installation): static
    {
        if (!$this->installations->contains($installation)) {
            $this->installations->add($installation);
            $installation->setVersion($this);
        }

        return $this;
    }

    public function removeInstallation(Installation $installation): static
    {
        if ($this->installations->removeElement($installation)) {
            // set the owning side to null (unless already changed)
            if ($installation->getVersion() === $this) {
                $installation->setVersion(null);
            }
        }

        return $this;
    }

    public function setCreateTime(?\DateTimeInterface $createdAt): self
    {
        $this->createTime = $createdAt;

        return $this;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createTime;
    }
}
