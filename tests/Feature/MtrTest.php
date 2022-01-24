<?php

namespace Xbnz\Mtr\Tests\Feature;

class MtrTest extends \PHPUnit\Framework\TestCase
{
    /** @test **/
    public function messing_around(): void
    {
        // Arrange
        $mtr = MTR::build([
            MtrOptions::INTERVAL => 0.1,
            MtrOptions::OUTPUTFORMAT => 'json'
        ]);

        // Act

        $results = $mtr->withIp([
            '1.1.1.1',
            '8.8.8.8',
        ]);

        // Assert

        $this->containsOnlyInstancesOf(MtrHop::class, $results);

    }
}