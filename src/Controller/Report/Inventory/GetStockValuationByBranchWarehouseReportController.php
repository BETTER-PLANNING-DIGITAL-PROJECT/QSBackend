<?php

namespace App\Controller\Report\Inventory;

use App\Entity\Billing\Sale\SaleSettlement;
use App\Entity\Inventory\Stock;
use App\Entity\Inventory\StockMovement;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use App\Repository\Inventory\StockRepository;
use App\Repository\Inventory\WarehouseRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\Session\YearRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetStockValuationByBranchWarehouseReportController extends AbstractController
{

    public function __construct(Request $req, EntityManagerInterface $entityManager,
                                private readonly TokenStorageInterface $tokenStorage,  StockRepository $stockRepository, BranchRepository $branchRepository, WarehouseRepository $warehouseRepository)
    {
        $this->req = $req;
        $this->entityManager = $entityManager;
        $this->stockRepository = $stockRepository;
        $this->branchRepository = $branchRepository;
        $this->warehouseRepository = $warehouseRepository;
    }

    #[Route('/api/get/stock-valuation-by-branch-warehouse/report', name: 'app_get_stock_valuation_by_branch_warehouse_report')]
    public function getStockValuationByBranchWarehouse(Request $request): JsonResponse
    {
        $stockData = json_decode($request->getContent(), true);

        $branch = $this->branchRepository->find($this->getIdFromApiResourceId($stockData['branch']));
        $warehouse = $this->warehouseRepository->find($this->getIdFromApiResourceId($stockData['warehouse']));

        $filteredStocks = [];

        $dql = 'SELECT id, item_id, batch_id, package_id, unit_id, warehouse_id, quantity, unit_cost, total_value FROM inventory_stock s 
            WHERE branch_id = '.$branch->getId(). ' AND warehouse_id = '.$warehouse->getId();


        if (isset($stockData['branch'])){
            $branch = $this->branchRepository->find($this->getIdFromApiResourceId($stockData['branch']));

            $dql = $dql .' AND branch_id = '. $branch->getId();
        }

        if (isset($stockData['warehouse'])){
            $warehouse = $this->warehouseRepository->find($this->getIdFromApiResourceId($stockData['warehouse']));

            $dql = $dql .' AND warehouse_id = '. $warehouse->getId();
        }

        $conn = $this->entityManager->getConnection();
        $resultSet = $conn->executeQuery($dql);
        $rows = $resultSet->fetchAllAssociative();

        foreach ($rows as $row) {
            $stock = $this->stockRepository->find($row['id']);
            $filteredStocks[] = $this->bindStock($stock);
        }

        return $this->json($filteredStocks);
    }

    public function bindStock(Stock $stock): array
    {
        return [
            'id' => $stock->getId(),
            'stockAt' => $stock->getStockAt()->format('d/m/y H:i'),
            'reference' => $stock->getReference(),
            'item' => $stock->getItem() ? $stock->getItem()->getName() : '',
            'quantity' => $stock->getQuantity(),
            'totalValue' => $stock->getTotalValue(),
        ];
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

    public function getIdFromApiResourceId(string $apiId){
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf+1);
        return intval($id);
    }
}



