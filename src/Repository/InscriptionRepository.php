<?php

namespace App\Repository;

use App\Entity\Inscription;
use App\Enum\Classe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SortDirection;

/**
 * @extends ServiceEntityRepository<Inscription>
 */
class InscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inscription::class);
    }

    /**
     * @return Inscription[]
     */
    public function findFiltered(?Classe $classe, ?string $email): array
    {
        $qb = $this->createQueryBuilder('i')->orderBy('i.dateInscription', SortDirection::Descending);

        if ($classe !== null) {
            $qb->andWhere('i.classe = :classe')->setParameter('classe', $classe);
        }

        if ($email !== null && $email !== '') {
            $qb->andWhere('i.email LIKE :email')->setParameter('email', '%'.$email.'%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array{total: int, recent: int, byClass: array<string, int>}
     */
    public function statistics(): array
    {
        $total = (int) $this->count([]);

        $recent = (int) $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.dateInscription >= :since')
            ->setParameter('since', new \DateTimeImmutable('-24 hours'))
            ->getQuery()
            ->getSingleScalarResult();

        $rows = $this->createQueryBuilder('i')
            ->select('i.classe AS classe, COUNT(i.id) AS count')
            ->groupBy('i.classe')
            ->orderBy('i.classe', SortDirection::Ascending)
            ->getQuery()
            ->getResult();

        $byClass = [];
        foreach ($rows as $row) {
            $key = $row['classe'] instanceof Classe ? $row['classe']->value : (string) $row['classe'];
            $byClass[$key] = (int) $row['count'];
        }

        return ['total' => $total, 'recent' => $recent, 'byClass' => $byClass];
    }
}
