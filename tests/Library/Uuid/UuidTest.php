<?php

namespace App\Tests\Library\Uuid;

use App\Library\Uuid\Uuid;
use App\Tests\BaseWebTestCase;

class UuidTest extends BaseWebTestCase
{
    /**
     * @throws \Exception
     */
    public function test_it_should_validate_valid_uuid()
    {
        // given
        $uuid = '290d00cd-623e-42a2-a068-0ccc52649369';

        // when
        $uuidObject = new Uuid($uuid);

        // then
        $this->assertNotEmpty($uuidObject);
    }

    /**
     * @throws \Exception
     */
    public function test_it_should_throw_exception_on_not_valid_uuid()
    {
        // given
        $uuid = '290d00cd-623e-42a2-a068-0ccc5264936911111';

        // then
        $this->expectException(\Exception::class);

        // when
        $uuidObject = new Uuid($uuid);
    }
}