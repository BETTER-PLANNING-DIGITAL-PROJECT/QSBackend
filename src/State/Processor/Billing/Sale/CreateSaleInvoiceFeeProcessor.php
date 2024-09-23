<?php
namespace App\State\Processor\Billing\Sale;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Billing\Sale\SaleInvoiceItem;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Product\ItemRepository;
use App\Repository\Setting\Finance\TaxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class CreateSaleInvoiceFeeProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager,
                                private readonly ItemRepository $itemRepository,
                                private readonly TaxRepository $taxRepository,
                                Private readonly SaleInvoiceRepository $saleInvoiceRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $invoiceData = json_decode($this->request->getContent(), true);

        $saleInvoiceItem = new SaleInvoiceItem();

        $saleInvoice = $this->saleInvoiceRepository->find($data->getId());

        if(!$data instanceof SaleInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This invoice is not found.'], 404);
        }

        $item = $this->itemRepository->find($this->getIdFromApiResourceId($invoiceData['item']));
        $saleInvoiceItem->setItem($item);
        $saleInvoiceItem->setName($item?->getFee()->getName());
        $saleInvoiceItem->setPu($item?->getFee()->getAmount());

        if (isset($invoiceData['quantity'])){
            $saleInvoiceItem->setQuantity($invoiceData['quantity']);
        }else{
            $saleInvoiceItem->setQuantity(1);
        }

        $saleInvoiceItem->setSaleInvoice($saleInvoice);

        if (isset($invoiceData['discount'])){
            $saleInvoiceItem->setDiscount($invoiceData['discount']);
        }

        if (isset($invoiceData['taxes'])){
            foreach ($invoiceData['taxes'] as $tax){
                $taxObject = $this->taxRepository->find($this->getIdFromApiResourceId($tax));
                $saleInvoiceItem->addTax($taxObject);
            }
        }


        $saleInvoiceItem->setAmount($saleInvoiceItem->getQuantity() * $saleInvoiceItem->getPu());

        $discountAmount =  $saleInvoiceItem->getAmount() * $saleInvoiceItem->getDiscount() / 100;

        $saleInvoiceItem->setDiscountAmount($discountAmount);
        $saleInvoiceItem->setAmountTtc($saleInvoiceItem->getAmount() - $discountAmount);

        $saleInvoiceItem->setUser($this->getUser());
        $saleInvoiceItem->setInstitution($this->getUser()->getInstitution());
        $saleInvoiceItem->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($saleInvoiceItem);
        $this->manager->flush();

        return $this->processor->process($saleInvoiceItem, $operation, $uriVariables, $context);
    }

    public function getIdFromApiResourceId(string $apiId){
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf+1);
        return intval($id);
    }

    public function getUser(): ?User
    {
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return null;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }
}
