<?php
namespace App\State\Processor\Billing\Sale;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Billing\Sale\SaleInvoiceItem;
use App\Entity\Billing\Sale\SaleReturnInvoice;
use App\Entity\Billing\Sale\SaleReturnInvoiceItem;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use App\Repository\Product\ItemRepository;
use App\Repository\Setting\Finance\TaxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class CreateSaleReturnInvoiceFeeProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager,
                                private readonly ItemRepository $itemRepository,
                                private readonly TaxRepository $taxRepository,
                                Private readonly SaleReturnInvoiceRepository $saleReturnInvoiceRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $invoiceData = json_decode($this->request->getContent(), true);

        $saleReturnInvoiceItem = new SaleReturnInvoiceItem();

        $saleReturnInvoice = $this->saleReturnInvoiceRepository->find($data->getId());

        if(!$data instanceof SaleReturnInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This invoice is not found.'], 404);
        }

        $item = $this->itemRepository->find($this->getIdFromApiResourceId($invoiceData['item']));
        $saleReturnInvoiceItem->setItem($item);
        $saleReturnInvoiceItem->setName($item?->getFee()->getName());
        $saleReturnInvoiceItem->setPu($item?->getFee()->getAmount());
        if (isset($invoiceData['quantity'])){
            $saleReturnInvoiceItem->setQuantity($invoiceData['quantity']);
        }else{
            $saleReturnInvoiceItem->setQuantity(1);
        }
        $saleReturnInvoiceItem->setSaleReturnInvoice($saleReturnInvoice);

        if (isset($invoiceData['discount'])){
            $saleReturnInvoiceItem->setDiscount($invoiceData['discount']);
        }

        if (isset($invoiceData['taxes'])){
            foreach ($invoiceData['taxes'] as $tax){
                $taxObject = $this->taxRepository->find($this->getIdFromApiResourceId($tax));
                $saleReturnInvoiceItem->addTax($taxObject);
            }
        }


        $saleReturnInvoiceItem->setAmount($saleReturnInvoiceItem->getQuantity() * $saleReturnInvoiceItem->getPu());

        $discountAmount =  $saleReturnInvoiceItem->getAmount() * $saleReturnInvoiceItem->getDiscount() / 100;

        $saleReturnInvoiceItem->setDiscountAmount($discountAmount);
        $saleReturnInvoiceItem->setAmountTtc($saleReturnInvoiceItem->getAmount() - $discountAmount);

        $saleReturnInvoiceItem->setUser($this->getUser());
        $saleReturnInvoiceItem->setInstitution($this->getUser()->getInstitution());
        $saleReturnInvoiceItem->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($saleReturnInvoiceItem);
        $this->manager->flush();

        return $this->processor->process($saleReturnInvoiceItem, $operation, $uriVariables, $context);
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
