<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/users', name: 'user_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // TODO: показывает неправильно DateTime ласт логина и регистраций
        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/users/update', name: 'users_update', methods: ['POST'])]
    public function updateUsersStatus(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        $userIds = $data['ids'];

        $status = $data['status'];

        $users = $entityManager->getRepository(User::class)->findBy(['id' => $userIds]);

        $currentUser = $this->getUser();
        foreach ($users as $user) {

            if ($status === 'deleted') {
                $entityManager->remove($user);
            }
            else if($status === 'unblocked'){
                $user->setStatus('active');
                $user->setRoles(['ROLE_USER']);
            }
            else{
                $user->setStatus($status);
                $user->setRoles([]);
            }
        }

        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
