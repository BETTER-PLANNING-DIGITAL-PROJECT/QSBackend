<?php

namespace App\Controller\Partner;

use App\Entity\Security\User;
use App\Repository\Partner\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class DeleteCustomerController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage, private readonly EntityManagerInterface $entityManager,)
    {
    }

    public function __invoke(mixed $data, Request $request, CustomerRepository $customerRepository)
    {
        $customer = $customerRepository->find($request->get('id'));

        if ($customer) {
            $this->entityManager->remove($customer);
            $this->entityManager->flush();
        }
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
