<?php
// src/Repository/TicketRepository.php

namespace App\Repository;

use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

class TicketRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Ticket::class);
        $this->em = $em;
    }

    public function save(Ticket $ticket, bool $flush = true): void
    {
        $this->em->persist($ticket);
        if ($flush) {
            $this->em->flush();
        }
    }

    public function searchTickets(string $query): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.user', 'u')
            ->addSelect('u')
            ->where('t.title LIKE :query')
            ->orWhere('t.description LIKE :query')
            ->orWhere('t.status LIKE :query')
            ->orWhere('u.nom LIKE :query')
            ->orWhere('u.prenom LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
    public function findUserTicket(int $id, User $user)
{
    return $this->createQueryBuilder('t')
        ->where('t.id = :id')
        ->andWhere('t.user = :user')
        ->setParameter('id', $id)
        ->setParameter('user', $user)
        ->getQuery()
        ->getOneOrNullResult();
}
public function searchByTitle(string $title): array
{
    return $this->createQueryBuilder('t')
        ->leftJoin('t.user', 'u')
        ->addSelect('u')
        ->where('t.title LIKE :title')
        ->setParameter('title', '%' . $title . '%')
        ->orderBy('t.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
}
}
