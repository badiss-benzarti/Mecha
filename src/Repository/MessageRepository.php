<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;   

class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function findByRecipientWithSender(Users $user): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.sender', 's')
            ->addSelect('s')
            ->leftJoin('m.ticket', 't')
            ->addSelect('t')
            ->where('m.recipient = :user')
            ->orWhere('m.sender = :user')
            ->setParameter('user', $user)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countUnreadMessages($user): int
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.recipient = :user')
            ->andWhere('m.isRead = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function searchByUserNames(string $query)
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.recipient', 'u')
            ->where('CONCAT(u.prenom, \' \', u.nom) LIKE :query')
            ->orWhere('u.prenom LIKE :query')
            ->orWhere('u.nom LIKE :query')
            ->setParameter('query', '%' . addcslashes($query, '%_') . '%')
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

        public function searchByUsername(string $query, int $senderId = null): array
        {
            $qb = $this->createQueryBuilder('m')
                ->leftJoin('m.recipient', 'r')
                ->addSelect('r')
                ->leftJoin('m.ticket', 't')
                ->addSelect('t')
                ->where('CONCAT(r.prenom, \' \', r.nom) LIKE :query')
                ->setParameter('query', '%' . $query . '%')
                ->orderBy('m.createdAt', 'DESC');

            if ($senderId) {
                $qb->andWhere('m.sender = :sender')
                ->setParameter('sender', $senderId);
            }

            return $qb->getQuery()->getResult();
        }
}
