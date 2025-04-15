<?php

namespace App\Repository;

use App\Entity\Participation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participation::class);
    }


    public function findAllWithRelations()
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.idUser', 'u')
            ->addSelect('u')
            ->leftJoin('p.idFormation', 'f')
            ->addSelect('f')
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllWithUsersAndFormations()
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.idUser', 'u')
            ->leftJoin('p.idFormation', 'f')
            ->addSelect('u')
            ->addSelect('f')
            ->getQuery()
            ->getResult();
    }

    // Add custom methods as needed
}
