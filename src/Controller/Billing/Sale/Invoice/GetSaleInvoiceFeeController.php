<?php

namespace App\Controller\Billing\Sale\Invoice;

use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Partner\CustomerRepository;
use App\Repository\Product\ItemRepository;
use App\Repository\School\Schooling\Configuration\FeeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetSaleInvoiceFeeController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                )
    {
    }

    public function __invoke(SaleInvoiceItemRepository $saleInvoiceItemRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             CustomerRepository $customerRepository,
                             ItemRepository $itemRepository,
                             FeeRepository $feeRepository,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');
        $saleInvoice = $saleInvoiceRepository->find($id);
        if (!$saleInvoice){
            return new JsonResponse(['hydra:description' => 'This invoice is not found.'], 404);
        }

        $items = [];

        $saleInvoiceItems = $saleInvoiceItemRepository->findBy(['saleInvoice' => $saleInvoice]);

        foreach ($saleInvoiceItems as $saleInvoiceItem){
            if ($saleInvoiceItem->getItem()->getFee()){
                $items[] = [
                    'id' => $saleInvoiceItem->getId(),
                    'saleInvoice' => [
                        'id' => $saleInvoiceItem->getSaleInvoice() ? $saleInvoiceItem->getSaleInvoice()->getId() : '',
                        '@id' => '/api/get/sale-invoice/'. $saleInvoiceItem->getSaleInvoice()->getId(),
                        'invoiceNumber' => $saleInvoiceItem->getSaleInvoice() ? $saleInvoiceItem->getSaleInvoice()->getInvoiceNumber() : '',
                    ],
                    'item' => [
                        'id' => $saleInvoiceItem->getItem()->getId(),
                        '@id' => '/api/get/items/'. $saleInvoiceItem->getItem()->getId(),
                        'name' => $saleInvoiceItem->getItem() ? $saleInvoiceItem->getItem()->getName() : '',
                        'reference' => $saleInvoiceItem->getItem() ? $saleInvoiceItem->getItem()->getReference() : '',
                        'barcode' => $saleInvoiceItem->getItem() ? $saleInvoiceItem->getItem()->getBarcode() : '',
                        'price' => $saleInvoiceItem->getItem() ? $saleInvoiceItem->getItem()->getPrice() : '',
                        'salePrice' => $saleInvoiceItem->getItem() ? $saleInvoiceItem->getItem()->getSalePrice() : '',
                        'cost' => $saleInvoiceItem->getItem() ? $saleInvoiceItem->getItem()->getCost() : '',
                    ],
                    'name' => $saleInvoiceItem->getName(),
                    'quantity' => $saleInvoiceItem->getQuantity(),
                    'amount' => number_format($saleInvoiceItem->getAmount(), 2, ',',' '),
                    'pu' => number_format($saleInvoiceItem->getPu(), 2, ',',' '),
                    'discount' => $saleInvoiceItem->getDiscount(),
                    'discountAmount' => $saleInvoiceItem->getDiscountAmount(),
                    'amountTtc' => $saleInvoiceItem->getAmountTtc(),
                    'amountWithTaxes' => $saleInvoiceItem->getAmountWithTaxes(),
                    'returnQuantity' => $saleInvoiceItem->getReturnQuantity(),
                ];
            }
        }

        return $this->json(['hydra:member' => $items]);
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
