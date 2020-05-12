<?php

namespace App\Domain\SocialTechCustomer\Repository;

use App\Domain\Core\ValueObject\Uuid4;
use App\Domain\SocialTechCustomer\Exception\CustomerAlreadyExistException;
use App\Domain\SocialTechCustomer\Exception\CustomerNotFoundException;
use App\Domain\SocialTechCustomer\ValueObject\Age;
use App\Domain\SocialTechCustomer\ValueObject\FirstName;
use App\Domain\SocialTechCustomer\ValueObject\HashedPassword;
use App\Domain\SocialTechCustomer\ValueObject\LastName;
use App\Domain\SocialTechCustomer\ValueObject\NickName;
use App\Domain\SocialTechCustomer\ValueObject\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CustomerRepository implements CustomerRepositoryInterface
{
    private string $pathToCustomerStorage;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->pathToCustomerStorage = $parameterBag->get('path_to_customer_storage');
    }

    /**
     * @param NickName $nickName
     *
     * @return User
     * @throws CustomerNotFoundException
     */
    public function getCustomerByNickName(NickName $nickName): User
    {
        $path = $this->pathToCustomerStorage . DIRECTORY_SEPARATOR . $nickName . '.json';
        if (!$this->checkIfExist($path)) {
            throw CustomerNotFoundException::forNickName((string)$nickName);
        }
        $userData = json_decode(\file_get_contents($path), true);

        return new User(
            FirstName::fromScalar($userData['firstName']),
            LastName::fromScalar($userData['lastName']),
            NickName::fromScalar($userData['nickName']),
            Age::fromScalar($userData['age']),
            HashedPassword::generateFromHash($userData['hashedPassword']),
            Uuid4::fromString($userData['uuid'])
        );
    }

    public function saveCustomer(User $user): void
    {
        $path = $this->pathToCustomerStorage . DIRECTORY_SEPARATOR . $user->getNickName() . '.json';
        if ($this->checkIfExist($path)) {
            throw CustomerAlreadyExistException::withNickName($user->getNickName());
        }
        file_put_contents($path, $user->asJson());
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    private function checkIfExist(string $path): bool
    {
        return file_exists($path);
    }
}
