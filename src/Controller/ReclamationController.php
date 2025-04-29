<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Ticket;
use App\Entity\Users;
use App\Repository\MessageRepository;
use App\Repository\TicketRepository;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

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
        EntityManagerInterface $em,
        MailerInterface $mailer, // Changed to MailerInterface
      
    ): Response {
        $data = ['title' => '', 'description' => ''];
        
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            
            $user = $em->getRepository(Users::class)->find(3); // Static ID 3
            if (!$user) {
                $this->addFlash('error', 'User not found');
                return $this->redirectToRoute('app_reclamation_new');
            }
    
            $ticket = new Ticket();
            $ticket->setTitle($data['title'])
                   ->setDescription($data['description'])
                   ->setStatus('fermé')
                   ->setUser($user); // Sets both relation and user_id
    
            $errors = $validator->validate($ticket);
            
            if (count($errors) === 0) {
                try {
                    // 1. Save ticket first
                    $em->persist($ticket);
                    $em->flush();
                    
                    // 2. Send email after successful save
                    $email = (new Email())
                        ->from('badissbenzarti@gmail.com') // Must be verified in Mailjet
                        ->to('badissbenzarti@gmail.com')
                        ->subject('New Ticket #'.$ticket->getId())
                        ->html(sprintf(
                            '<h1>New Ticket Submitted</h1>
                            <p><strong>Title:</strong> %s</p>
                            <p><strong>Description:</strong> %s</p>',
                            $ticket->getTitle(),
                            $ticket->getDescription()
                        ));
    
                    $mailer->send($email);
                    $this->addFlash('success', 'Ticket created and email sent!');
                    
                } catch (\Exception $e) {
                    
                    $this->addFlash('error', 'Error saving ticket: '.$e->getMessage());
                }
    
                return $this->redirectToRoute('app_reclamation_new');
            }
        }
    
        return $this->render('reclamation/new.html.twig', [
            'submittedData' => $data,
            'errors' => $errors ?? []
        ]);
    }
#[Route('/reclamation/messages', name: 'app_reclamation_msg')]
public function messages(MessageRepository $messageRepository, EntityManagerInterface $em): Response
{
    $user = $em->getRepository(Users::class)->find(3);
    if (!$user) {
        throw $this->createNotFoundException('User with ID 3 not found');
    }

    $messages = $messageRepository->findByRecipientWithSender($user);

    return $this->render('reclamation/messages.html.twig', [
        'messages' => $messages,
        'current_user_id' => $user->getId()
    ]);
}

#[Route('/message/conversation/{ticketId}', name: 'message_conversation')]
public function conversation(int $ticketId, MessageRepository $messageRepository, EntityManagerInterface $em): Response
{
    $user = $em->getRepository(Users::class)->find(3);
    $ticket = $em->getRepository(Ticket::class)->find($ticketId);

    if (!$user || !$ticket) {
        throw $this->createNotFoundException('User or Ticket not found');
    }

    $messages = $messageRepository->findBy(
        ['ticket' => $ticket],
        ['createdAt' => 'ASC']
    );

    return $this->render('reclamation/conversation.html.twig', [
        'messages' => $messages,
        'ticket' => $ticket,
        'current_user_id' => $user->getId()
    ]);
}

#[Route('/message/reply/{ticketId}', name: 'message_reply', methods: ['POST'])]
public function reply(
    int $ticketId, 
    Request $request, 
    EntityManagerInterface $em,
    ValidatorInterface $validator
): Response {
    $user = $em->getRepository(Users::class)->find(3);
    $ticket = $em->getRepository(Ticket::class)->find($ticketId);

    if (!$user) {
        throw $this->createNotFoundException('User not found');
    }
    if (!$ticket) {
        throw $this->createNotFoundException('Ticket not found');
    }

    $message = new Message();
    $message->setContent($request->request->get('message_content', ''));
    $message->setSender($user);
    $message->setRecipient($ticket->getUser());
    $message->setTicket($ticket);
    $message->setCreatedAt(new \DateTime());

    $errors = $validator->validate($message);
    if (count($errors) > 0) {
        foreach ($errors as $error) {
            $this->addFlash('error', $error->getMessage());
        }
        return $this->redirectToRoute('message_conversation', ['ticketId' => $ticketId]);
    }

    $em->persist($message);
    $em->flush();

    return $this->redirectToRoute('message_conversation', ['ticketId' => $ticketId]);
}

#[Route('/message/{id}/delete', name: 'message_delete', methods: ['POST'])]
public function deleteMessage(int $id, EntityManagerInterface $em, Request $request): Response
{
    $message = $em->getRepository(Message::class)->find($id);

    if (!$message) {
        throw $this->createNotFoundException('Message not found');
    }

    if ($this->isCsrfTokenValid('delete' . $message->getId(), $request->request->get('_token'))) {
        $em->remove($message);
        $em->flush();
        $this->addFlash('success', 'Message deleted successfully');
    }

    return $this->redirectToRoute('app_reclamation_msg');
}
}