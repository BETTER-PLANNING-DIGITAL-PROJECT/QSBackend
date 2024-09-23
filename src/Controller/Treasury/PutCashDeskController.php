<?php

namespace App\Controller\Treasury;

use App\Entity\Security\User;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\SystemSettingsRepository;
use App\Repository\Security\UserRepository;
use App\Repository\Setting\Finance\CurrencyRepository;
use App\Repository\Treasury\CashDeskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PutCashDeskController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(mixed $data, Request $request, CashDeskRepository $cashDeskRepository, BranchRepository $branchRepository,
                             SystemSettingsRepository $systemSettingsRepository, UserRepository $userRepository, CurrencyRepository $currencyRepository)
    {
        $cashDeskData = json_decode($request->getContent(), true);

        $systemSettings = $systemSettingsRepository->findOneBy([]);
        $branch = !isset($cashDeskData['branch']) ? null : $branchRepository->find($this->getIdFromApiResourceId($cashDeskData['branch']));

        $data->setCode($cashDeskData['code']);
        $operator = !isset($cashDeskData['operator']) ? null : $userRepository->find($this->getIdFromApiResourceId($cashDeskData['operator']));
        $data->setOperator($operator);
        $currency = !isset($cashDeskData['currency']) ? null : $currencyRepository->find($this->getIdFromApiResourceId($cashDeskData['currency']));
        $data->setCurrency($currency);
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                $data->setBranch($branch);

            }
        }
        $data->setIsMain($cashDeskData['isMain']);
        $data->setIsOpen($cashDeskData['isOpen']);

        $cashDeskRepository->save($data);

        return $data;
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
