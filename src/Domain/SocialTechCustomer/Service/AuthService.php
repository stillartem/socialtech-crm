<?php

namespace App\Domain\SocialTechCustomer\Service;

use App\Domain\Core\ValueObject\Uuid4;
use App\Domain\SocialTechCustomer\Entity\AuthToken;
use App\Domain\SocialTechCustomer\Exception\CustomerNotFoundException;
use App\Domain\SocialTechCustomer\Repository\CustomerRepositoryInterface;
use App\Domain\SocialTechCustomer\ValueObject\Age;
use App\Domain\SocialTechCustomer\ValueObject\FirstName;
use App\Domain\SocialTechCustomer\ValueObject\HashedPassword;
use App\Domain\SocialTechCustomer\ValueObject\LastName;
use App\Domain\SocialTechCustomer\ValueObject\NickName;
use App\Domain\SocialTechCustomer\ValueObject\User;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class AuthService
{
    /** @var CustomerRepositoryInterface */
    private CustomerRepositoryInterface $customerRepository;

    /** @var AuthTokenService */
    private AuthTokenService $authTokenService;

    public function __construct(CustomerRepositoryInterface $customerRepository, AuthTokenService $authTokenService)
    {
        $this->customerRepository = $customerRepository;
        $this->authTokenService = $authTokenService;
    }

    /**
     * @param NickName $nickName
     * @param string $plainPassword
     *
     * @return AuthToken
     * @throws CustomerNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function login(NickName $nickName, string $plainPassword): AuthToken
    {
        $user = $this->customerRepository->getCustomerByNickName($nickName);
        if (!$user->getHashedPassword()->verify($plainPassword)) {
            throw CustomerNotFoundException::forNickName($nickName);
        }

        return $this->authTokenService->createNewTokenForUser($user->getNickName(), $user->getUuid());
    }

    /**
     * @param array $userData
     *
     * @return AuthToken
     * @throws \Exception
     */
    public function register(array $userData): AuthToken
    {
        $hashedPassword = HashedPassword::generate($userData['password'] ?? '');
        $user = new User(
            FirstName::fromScalar($userData['first_name']),
            LastName::fromScalar($userData['last_name']),
            NickName::fromScalar($userData['nickname']),
            Age::fromScalar($userData['age']),
            HashedPassword::generateFromHash($hashedPassword),
            Uuid4::generate()
        );
        $this->customerRepository->saveCustomer($user);

        return $this->authTokenService->createNewTokenForUser($user->getNickName(), $user->getUuid());
    }
}
