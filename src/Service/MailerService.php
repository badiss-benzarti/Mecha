<?php
// src/Service/MailerService.php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    private $mailer;
    private $senderEmail;

    public function __construct(MailerInterface $mailer, string $senderEmail)
    {
        $this->mailer = $mailer;
        $this->senderEmail = $senderEmail;
    }

    public function sendTicketCreatedEmail(string $recipientEmail, $ticket)
    {
        $email = (new Email())
            ->from($this->senderEmail)
            ->to($recipientEmail)
            ->subject('New Ticket Created')
            ->text('A new ticket has been created with the following details: Title: ' . $ticket->getTitle() . ' and Description: ' . $ticket->getDescription());

        $this->mailer->send($email);
    }
}
