<?php

namespace App\Tests\Port\Controller;

use App\Tests\BaseWebTestCase;

class AuthControllerTest extends BaseWebTestCase
{
    /**
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();
        self::purgeDatabase();
    }

    /**
     * @throws \Exception
     */
    public function test_it_should_login_user_with_correct_creds()
    {
        // when
        $this->getWebClient()->request(
            'POST', '/api/v1/auth/login', [], [], [], json_encode(
                [
                    'analytic_data' => [
                        'source_label' => 'login_page',
                        'id' => 1,
                        'date_created' => '2018-04-10 12:09:07',
                    ],
                    'login_data' => [
                        'nickname' => 'existingUser',
                        'password' => 'test',
                    ],
                ]
            )
        );


        // then
        $this->assertEquals(200, $this->getWebClient()->getResponse()->getStatusCode());
        $content = $this->getWebClient()->getResponse()->getContent();
        $this->assertJson($content);
        $content = json_decode($content, true);
        $this->assertArrayHasKey('token', $content);
        $this->assertArrayHasKey('expireAt', $content);

        //$this->assertEmpty($content);
    }

    /**
     * @throws \Exception
     */
    public function test_it_should_not_login_user()
    {
        // when
        $this->getWebClient()->request(
            'POST', '/api/v1/auth/login', [], [], [], json_encode(
                [
                    'analytic_data' => [
                        'source_label' => 'login_page',
                        'id' => 1,
                        'date_created' => '2018-04-10 12:09:07',
                    ],
                    'login_data' => [
                        'nickname' => 'notExistingUser',
                        'password' => '123',
                    ],
                ]
            )
        );


        // then
        $this->assertEquals(400, $this->getWebClient()->getResponse()->getStatusCode());
        $content = $this->getWebClient()->getResponse()->getContent();
        $this->assertJson($content);
        $content = json_decode($content, true);
        $this->assertEquals('invalid.argument', $content['code']);
    }

    public function test_it_should_register_customer()
    {
        // when
        $this->getWebClient()->request(
            'POST', '/api/v1/auth/registration', [], [], [], json_encode(
                [
                    'analytic_data' => [
                        'source_label' => 'registration',
                        'id' => 1,
                        'date_created' => '2018-04-10 12:09:07',
                    ],
                    'user_data' => [
                        'nickname' => 'notExistingUser',
                        'password' => '12345',
                        'first_name' => 'test',
                        'last_name' => 'test',
                        'age' => 18,
                    ],
                ]
            )
        );
        $this->assertEquals(200, $this->getWebClient()->getResponse()->getStatusCode());
        $content = $this->getWebClient()->getResponse()->getContent();
        $this->assertJson($content);
        $content = json_decode($content, true);
        $this->assertArrayHasKey('token', $content);
        $this->assertArrayHasKey('expireAt', $content);

        $path = self::$container->getParameter('path_to_customer_storage').'/notExistingUser.json';
        $this->assertFileExists($path);
    }

    public function test_it_should_not_register_customer_wrong_pass_format()
    {
        // when
        $this->getWebClient()->request(
            'POST', '/api/v1/auth/registration', [], [], [], json_encode(
                [
                    'analytic_data' => [
                        'source_label' => 'registration',
                        'id' => 1,
                        'date_created' => '2018-04-10 12:09:07',
                    ],
                    'user_data' => [
                        'nickname' => 'notExistingUser',
                        'password' => '12',
                        'first_name' => 'test',
                        'last_name' => 'test',
                        'age' => 18,
                    ],
                ]
            )
        );
        $this->assertEquals(400, $this->getWebClient()->getResponse()->getStatusCode());
        $content = $this->getWebClient()->getResponse()->getContent();
        $this->assertJson($content);
        $content = json_decode($content, true);
        $this->assertEquals('field.password.invalid', $content['code']);
    }
}
