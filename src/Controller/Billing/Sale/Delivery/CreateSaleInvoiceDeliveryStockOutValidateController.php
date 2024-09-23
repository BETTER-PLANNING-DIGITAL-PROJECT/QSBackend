<?php

namespace App\Controller\Billing\Sale\Delivery;

use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Inventory\Delivery;
use App\Entity\Inventory\DeliveryItem;
use App\Entity\Inventory\StockMovement;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemStockRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Inventory\DeliveryItemRepository;
use App\Repository\Inventory\DeliveryRepository;
use App\Repository\Inventory\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class CreateSaleInvoiceDeliveryStockOutValidateController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly EntityManagerInterface $manager
    )
    {
    }

    public function __invoke(SaleInvoiceItemRepository $saleInvoiceItemRepository,
                             SaleInvoiceItemStockRepository $saleInvoiceItemStockRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             DeliveryRepository $deliveryRepository,
                             DeliveryItemRepository $deliveryItemRepository,
                             StockRepository $stockRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $data = json_decode($request->getContent(), true);

        $invoice = $saleInvoiceRepository->find($id);

        if(!($invoice instanceof SaleInvoice))
        {
            // Warning
            return new JsonResponse(['hydra:title' => 'This data must be type of invoice.'], 404);
        }

        if(!$invoice)
        {
            // Warning
            return new JsonResponse(['hydra:title' => 'This invoice is not found.'], 404);
        }

        $existingReference = $deliveryRepository->findOneBy(['otherReference' => $invoice->getInvoiceNumber()]);
        if ($existingReference){
            return new JsonResponse(['hydra:title' => 'This feature already generated.'], 500);
        }

        $generateDeliveryUniqNumber = $deliveryRepository->findOneBy([], ['id' => 'DESC']);

        if (!$generateDeliveryUniqNumber){
            $uniqueNumber = 'INV/DEL/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $generateDeliveryUniqNumber->getReference());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $uniqueNumber = 'INV/DEL/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }

        $delivery = new Delivery();
        $delivery->setInvoice($invoice);
        $delivery->setCustomer($invoice->getCustomer());
        $delivery->setReference($uniqueNumber);
        $delivery->setOtherReference($invoice->getInvoiceNumber());
        $delivery->setDeliveryAt(new \DateTimeImmutable());
        // description
        // validate
        $delivery->setIsValidate(true);
        $delivery->setValidateAt(new \DateTimeImmutable());
        $delivery->setValidateBy($this->getUser());
        $delivery->setStatus('delivery');

        $delivery->setIsEnable(true);
        $delivery->setCreatedAt(new \DateTimeImmutable());
        $delivery->setYear($this->getUser()->getCurrentYear());
        $delivery->setUser($this->getUser());
        $delivery->setInstitution($this->getUser()->getInstitution());

        // other invoice status update
        $invoice->setOtherStatus('delivery');

        $entityManager->persist($delivery);

        $saleInvoiceItems = $saleInvoiceItemRepository->findBy(['saleInvoice' => $invoice]);

        if ($saleInvoiceItems)
        {
            foreach ($saleInvoiceItems as $saleInvoiceItem)
            {
                $deliveryItem = new DeliveryItem();
                $deliveryItem->setDelivery($delivery);
                $deliveryItem->setItem($saleInvoiceItem->getItem());
                $deliveryItem->setQuantity($saleInvoiceItem->getQuantity());

                $deliveryItem->setIsEnable(true);
                $deliveryItem->setCreatedAt(new \DateTimeImmutable());
                $deliveryItem->setYear($this->getUser()->getCurrentYear());
                $deliveryItem->setUser($this->getUser());
                $deliveryItem->setInstitution($saleInvoiceItem->getInstitution());

                $this->manager->persist($deliveryItem);

                // Faire la sortie de stock
                $saleInvoiceItemStocks = $saleInvoiceItemStockRepository->findBy(['saleInvoiceItem' => $saleInvoiceItem]);

                if ($saleInvoiceItemStocks){
                    foreach ($saleInvoiceItemStocks as $saleInvoiceItemStock)
                    {
                        $stock = $saleInvoiceItemStock->getStock();
                        $stock->setQuantity($stock->getQuantity() - $deliveryItem->getQuantity());
                        $stock->setAvailableQte($stock->getAvailableQte() - $deliveryItem->getQuantity());
                        // $stock->setReserveQte($deliveryItem->getQuantity());

                        // Stock movement
                        $stockMovement = new StockMovement();
                        $stockMovement->setReference($stock->getReference());
                        $stockMovement->setItem($deliveryItem->getItem());
                        $stockMovement->setQuantity($deliveryItem->getQuantity());
                        $stockMovement->setUnitCost($stock->getUnitCost());
                        $stockMovement->setFromWarehouse($stock->getWarehouse());
                        // from location
                        $stockMovement->setStockAt(new \DateTimeImmutable());
                        $stockMovement->setLoseAt($stock->getLoseAt());
                        $stockMovement->setNote($stock->getNote());
                        $stockMovement->setIsOut(true);

                        $stockMovement->setYear($this->getUser()->getCurrentYear());
                        $stockMovement->setUser($this->getUser());
                        $stockMovement->setCreatedAt(new \DateTimeImmutable());
                        $stockMovement->setIsEnable(true);
                        $stockMovement->setUpdatedAt(new \DateTimeImmutable());
                        $stockMovement->setInstitution($this->getUser()->getInstitution());

                        $entityManager->persist($stockMovement);
                    }
                }
            }
        }

        $this->manager->flush();

        return $this->json(['hydra:member' => $delivery]);
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
