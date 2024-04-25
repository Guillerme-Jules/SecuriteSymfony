<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\MessageType;
use App\Form\VerifPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use function App\Fonction\generateToken;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/forgot', name: 'app_forgot')]
    public function forgot(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VerifPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $entityManager->getRepository(user::class)->findBy(array('email' => $form->get('email')->getData()));
            // Enregistrer le nouveau message dans la base de données

            $token = generateToken(20);
            $user[0]->setToken($token);

            $entityManager->flush();





        }
        return $this->render('security/changePassword.html.twig', [
            'form' => $form

        ]);
    }


    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}

