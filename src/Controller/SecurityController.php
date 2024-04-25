<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\MessageType;
use App\Form\VerifPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
            $email = $form->get('email')->getData();
            $query = "SELECT * FROM [user] WHERE email = '" . $email . "'";
            $user = $entityManager->getConnection()->query($query)->fetch();
            // Enregistrer le nouveau message dans la base de données
            $token = generateToken(20);
            if($user){
                $userEntity = $entityManager->getRepository(User::class)->find($user['id']);
                $userEntity->setToken($token);
                $userEntity->setDateExpirationToken(new \DateTime('+1 hour'));
                $entityManager->flush();
                return $this->redirectToRoute("app_email", array('entity' => $userEntity->getId()));
            }
        }
        return $this->render('security/forgot.html.twig', [
            'form' => $form
        ]);
    }

    #[Route(path: '/forgot/{token}', name: 'app_changePassword')]
    public function changePassword(Request $request, UserPasswordHasherInterface $userPasswordHasher ,EntityManagerInterface $entityManager, $token): Response
    {
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);
        $user = $entityManager->getRepository(user::class)->findOneBy(array('token' => $token));
            if ($form->isSubmitted() && $form->isValid()) {
                if($user){
                    if($user->getDateExpirationToken() > new \DateTime()){
                        $user->setPassword(
                            $userPasswordHasher->hashPassword(
                            $user,
                            $form->get('newPassword')->getData()
                            )
                        );
                        $entityManager->flush();
                    }
                    $user->setToken(null);
                    $user->setDateExpirationToken(null);
                    return $this->redirectToRoute('app_login');
                }

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

