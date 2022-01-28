<?php

namespace Xbnz\Mtr\Tests\Unit;

use Symfony\Component\VarDumper\Dumper\DataDumperInterface;
use Xbnz\Mtr\Factories\MtrHopFactory;

class MtrHopFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @test **/
    public function the_factory_fromRawHop_method_will_reject_invalid_autonomous_system_numbers(): void
    {
        // Arrange
        $hop = $this->fakeRawHop(['ASN' => 'AS???']);

        // Act
        $hopDto = MtrHopFactory::fromRawHop($hop);

        // Assert
        $this->assertNull($hopDto->asNumber);
    }


    /** @test **/
    public function the_factory_fromRawHop_method_will_accept_valid_autonomous_system_numbers(): void
    {
        // Arrange
        $hop = $this->fakeRawHop(['ASN' => 'AS777']);

        // Act
        $hopDto = MtrHopFactory::fromRawHop($hop);

        // Assert
        $this->assertEquals('AS777', $hopDto->asNumber);
    }



    private function fakeRawHop($overrides = []): array
    {
        return array_merge([
            "count" => 1,
            "host" => "ax88u",
            "ASN" => "AS???",
            "Loss%" => 0.0,
            "Drop" => 0,
            "Rcv" => 10,
            "Snt" => 10,
            "Last" => 4.385,
            "Best" => 3.432,
            "Avg" => 4.943,
            "Wrst" => 12.453,
            "StDev" => 2.675,
            "Gmean" => 4.567,
            "Jttr" => 0.564,
            "Javg" => 1.257,
            "Jmax" => 8.571,
            "Jint" => 8.474,
        ], $overrides);
    }
}