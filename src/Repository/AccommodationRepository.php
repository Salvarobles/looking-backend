<?php

namespace App\Repository;

use App\Entity\Accommodation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Accommodation>
 *
 * @method Accommodation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Accommodation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Accommodation[]    findAll()
 * @method Accommodation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccommodationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Accommodation::class);
    }

//    /**
//     * @return Accommodation[] Returns an array of Accommodation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Accommodation
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function getFiveBestAccommodation(): array
    {
        return $this->createQueryBuilder('a')
            ->select('a.id', 'a.name', 'a.typeAccommodation', 'a.img', 'AVG(r.rating) AS averageRating')
            ->leftJoin('a.reviews', 'r')
            ->andWhere('a.hidden = :hidden')
            ->setParameter('hidden', 0)
            ->groupBy('a.id')
            ->orderBy('averageRating', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

    public function getAccommodationsHidden(): array
    {
        return $this->createQueryBuilder('a')
            ->select('a.id', 'a.name', 'a.typeAccommodation', 'a.email', 'a.img', 'a.checkIn', 'a.checkOut', 'a.description')
            ->where('a.hidden = :hidden')
            ->setParameter('hidden', 1)
            ->getQuery()
            ->getResult();
    }

    
    public function getAccommodations(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('city', 'city');
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('country', 'country');
        $rsm->addScalarResult('typeAccommodation', 'typeAccommodation');
        $rsm->addScalarResult('img', 'img');
        $rsm->addScalarResult('price', 'price');
        $rsm->addScalarResult('maximumCapacity', 'maximumCapacity');
    
        $sql = "
            SELECT a.id, c.name as city, a.name, a.country, a.type_accommodation as typeAccommodation, a.img, a.price, 
                   (SELECT r.maximum_capacity 
                    FROM room r 
                    WHERE r.accommodation_id = a.id 
                    ORDER BY r.id ASC 
                    LIMIT 1) as maximumCapacity
            FROM accommodation a
            LEFT JOIN city c ON a.city_id = c.id
            WHERE a.hidden = :hidden
        ";
    
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('hidden', 0);
    
        return $query->getResult();
    }

    public function getAccommodationsSearch($cityName)
    {
        return $this->createQueryBuilder('a')
            ->select('a.id', 'c.name as city', 'a.name', 'a.country', 'a.typeAccommodation', 'a.img', 'a.price')
            ->leftJoin('a.city', 'c')
            ->andWhere('LOWER(c.name) = :city')
            ->setParameter('city', strtolower($cityName))
            ->getQuery()
            ->getResult();
    }

    public function getAccommodationExpensive()
    {
        return $this->createQueryBuilder('a')
            ->select('MAX(a.price) AS max_price')
            ->getQuery()
            ->getSingleScalarResult();
    }    
}