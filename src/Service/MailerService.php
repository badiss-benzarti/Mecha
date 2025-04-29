<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\Ticket;

class MailerService
{
    private $mailer;
    private $senderEmail;
    private $recipientEmail;

    public function __construct(
        MailerInterface $mailer,
        string $senderEmail,
        string $recipientEmail
    ) {
        $this->mailer = $mailer;
        $this->senderEmail = $senderEmail;
        $this->recipientEmail = $recipientEmail;
    }

    public function sendTicketCreatedEmail(Ticket $ticket): void
    {
        $email = (new Email())
            ->from($this->senderEmail)
            ->to($this->recipientEmail)
            ->subject('New Ticket: ' . $ticket->getTitle())
            ->text(sprintf(
                "New ticket submitted:\nFrom: %s\nTitle: %s\nDescription: %s",
                $this->senderEmail,
                $ticket->getTitle(),
                $ticket->getDescription()
            ));

        $this->mailer->send($email);
    }
}