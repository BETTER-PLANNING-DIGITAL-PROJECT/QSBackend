<?php

namespace App\Entity\Product;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteSelectedResourceController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Product\AttributeValueRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AttributeValueRepository::class)]
#[ORM\Table(name: 'product_attribute_value')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/attribute-value/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:AttributeValue:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/attribute-value',
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:AttributeValue:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/attribute-value',
            denormalizationContext: [
                'groups' => ['write:AttributeValue'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/attribute-value/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:AttributeValue'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/attribute-value/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/delete/selected/attribute-value',
            controller: DeleteSelectedResourceController::class,
            openapiContext: [
                "summary" => "Restore collections of api resource",
            ],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'this name already exist',
)]
class AttributeValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:AttributeValue:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:AttributeValue:collection','write:AttributeValue'])]
    private ?Attribute $attribute = null;

    #[ORM\Column(length: 100)]
    #[Groups(['get:AttributeValue:collection','write:AttributeValue'])]
    private ?string $name = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:AttributeValue:collection','write:AttributeValue'])]
    private ?string $color = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:AttributeValue:collection','write:AttributeValue'])]
    private ?float $price = null;

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

    public function getAttribute(): ?Attribute
    {
        return $this->attribute;
    }

    public function setAttribute(?Attribute $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

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
