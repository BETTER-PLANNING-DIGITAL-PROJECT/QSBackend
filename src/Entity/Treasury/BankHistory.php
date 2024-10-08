<?php

namespace App\Entity\Treasury;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\Finance\OperationCategory;
use App\Repository\Treasury\BankHistoryRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BankHistoryRepository::class)]
#[ORM\Table(name: 'treasury_bank_history')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/bank-history',
            normalizationContext: [
                'groups' => ['get:BankHistory:collection'],
            ],
        ),
    ]
)]

class BankHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:BankHistory:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:BankHistory:collection', 'write:BankHistory'])]
    private ?BankAccount $bankAccount = null;

    #[ORM\ManyToOne]
    #[Groups(['get:BankHistory:collection', 'write:BankHistory'])]
    private ?OperationCategory $operationCategory = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:BankHistory:collection', 'write:BankHistory'])]
    private ?string $reference = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:BankHistory:collection', 'write:BankHistory'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:BankHistory:collection', 'write:BankHistory'])]
    private ?float $debit = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:BankHistory:collection', 'write:BankHistory'])]
    private ?float $credit = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:BankHistory:collection', 'write:BankHistory'])]
    private ?string $balance = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:BankHistory:collection', 'write:BankHistory'])]
    private ?\DateTimeImmutable $dateAt = null;

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

    public function getDebit(): ?float
    {
        return $this->debit;
    }

    public function setDebit(?float $debit): self
    {
        $this->debit = $debit;

        return $this;
    }

    public function getCredit(): ?float
    {
        return $this->credit;
    }

    public function setCredit(?float $credit): self
    {
        $this->credit = $credit;

        return $this;
    }

    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function setBalance(?string $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    public function getDateAt(): ?\DateTimeImmutable
    {
        return $this->dateAt;
    }

    public function setDateAt(?\DateTimeImmutable $dateAt): self
    {
        $this->dateAt = $dateAt;

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

}
