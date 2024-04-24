<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class UserController extends AbstractController
{
    #[Route('/users', name: 'user_index')]
    public function index(EntityManagerInterface $entityManager, Security $security): Response
    {
        if (!$security->isGranted('USER_ACCESS')) {
            $this->addFlash('error', 'You are blocked');

            return $this->redirectToRoute('app_login');
        }

        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/users/update', name: 'users_update', methods: ['POST'])]
    public function updateUsersStatus(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, SessionInterface $session): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        $userIds = $data['ids'];

        $status = $data['status'];

        $users = $entityManager->getRepository(User::class)->findBy(['id' => $userIds]);

        foreach ($users as $user) {
            $user->setStatus($status);

            if ($status === 'deleted' && $this->getUser() == $user) {
                if($this->getUser() == $user){
                    $session->invalidate();
                    $tokenStorage->setToken(null);
                }

                $entityManager->remove($user);
                $entityManager->flush();
            }
        }

        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
