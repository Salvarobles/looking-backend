<?php

namespace App\Repository;

use App\Entity\Room;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Room>
 *
 * @method Room|null find($id, $lockMode = null, $lockVersion = null)
 * @method Room|null findOneBy(array $criteria, array $orderBy = null)
 * @method Room[]    findAll()
 * @method Room[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Room::class);
        $this->entityManager = $entityManager;
    }

//    /**
//     * @return Room[] Returns an array of Room objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Room
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

public function findAvailableRooms($idAccommodation, $startDate, $endDate): array 
        {
            $rsm = new ResultSetMapping();
            $rsm->addEntityResult(Room::class, 'r');
            $rsm->addFieldResult('r', 'id', 'id');
            $rsm->addFieldResult('r', 'maximum_capacity', 'maximumCapacity');
        
            $sql = "SELECT room.id, room.maximum_capacity
                    FROM room 
                    INNER JOIN accommodation ON room.accommodation_id = accommodation.id 
                    WHERE accommodation.id = :accommodationId 
                    AND NOT EXISTS ( 
                        SELECT 1
                        FROM reservation 
                        WHERE reservation.room_id = room.id
                        AND (reservation.start_date <= :endDate AND reservation.end_date >= :startDate)
                    )";
        
            $query = $this->entityManager->createNativeQuery($sql, $rsm);
            $query->setParameter('accommodationId', $idAccommodation);
            $query->setParameter('startDate', $startDate);
            $query->setParameter('endDate', $endDate);
        
            return $query->getResult();
        }

}
