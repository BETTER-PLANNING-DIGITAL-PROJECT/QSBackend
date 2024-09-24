<?php

namespace App\Controller\Treasury;

use App\Entity\Security\User;
use App\Repository\Security\SystemSettingsRepository;
use App\Repository\Treasury\BankAccountRepository;
use App\Repository\Treasury\CashDeskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetBankAccountController extends AbstractController
{
    private BankAccountRepository $bankAccountRepository;
    private SystemSettingsRepository $systemSettingsRepository;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                BankAccountRepository                     $bankAccountRepository,
                                SystemSettingsRepository                     $systemSettingsRepository,
    )
    {
        $this->bankAccountRepository = $bankAccountRepository;
        $this->systemSettingsRepository = $systemSettingsRepository;
    }

    public function __invoke(Request $request):JsonResponse
    {
        $bankAccountData = [];

        if($this->getUser()->isIsBranchManager()){
            // get all bank account
            $bankAccounts = $this->bankAccountRepository->findBy([], ['id' => 'DESC']);

            foreach ($bankAccounts as $bankAccount)
            {
                $bankAccountData[] = [
					'@id' => "/api/get/bank-account/" . $bankAccount->getId(),
                    'id'=> $bankAccount ->getId(),
                    'code'=> $bankAccount->getCodeSwift(),
                    'bank' => [
						'@id' => "/api/get/bank/" . $bankAccount->getBank()->getId(),
                        '@type' => "Bank",
                        'id' => $bankAccount->getBank() ? $bankAccount->getBank()->getId() : '',
                        'code' => $bankAccount->getBank() ? $bankAccount->getBank()->getCode() : '',
                        'name' => $bankAccount->getBank() ? $bankAccount->getBank()->getName() : '',
                    ],
                    'accountNumber'=> $bankAccount->getAccountNumber(),
                    'accountName'=> $bankAccount->getAccountName(),
                    'balance' => $bankAccount->getBalance(),
                    'codeBranch'=> $bankAccount->getCodeBranch(),
                    'codeRib'=> $bankAccount->getCodeRib(),
                    'branch' => [
                        '@id' => "/api/get/branch/" . $bankAccount->getId(),
                        '@type' => "Branch",
                        'id' => $bankAccount->getBranch() ? $bankAccount->getBranch()->getId() : '',
                        'name' => $bankAccount->getBranch() ? $bankAccount->getBranch()->getName() : '',
                    ],
                ];
            }
        }
        else
        {
            $systemSettings = $this->systemSettingsRepository->findOneBy([]);
            if($systemSettings)
            {
                if($systemSettings->isIsBranches())
               {
                   $userBranches = $this->getUser()->getUserBranches();
                   foreach ($userBranches as $userBranch) {

                       // get cash desk
                       $bankAccounts = $this->bankAccountRepository->findBy(['branch' => $userBranch], ['id' => 'DESC']);
                          foreach ($bankAccounts as $bankAccount){
                              $bankAccountData[] = [
								  '@id' => "/api/get/bank-account/" . $bankAccount->getId(),
                                  'id'=> $bankAccount ->getId(),
                                  'code'=> $bankAccount->getCodeSwift(),
                                  'bank' => [
									  '@id' => "/api/get/bank/" . $bankAccount->getBank()->getId(),
                                      '@type' => "Bank",
                                      'id' => $bankAccount->getBank() ? $bankAccount->getBank()->getId() : '',
                                      'code' => $bankAccount->getBank() ? $bankAccount->getBank()->getCode() : '',
                                      'name' => $bankAccount->getBank() ? $bankAccount->getBank()->getName() : '',
                                  ],
                                  'accountNumber'=> $bankAccount->getAccountNumber(),
                                  'accountName'=> $bankAccount->getAccountName(),
                                  'balance' => $bankAccount->getBalance(),
                                  'codeBranch'=> $bankAccount->getCodeBranch(),
                                  'codeRib'=> $bankAccount->getCodeRib(),
                                  'branch' => [
                                      '@id' => "/api/get/branch/" . $bankAccount->getId(),
                                      '@type' => "Branch",
                                      'id' => $bankAccount->getBranch() ? $bankAccount->getBranch()->getId() : '',
                                      'name' => $bankAccount->getBranch() ? $bankAccount->getBranch()->getName() : '',
                                  ],
                              ];
                          }
                       }
               }
               else {
                   $bankAccounts = $this->bankAccountRepository->findBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);

                   foreach ($bankAccounts as $bankAccount) {
                       if ($bankAccount) {
                           $bankAccountData[] = [
							   '@id' => "/api/get/bank-account/" . $bankAccount->getId(),
                               'id' => $bankAccount->getId(),
                               'code' => $bankAccount->getCodeSwift(),
                               'bank' => [
								   '@id' => "/api/get/bank/" . $bankAccount->getBank()->getId(),
                                   '@type' => "Bank",
                                   'id' => $bankAccount->getBank() ? $bankAccount->getBank()->getId() : '',
                                   'code' => $bankAccount->getBank() ? $bankAccount->getBank()->getCode() : '',
                                   'name' => $bankAccount->getBank() ? $bankAccount->getBank()->getName() : '',
                               ],
                               'accountNumber' => $bankAccount->getAccountNumber(),
                               'accountName' => $bankAccount->getAccountName(),
                               'balance' => $bankAccount->getBalance(),
                               'codeBranch' => $bankAccount->getCodeBranch(),
                               'codeRib' => $bankAccount->getCodeRib(),
                               'branch' => [
                                   '@id' => "/api/get/branch/" . $bankAccount->getId(),
                                   '@type' => "Branch",
                                   'id' => $bankAccount->getBranch() ? $bankAccount->getBranch()->getId() : '',
                                   'name' => $bankAccount->getBranch() ? $bankAccount->getBranch()->getName() : '',
                               ],
                           ];
                       }
                   }

               }
            }
        }


        return $this->json(['hydra:member' => $bankAccountData]);
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
