<?php

namespace App\Controller\Treasury;

use App\Entity\Security\User;
use App\Entity\Treasury\BankAccount;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\SystemSettingsRepository;
use App\Repository\Treasury\BankAccountRepository;
use App\Repository\Treasury\BankRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PostBankAccountController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(mixed $data, Request $request, BankAccountRepository $bankAccountRepository, BranchRepository $branchRepository,
    SystemSettingsRepository $systemSettingsRepository, BankRepository $bankRepository)
    {
        $bankAccountData = json_decode($request->getContent(), true);
        $branch = !isset($bankAccountData['branch']) ? null : $branchRepository->find($this->getIdFromApiResourceId($bankAccountData['branch']));

        $systemSettings = $systemSettingsRepository->findOneBy([]);

        $bankAccount = new BankAccount();
        $bank = !isset($bankAccountData['bank']) ? null : $bankRepository->find($this->getIdFromApiResourceId($bankAccountData['bank']));
        $bankAccount->setBank($bank);
        $bankAccount->setAccountNumber($bankAccountData['accountNumber']);
        $bankAccount->setAccountName($bankAccountData['accountName']);
        $bankAccount->setCodeSwift($bankAccountData['codeSwift']);
        $bankAccount->setCodeIbam($bankAccountData['codeIbam']);
        $bankAccount->setCodeRib($bankAccountData['codeRib']);
        $bankAccount->setCodeBranch($bankAccountData['codeBranch']);
        $bankAccount->setIsDefault($bankAccountData['isDefault']);
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                $bankAccount->setBranch($branch);
            } else {
                $bankAccount->setBranch($this->getUser()->getBranch());
            }
        }

        $bankAccount->setInstitution($this->getUser()->getInstitution());
        $bankAccount->setUser($this->getUser());
        $bankAccount->setYear($this->getUser()->getCurrentYear());

        $bankAccountRepository->save($bankAccount);

        return $bankAccount;
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
