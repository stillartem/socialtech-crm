<?php

namespace App\Tests\Domain\Core\EventSubscriber;

use App\Tests\BaseWebTestCase;

class ExceptionSubscriberTest extends BaseWebTestCase
{
    public function test_it_return_formatted_customer_not_found_error_response()
    {
        $client = static::createClient(
            [
                'environment' => 'test',
                'debug'       => true,
            ]
        );

        $client->request('GET', '/v1/customers/11111111');

        $response = $client->getResponse();

        $this->assertEquals($response::HTTP_NOT_FOUND, $response->getStatusCode());

        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);

        $this->assertEquals('page_not_found', $content['code']);
    }
}
