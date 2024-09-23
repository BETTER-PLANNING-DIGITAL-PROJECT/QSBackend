<?php

namespace App\Entity\Partner;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Partner\PartnerCategoryRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PartnerCategoryRepository::class)]
#[ORM\Table(name: 'partner_category')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/partner-category/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:PartnerCategory:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/partner-category',
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:PartnerCategory:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/partner-category',
            denormalizationContext: [
                'groups' => ['write:PartnerCategory'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/partner-category/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:PartnerCategory'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/partner-category/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]

)]
#[UniqueEntity(
    fields: ['name'],
    message: 'This name already exist',
)]
class PartnerCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:PartnerCategory:collection', 'get:Customer:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:PartnerCategory:collection','write:PartnerCategory','get:Customer:collection'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?Year $year = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }
    public function isIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(bool $is_enable): static
    {
        $this->is_enable = $is_enable;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): static
    {
        $this->institution = $institution;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): self
    {
        $this->year = $year;

        return $this;
    }
}
