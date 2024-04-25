<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;

class EmailController extends AbstractController
{
    #[Route('/email/{entity}', name: 'app_email')]
    public function index($entity, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(user::class)->find($entity);
        $token = $user->getToken();
        return $this->render('email/index.html.twig', [
            'controller_name' => 'EmailController',
            'token' => $token
        ]);
    }
}
