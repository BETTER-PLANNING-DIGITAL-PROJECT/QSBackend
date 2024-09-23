<?php

namespace App\Controller\Inventory;

use App\Entity\Inventory\Location;
use App\Entity\Security\User;
use App\Repository\Inventory\LocationRepository;
use App\Repository\Inventory\WarehouseRepository;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PostLocationController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(mixed $data, Request $request, LocationRepository $locationRepository, BranchRepository $branchRepository,
    SystemSettingsRepository $systemSettingsRepository, WarehouseRepository $warehouseRepository)
    {
        $locationData = json_decode($request->getContent(), true);
        $branch = !isset($locationData['branch']) ? null : $branchRepository->find($this->getIdFromApiResourceId($locationData['branch']));
        $warehouse = !isset($locationData['warehouse']) ? null : $warehouseRepository->find($this->getIdFromApiResourceId($locationData['warehouse']));

        $systemSettings = $systemSettingsRepository->findOneBy([]);

        $name = $locationData['name'];

        // Check for duplicates based on name within the same branch
        $duplicateCheckName = $locationRepository->findOneBy(['name' => $name, 'branch' => $branch, 'warehouse' => $warehouse]);
        if ($duplicateCheckName) {
            return new JsonResponse(['hydra:description' => 'This name already exists in this branch and warehouse.'], 400);
        }

        $location = new Location();
        $location->setName($locationData['name']);
        $location->setWarehouse($warehouse);
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                $location->setBranch($branch);
            } else {
                $location->setBranch($this->getUser()->getBranch());
            }
        }

        $location->setInstitution($this->getUser()->getInstitution());
        $location->setUser($this->getUser());
        $location->setYear($this->getUser()->getCurrentYear());

        $locationRepository->save($location);

        return $location;
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
