<?php

namespace App\Entity;

use App\Repository\TenantRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TenantRepository::class)
 */
class Tenant
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $applicationId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $hostname;

    /**
     * @ORM\Column(type="string", length=6)
     */
    private $backgroundColorCode;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="tenant", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fusionAuthTenantId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApplicationId(): ?string
    {
        return $this->applicationId;
    }

    public function setApplicationId(string $applicationId): self
    {
        $this->applicationId = $applicationId;

        return $this;
    }

    public function getHostname(): ?string
    {
        return $this->hostname;
    }

    public function setHostname(string $hostname): self
    {
        $this->hostname = $hostname;

        return $this;
    }

    public function getBackgroundColorCode(): ?string
    {
        return $this->backgroundColorCode;
    }

    public function setBackgroundColor(string $backgroundColorCode): self
    {
        $this->backgroundColorCode = $backgroundColorCode;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getFusionAuthTenantId(): ?string
    {
        return $this->fusionAuthTenantId;
    }

    public function setFusionAuthTenantId(string $fusionAuthTenantId): self
    {
        $this->fusionAuthTenantId = $fusionAuthTenantId;

        return $this;
    }
}