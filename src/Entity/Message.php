<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table(name: "messages")]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    #[Groups(['message'])]
    private ?int $id = null;

    #[ORM\Column(type: "text")]
    #[Groups(['message'])]
    #[Assert\NotBlank(message: "Message content cannot be empty.")]
    #[Assert\Length(
        min: 2,
        max: 1000,
        minMessage: "Message must be at least {{ limit }} characters long",
        maxMessage: "Message cannot be longer than {{ limit }} characters"
    )]
    private string $content;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    #[Groups(['message'])]
    private bool $isRead = false;

    #[ORM\ManyToOne(targetEntity: Users::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: "sender_id", referencedColumnName: "id")]
    #[Groups(['message'])]
    private ?Users $sender = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: "recipient_id", referencedColumnName: "id")]
    #[Groups(['message'])]
    private ?Users $recipient = null;

    #[ORM\ManyToOne(targetEntity: Ticket::class)]
    #[ORM\JoinColumn(name: "ticket_id", referencedColumnName: "ticket_id")]
    #[Groups(['message'])]
    private ?Ticket $ticket = null;

    #[ORM\Column(type: "datetime")]
    #[Groups(['message'])]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSender(): ?Users
    {
        return $this->sender;
    }

    public function setSender(?Users $sender): self
    {
        $this->sender = $sender;
        return $this;
    }

    public function getRecipient(): ?Users
    {
        return $this->recipient;
    }

    public function setRecipient(?Users $recipient): self
    {
        $this->recipient = $recipient;
        return $this;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): self
    {
        $this->ticket = $ticket;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;
        return $this;
    }
}
