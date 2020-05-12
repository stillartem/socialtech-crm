<?php

namespace App\Tests\Domain\SocialTechCustomer\Service;

use App\Domain\Core\Exception\BadDataException;
use App\Domain\SocialTechCustomer\Exception\CustomerNotFoundException;
use App\Domain\SocialTechCustomer\Repository\CustomerRepositoryInterface;
use App\Domain\SocialTechCustomer\Service\AuthService;
use App\Domain\SocialTechCustomer\Service\AuthTokenService;
use App\Tests\BaseWebTestCase;
use App\Tests\Helper\RepositoryMock\CustomerRepository;
use PHPUnit\Framework\MockObject\MockObject;

class AuthServiceTest extends BaseWebTestCase
{
    /** @var AuthService */
    private $authService;

    /** @var CustomerRepositoryInterface | MockObject */
    private $repository;

    /** @var AuthTokenService | MockObject */
    private $tokenService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository =
            $this->getMockBuilder(CustomerRepositoryInterface::class)->disableOriginalConstructor()->getMock();
        $this->tokenService =
            $this->getMockBuilder(AuthTokenService::class)->disableOriginalConstructor()->getMock();
        $this->authService = new AuthService($this->repository, $this->tokenService);
    }

    public function test_it_should_login_correct()
    {
        $user = CustomerRepository::getTestUser();
        $this->repository->expects($this->once())->method('getCustomerByNickName')->willReturn($user);
        $this->tokenService->expects($this->once())->method('createNewTokenForUser');

        $this->authService->login($user->getNickName(), 'test');
    }

    public function test_it_should_throw_exception_on_login()
    {
        $user = CustomerRepository::getTestUser();
        $this->repository->expects($this->once())->method('getCustomerByNickName')->willReturn($user);

        $this->expectException(CustomerNotFoundException::class);

        $this->authService->login($user->getNickName(), 'wrong_pass');

    }

    public function test_it_should_register_correct()
    {
        $user = CustomerRepository::getTestUser();
        $userData = [
            'first_name' => (string)$user->getFirstName(),
            'last_name' => (string)$user->getLastName(),
            'nickname' => (string)$user->getNickName(),
            'password' => 'test',
            'age' => 18,
        ];
        $this->repository->expects($this->once())->method('saveCustomer');
        $this->tokenService->expects($this->once())->method('createNewTokenForUser')->with($user->getNickName());

        $this->authService->register($userData);
    }

    public function test_it_should_throw_exception_for_bad_registration_data()
    {
        $user = CustomerRepository::getTestUser();
        $userData = [
            'first_name' => (string)$user->getFirstName(),
            'last_name' => (string)$user->getLastName(),
            'nickname' => (string)$user->getNickName(),
            'password' => 'test',
            'age' => 17,
        ];
        $this->repository->expects($this->never())->method('saveCustomer');
        $this->tokenService->expects($this->never())->method('createNewTokenForUser')->with($user->getNickName());

        $this->expectException(BadDataException::class);
        $this->authService->register($userData);

    }

}
