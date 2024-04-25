<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class HomeController extends AbstractController
{

    #[Route('/', name: 'app_home_index')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer les messages depuis la base de données
        $messages = $entityManager->getRepository(Message::class)->findAll();

        // Créer un nouveau formulaire de message
        $message = new Message();
        $user = $entityManager->getRepository(User::class)->findOneBy(array('email' => $this->getUser()->getUserIdentifier()));
        $form = $this->createForm(MessageType::class, $message);

        // Traiter la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $message->setUsers($user);
            // Enregistrer le nouveau message dans la base de données
            $entityManager->persist($message);
            $entityManager->flush();

            // Rediriger vers la page d'accueil après avoir soumis le message
            return $this->redirectToRoute('app_home_index');
        }





        // Afficher le template avec les messages et le formulaire
        return $this->render('home/index.html.twig', [
            'form' => $form,
            'messages' => $messages,
        ]);
    }
}
