<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Users;
use App\Repository\TicketRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\MailerService;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function dashboard(TicketRepository $ticketRepository, Request $request): Response
    {
        $searchQuery = $request->query->get('searchQuery', '');
        
        $counts = [
            'openTicketsCount' => $ticketRepository->count(['status' => 'open']) + $ticketRepository->count(['status' => 'ouvert']),
            'inProgressTicketsCount' => $ticketRepository->count(['status' => 'en cours']),
            'solvedTicketsCount' => $ticketRepository->count(['status' => 'résolu']),
            'closedTicketsCount' => $ticketRepository->count(['status' => 'fermé']) + $ticketRepository->count(['status' => 'closed'])
        ];
        
        $totalTickets = array_sum($counts);
        
        $percentages = [
            'openTicketsPercentage' => $totalTickets > 0 ? ($counts['openTicketsCount'] / $totalTickets) * 100 : 0,
            'inProgressTicketsPercentage' => $totalTickets > 0 ? ($counts['inProgressTicketsCount'] / $totalTickets) * 100 : 0,
            'solvedTicketsPercentage' => $totalTickets > 0 ? ($counts['solvedTicketsCount'] / $totalTickets) * 100 : 0,
            'closedTicketsPercentage' => $totalTickets > 0 ? ($counts['closedTicketsCount'] / $totalTickets) * 100 : 0
        ];
    
        return $this->render('admin/index.html.twig', [
            'openTicketsCount' => $counts['openTicketsCount'],
            'inProgressTicketsCount' => $counts['inProgressTicketsCount'],
            'solvedTicketsCount' => $counts['solvedTicketsCount'],
            'closedTicketsCount' => $counts['closedTicketsCount'],
            'totalTicketsCount' => $totalTickets,
            'openTicketsPercentage' => $percentages['openTicketsPercentage'],
            'inProgressTicketsPercentage' => $percentages['inProgressTicketsPercentage'],
            'solvedTicketsPercentage' => $percentages['solvedTicketsPercentage'],
            'closedTicketsPercentage' => $percentages['closedTicketsPercentage'],
            'allTickets' => $ticketRepository->searchTickets($searchQuery)
        ]);
    }
    #[Route('/admin/tickets/search', name: 'admin_ticket_search', methods: ['GET'])]
    public function searchTicketsAjax(Request $request, TicketRepository $ticketRepository, NormalizerInterface $normalizer): JsonResponse
    {
        $searchTerm = $request->query->get('term', '');
        $tickets = $ticketRepository->searchByTitle($searchTerm);
        
        $normalizedTickets = $normalizer->normalize($tickets, 'json', [
            'groups' => ['tickets']
        ]);
        
        return new JsonResponse($normalizedTickets);
    }

    #[Route('/admin/ticket/{id}', name: 'admin_ticket_show')]
    public function showTicket(int $id, TicketRepository $ticketRepository): Response
    {
        $ticket = $ticketRepository->find($id) ?? throw $this->createNotFoundException('Ticket not found');
        return $this->render('admin/ticket_show.html.twig', ['ticket' => $ticket]);
    }

    #[Route('/admin/ticket/{id}/update', name: 'admin_ticket_update', methods: ['POST'])]
    public function updateTicket(int $id, Request $request, TicketRepository $ticketRepository, EntityManagerInterface $em): Response
    {
        $ticket = $ticketRepository->find($id) ?? throw $this->createNotFoundException('Ticket not found');

        $ticket->setTitle($request->request->get('title'))
            ->setDescription($request->request->get('description'))
            ->setStatus($request->request->get('status'))
            ->setUpdatedAt(new \DateTime());

        $em->flush();
        $this->addFlash('success', 'Ticket updated successfully');
        return $this->redirectToRoute('admin_ticket_show', ['id' => $id]);
    }

    #[Route('/admin/ticket/{id}/delete', name: 'admin_ticket_delete', methods: ['POST'])]
    public function deleteTicket(int $id, Request $request, TicketRepository $ticketRepository, EntityManagerInterface $em): Response
    {
        $ticket = $ticketRepository->find($id) ?? throw $this->createNotFoundException('Ticket not found');

        if ($this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            $em->remove($ticket);
            $em->flush();
            $this->addFlash('success', 'Ticket deleted successfully');
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/ticket/{id}/send-message', name: 'admin_send_message', methods: ['POST'])]
    public function sendMessage(
        int $id,
        Request $request,
        TicketRepository $ticketRepository,
        EntityManagerInterface $em,
        MailerService $mailer
    ): Response {
        $ticket = $ticketRepository->find($id) ?? throw $this->createNotFoundException('Ticket not found');

        $message = (new Message())
            ->setTicket($ticket)
            ->setSender($em->getReference(Users::class, 12))
            ->setRecipient($ticket->getUser())
            ->setContent($request->request->get('message_content'))
            ->setCreatedAt(new \DateTime());

        $em->persist($message);
        $em->flush();

        return $this->redirectToRoute('admin_conversation', ['ticketId' => $ticket->getTicketId()]);
    }

    #[Route('/admin/ticket/{id}/message', name: 'admin_ticket_message')]
    public function messageForm(int $id, TicketRepository $ticketRepository): Response
    {
        $ticket = $ticketRepository->find($id) ?? throw $this->createNotFoundException('Ticket not found');
        
        return $this->render('admin/message_form.html.twig', [
            'ticket' => $ticket,
            'recipient' => $ticket->getUser()
        ]);
    }

    #[Route('/admin/conversation/{ticketId}', name: 'admin_conversation')]
    public function conversation(
        int $ticketId,
        TicketRepository $ticketRepository,
        MessageRepository $messageRepository
    ): Response {
        $ticket = $ticketRepository->find($ticketId) ?? throw $this->createNotFoundException('Ticket not found');
        
        $messages = $messageRepository->findBy(
            ['ticket' => $ticket],
            ['createdAt' => 'ASC']
        );
    
        return $this->render('admin/conversation.html.twig', [
            'ticket' => $ticket,
            'messages' => $messages,
            'recipient' => $ticket->getUser()
        ]);
    }

    #[Route('/admin/messages', name: 'admin_messages')]
    public function messages(MessageRepository $messageRepository): Response
    {
        $messages = $messageRepository->findBy(
            ['sender' => 12],
            ['createdAt' => 'DESC']
        );
    
        return $this->render('admin/messages.html.twig', [
            'messages' => $messages
        ]);
    }

    #[Route('/admin/message/delete', name: 'admin_delete_message', methods: ['POST'])]
    public function deleteMessage(Request $request, MessageRepository $messageRepository, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = $messageRepository->find($data['id']);

        if (!$message) {
            return $this->json(['success' => false, 'error' => 'Message not found']);
        }

        $em->remove($message);
        $em->flush();

        return $this->json(['success' => true]);
    }
  
    // src/Controller/AdminController.php
#[Route('/admin/messages/search', name: 'admin_message_search', methods: ['GET'])]
public function searchMessages(Request $request, MessageRepository $messageRepository, NormalizerInterface $normalizer): JsonResponse
{
    $query = $request->query->get('q', '');
    
    if (empty($query)) {
        return $this->json([]);
    }

    // Search messages where current admin is the sender (assuming admin has ID 12)
    $messages = $messageRepository->searchByUsername($query, 12);

    $data = array_map(function($message) {
        return [
            'id' => $message->getId(),
            'content' => $message->getContent(),
            'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i'),
            'recipient' => $message->getRecipient() ? 
                $message->getRecipient()->getPrenom().' '.$message->getRecipient()->getNom() : 
                'Unknown',
            'ticketId' => $message->getTicket()->getTicketId(),
            'ticketTitle' => $message->getTicket()->getTitle()
        ];
    }, $messages);

    return $this->json($data);
}
}