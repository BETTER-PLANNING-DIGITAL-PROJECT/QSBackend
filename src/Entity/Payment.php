<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\School\Schooling\Configuration\Fee;
use App\Entity\School\Schooling\Registration\Student;
use App\Repository\PaymentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
//#[ORM\Table(name: 'setting_payment_gateway')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/payment/get',
            normalizationContext: [
                'groups' => ['get:Payment:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/payment/create',
            denormalizationContext: [
                'groups' => ['write:Payment'],
            ],
        ),
        new Put(
            uriTemplate: '/payment/edit/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Payment'],
            ],
        ),
        new Delete(
            uriTemplate: '/payment/delete/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]

)]class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Payment:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Payment:collection', 'write:Payment'])]
    private ?string $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Payment:collection', 'write:Payment'])]
    private ?string $reference = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Payment:collection', 'write:Payment'])]
    private ?string $paymentUrl = null;

    #[ORM\Column(type: Types::GUID, nullable: true)]
    #[Groups(['get:Payment:collection', 'write:Payment'])]
    private ?string $uuid = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Payment:collection', 'write:Payment'])]
    private ?string $code = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Payment:collection', 'write:Payment'])]
    private ?int $paymentGatewayId = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Payment:collection', 'write:Payment'])]
    private ?string $methodUsed = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:Payment:collection', 'write:Payment'])]
    private ?string $type = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Payment:collection', 'write:Payment'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Student $student = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Payment:collection', 'write:Payment'])]
    private ?Fee $fee = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getPaymentUrl(): ?string
    {
        return $this->paymentUrl;
    }

    public function setPaymentUrl(?string $paymentUrl): static
    {
        $this->paymentUrl = $paymentUrl;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getPaymentGatewayId(): ?int
    {
        return $this->paymentGatewayId;
    }

    public function setPaymentGatewayId(?int $paymentGatewayId): static
    {
        $this->paymentGatewayId = $paymentGatewayId;

        return $this;
    }

    public function getMethodUsed(): ?string
    {
        return $this->methodUsed;
    }

    public function setMethodUsed(?string $methodUsed): static
    {
        $this->methodUsed = $methodUsed;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): static
    {
        $this->student = $student;

        return $this;
    }

    public function getFee(): ?Fee
    {
        return $this->fee;
    }

    public function setFee(?Fee $fee): static
    {
        $this->fee = $fee;

        return $this;
    }
}
