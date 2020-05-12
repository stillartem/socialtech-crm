<?php

namespace App\Tests\Port\Controller;

use App\Domain\Core\ValueObject\Uuid4;
use App\Tests\BaseWebTestCase;


class TrackingControllerTest extends BaseWebTestCase
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
    public function test_it_should_track_user_action()
    {
        // when
        $this->getWebClient()->request(
            'POST',
            '/api/v1/tracking',
            [],
            [],
            ['HTTP_X-API-TOKEN' => 'existing_token'],
            json_encode(
                [
                    'source_label' => 'test_source',
                    'id' => 1,
                    'date_created' => '2018-04-10 12:09:07',
                    'id_user' => (string)Uuid4::generate(),
                ]
            )
        );


        // then
        $this->assertEquals(204, $this->getWebClient()->getResponse()->getStatusCode());
        $content = $this->getWebClient()->getResponse()->getContent();

        $this->assertEmpty($content);
    }

    /**
     * @throws \Exception
     */
    public function test_it_should_not_valid_user_id()
    {
        // when
        $this->getWebClient()->request(
            'POST',
            '/api/v1/tracking',
            [],
            [],
            ['HTTP_X-API-TOKEN' => 'existing_token'],
            json_encode(
                [
                    'source_label' => 'test_source',
                    'id' => 1,
                    'date_created' => '2018-04-10 12:09:07',
                    'id_user' => '123',
                ]
            )
        );


        // then
        $this->assertEquals(400, $this->getWebClient()->getResponse()->getStatusCode());
        $content = $this->getWebClient()->getResponse()->getContent();
        $this->assertJson($content);
        $content = json_decode($content, true);
        $this->assertEquals('field.uuid.invalid', $content['code']);
    }

    /**
     * @throws \Exception
     */
    public function test_it_should_wrong_token()
    {
        // when
        $this->getWebClient()->request(
            'POST',
            '/api/v1/tracking',
            [],
            [],
            ['HTTP_X-API-TOKEN' => 'not_exiting_token'],
            json_encode(
                [
                    'source_label' => 'test_source',
                    'id' => 1,
                    'date_created' => '2018-04-10 12:09:07',
                    'id_user' => '123',
                ]
            )
        );


        // then
        $this->assertEquals(403, $this->getWebClient()->getResponse()->getStatusCode());
        $content = $this->getWebClient()->getResponse()->getContent();
        $this->assertJson($content);
        $content = json_decode($content, true);
        $this->assertEquals('auth.auth_token_is_wrong', $content['code']);
    }
}
