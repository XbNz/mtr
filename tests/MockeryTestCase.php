<?php

namespace Xbnz\Mtr\Tests;

class MockeryTestCase extends \PHPUnit\Framework\TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        \Mockery::close();
    }
}