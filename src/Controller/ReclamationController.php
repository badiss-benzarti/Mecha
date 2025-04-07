<?php
// src/Controller/ReclamationController.php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\Users;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\MailerService;

class ReclamationController extends AbstractController
{
    #[Route('/reclamation', name: 'app_reclamation')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_reclamation_new');
    }

    #[Route('/reclamation/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        TicketRepository $ticketRepo, 
        ValidatorInterface $validator, 
        EntityManagerInterface $entityManager,
        MailerService $mailerService  // Inject MailerService
    ): Response {
        $submittedData = [
            'title' => $request->request->get('title', ''),
            'description' => $request->request->get('description', ''),
            'user_id' => 1  // Always set the user_id to 1
        ];

        $errors = [];

        if ($request->isMethod('POST')) {
            // Randomly select a user ID between 1 and 11
            $randomUserId = rand(3, 3);
            $user = $entityManager->getRepository(Users::class)->find($randomUserId);

            // Check if user exists
            if (!$user) {
                $this->addFlash('error', 'Utilisateur non trouvé.');
                return $this->redirectToRoute('app_reclamation_new');
            }

            $ticket = new Ticket();
            $ticket->setTitle($submittedData['title']);
            $ticket->setDescription($submittedData['description']);
            $ticket->setStatus('fermé');
            $ticket->setUser($user); // Set the randomly selected user
            $ticket->setUserId($user->getId()); // Ensure user_id is stored

            $errors = $validator->validate($ticket);

            if (count($errors) === 0) {
                $ticketRepo->save($ticket, true);
                $this->addFlash('success', 'Réclamation soumise avec succès!');

                // Send email when the ticket is created
                $mailerService->sendTicketCreatedEmail('badiss.benzarti22@gmail.com', $ticket);

                return $this->redirectToRoute('app_reclamation_new');
            }
        }

        return $this->render('reclamation/new.html.twig', [
            'submittedData' => $submittedData,
            'errors' => $errors
        ]);
    }
}
