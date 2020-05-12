<?php

namespace App\Domain\SocialTechCustomer\Service;

use App\Domain\Core\ValueObject\Uuid4;
use App\Domain\SocialTechCustomer\Entity\AuthToken;
use App\Domain\SocialTechCustomer\Repository\AuthTokenRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AuthTokenService
{
    /** @var AuthTokenRepository */
    private AuthTokenRepository $authTokenRepository;

    /** @var string */
    private string $tokenExpireAt;

    public function __construct(AuthTokenRepository $authTokenRepository, ParameterBagInterface $parameterBag)
    {
        $this->authTokenRepository = $authTokenRepository;
        $this->tokenExpireAt = $parameterBag->get('authTokenLifeTime');
    }

    /**
     * @param string $nickName
     *
     * @param Uuid4 $userId
     *
     * @return AuthToken
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createNewTokenForUser(string $nickName, Uuid4 $userId): AuthToken
    {
        $expireAt = new \DateTime($this->tokenExpireAt);
        $authToken = (new AuthToken())
            ->setNickName($nickName)
            ->setUserId($userId)
            ->setExpireAt($expireAt);
        $this->authTokenRepository->save($authToken);

        return $authToken;
    }
}
