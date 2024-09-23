<?php

namespace App\Controller\Report\Treasury;

use App\Entity\Security\User;
use App\Repository\Treasury\BankAccountRepository;
use App\Repository\Treasury\BankHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class BankHistoryReportController extends AbstractController
{

    public function __construct(Request $req, EntityManagerInterface $entityManager, BankHistoryRepository $bankHistoryRepository, BankAccountRepository $bankAccountRepository,
                                private readonly TokenStorageInterface $tokenStorage)
    {
        $this->req = $req;
        $this->entityManager = $entityManager;
        $this->bankAccountRepository = $bankAccountRepository;
        $this->bankHistoryRepository = $bankHistoryRepository;
    }

    #[Route('/api/get/bank-history/report', name: 'app_get_bank_history_report')]
    public function getBankHistory(Request $request): JsonResponse
    {
        $bankHistoryData = json_decode($request->getContent(), true);

        $filteredInvoices = [];

        $dql = 'SELECT id, bank_account_id, reference, description, debit, credit, balance FROM treasury_bank_history
          WHERE ';

        if(isset($bankHistoryData['startDate']) && !isset($bankHistoryData['endDate'])){
            $dql = $dql .' date_at LIKE \'' . $bankHistoryData['startDate'] . '%\'';
        }


        if(isset($bankHistoryData['startDate']) && isset($bankHistoryData['endDate'])){
            $dql = $dql .' date_at BETWEEN \'' . $bankHistoryData['startDate'] . '\' AND \'' . $bankHistoryData['endDate'] . '\''; // Use proper escaping with \ and add space
        }

        if (isset($bankHistoryData['bankAccount'])){
            $bankAccountId = $this->bankAccountRepository->find($this->getIdFromApiResourceId($bankHistoryData['bankAccount']));

            $dql = $dql .' AND bank_account_id = '. $bankAccountId->getId();
        }


        $conn = $this->entityManager->getConnection();
        $resultSet = $conn->executeQuery($dql);
        $rows = $resultSet->fetchAllAssociative();


        foreach ($rows as $row) {

            $bankHistory = $this->bankHistoryRepository->find($row['id']);

            $filteredInvoices[] = [
                'id' => $bankHistory->getId(),
                'reference' => $bankHistory->getReference(),
                'description' => $bankHistory->getDescription(),
                'debit' => $bankHistory->getDebit(),
                'credit' => $bankHistory->getCredit(),
                'balance' => $bankHistory->getBalance(),
                'dateAt' => $bankHistory->getDateAt()->format('Y-m-d'),
                'bankAccount' => [
                    '@id' => "/api/bankAccount/" . $bankHistory->getBankAccount()->getId(),
                    '@type' => "BankAccount",
                    'id' => $bankHistory->getBankAccount()->getId(),
                    'accountName' => $bankHistory->getBankAccount()->getAccountName(),
                    'balance' => $bankHistory->getBankAccount()->getBalance(),
                ],
            ];
        }


        return $this->json($filteredInvoices);
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