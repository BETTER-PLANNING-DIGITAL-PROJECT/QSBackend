<?php

namespace App\Entity\Treasury;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Treasury\BankOperationController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\Finance\OperationCategory;
use App\Repository\Treasury\BankOperationRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BankOperationRepository::class)]
#[ORM\Table(name: 'treasury_bank_operation')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/bank-operation/{id}/',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:BankOperation:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/bank-operation',
            normalizationContext: [
                'groups' => ['get:BankOperation:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/bank-operation',
            controller: BankOperationController::class,
            denormalizationContext: [
                'groups' => ['write:BankOperation'],
            ],
            processor: SystemProcessor::class,
        ),
    ]
)]

class BankOperation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:BankOperation:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:BankOperation:collection', 'write:BankOperation'])]
    private ?BankAccount $bankAccount = null;

    #[ORM\ManyToOne]
    #[Groups(['get:BankOperation:collection', 'write:BankOperation'])]
    private ?OperationCategory $operationCategory = null;

    #[ORM\ManyToOne]
    #[Groups(['get:BankOperation:collection', 'write:BankOperation'])]
    private ?CashDesk $vault = null;

    #[ORM\ManyToOne]
    #[Groups(['get:BankOperation:collection', 'write:BankOperation'])]
    private ?User $validateBy = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:BankOperation:collection', 'write:BankOperation'])]
    private ?string $reference = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:BankOperation:collection', 'write:BankOperation'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:BankOperation:collection', 'write:BankOperation'])]
    private ?string $amount = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:BankOperation:collection', 'write:BankOperation'])]
    private ?bool $isValidate = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:BankOperation:collection', 'write:BankOperation'])]
    private ?\DateTimeImmutable $validate_At = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?Year $year = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getBankAccount(): ?BankAccount
    {
        return $this->bankAccount;
    }

    public function setBankAccount(?BankAccount $bankAccount): self
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function isIsValidate(): ?bool
    {
        return $this->isValidate;
    }

    public function setIsValidate(?bool $isValidate): self
    {
        $this->isValidate = $isValidate;

        return $this;
    }

    public function getValidateAt(): ?\DateTimeImmutable
    {
        return $this->validate_At;
    }

    public function setValidateAt(?\DateTimeImmutable $validate_At): self
    {
        $this->validate_At = $validate_At;

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
    public function getOperationCategory(): ?OperationCategory
    {
        return $this->operationCategory;
    }

    public function setOperationCategory(?OperationCategory $operationCategory): self
    {
        $this->operationCategory = $operationCategory;

        return $this;
    }

    public function getVault(): ?CashDesk
    {
        return $this->vault;
    }

    public function setVault(?CashDesk $vault): self
    {
        $this->vault = $vault;

        return $this;
    }

    public function getValidateBy(): ?User
    {
        return $this->validateBy;
    }

    public function setValidateBy(?User $validateBy): self
    {
        $this->validateBy = $validateBy;

        return $this;
    }
}
