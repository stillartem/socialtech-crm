<?php

namespace App\Domain\SocialTechCustomer\Repository;

use DateTime;
use Doctrine\Common\Persistence\ManagerRegistry;
use App\Domain\SocialTechCustomer\Entity\AuthToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class AuthTokenRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthToken::class);
    }

    /**
     * @param AuthToken $authToken
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(AuthToken $authToken): void
    {
        $this->_em->persist($authToken);
        $this->_em->flush();
    }

    public function test()
    {

    }

    /**
     * @param string $token
     *
     * @return bool
     * @throws NonUniqueResultException
     */
    public function validateToken(string $token): bool
    {
        $authToken = $this->createQueryBuilder('at')
            ->where('at.token = :token')
            ->andWhere('at.expireAt > :today')
            ->setParameter('token', $token)
            ->setParameter('today', new DateTime('now'))
            ->getQuery()
            ->getOneOrNullResult();


        return !($authToken === null);
    }
}
