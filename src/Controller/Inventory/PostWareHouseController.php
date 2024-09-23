<?php

namespace App\Controller\Inventory;

use App\Entity\Inventory\Warehouse;
use App\Entity\Security\User;
use App\Entity\Treasury\BankAccount;
use App\Repository\Inventory\WarehouseRepository;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\SystemSettingsRepository;
use App\Repository\Treasury\BankAccountRepository;
use App\Repository\Treasury\BankRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PostWareHouseController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(mixed $data, Request $request, WarehouseRepository $warehouseRepository, BranchRepository $branchRepository,
    SystemSettingsRepository $systemSettingsRepository)
    {
        $warehouseData = json_decode($request->getContent(), true);
        $branch = !isset($warehouseData['branch']) ? null : $branchRepository->find($this->getIdFromApiResourceId($warehouseData['branch']));

        $systemSettings = $systemSettingsRepository->findOneBy([]);

        $name = $warehouseData['name'];
        $code = $warehouseData['code'];

        // Check for duplicates based on code within the same branch
        $duplicateCheckCode = $warehouseRepository->findOneBy(['code' => $code, 'branch' => $branch]);
        if ($duplicateCheckCode) {
            return new JsonResponse(['hydra:description' => 'This code already exists in this branch.'], 400);
        }

        // Check for duplicates based on name within the same branch
        $duplicateCheckName = $warehouseRepository->findOneBy(['name' => $name, 'branch' => $branch]);
        if ($duplicateCheckName) {
            return new JsonResponse(['hydra:description' => 'This name already exists in this branch.'], 400);
        }

        $warehouse = new Warehouse();
        $warehouse->setCode($warehouseData['code']);
        $warehouse->setName($warehouseData['name']);
        $warehouse->setAddress($warehouseData['address']);
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                $warehouse->setBranch($branch);
            } else {
                $warehouse->setBranch($this->getUser()->getBranch());
            }
        }

        $warehouse->setInstitution($this->getUser()->getInstitution());
        $warehouse->setUser($this->getUser());
        $warehouse->setYear($this->getUser()->getCurrentYear());

        $warehouseRepository->save($warehouse);

        return $warehouse;
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
