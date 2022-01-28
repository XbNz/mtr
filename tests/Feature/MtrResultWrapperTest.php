<?php

namespace Xbnz\Mtr\Tests\Feature;

use Illuminate\Support\Collection;
use Xbnz\Mtr\MTR;
use Xbnz\Mtr\MtrHopDto;
use Xbnz\Mtr\MtrResult;

class MtrResultWrapperTest extends \PHPUnit\Framework\TestCase
{
    /** @test **/
    public function the_target_down_method_returns_true_if_clients_supplied_destination_doesnt_match_final_Hop(): void
    {
        // Arrange
        $mtrResult = $this->fakeMtrResult();
        $lastHop = array_key_last($mtrResult['hubs']);
        $mtrResult['hubs'][$lastHop]['host'] = '???';

        // Act
        $wrapper = new MtrResult($mtrResult);

        // Assert
        $this->assertTrue($wrapper->targetDown());
    }

    /** @test **/
    public function the_target_up_method_returns_true_if_clients_supplied_destination_matches_final_Hop(): void
    {
        // Arrange
        $mtrResult = $this->fakeMtrResult();
        $lastHop = array_key_last($mtrResult['hubs']);
        $mtrResult['hubs'][$lastHop]['host'] = 'alive.org';

        // Act
        $wrapper = new MtrResult($mtrResult);

        // Assert
        $this->assertTrue($wrapper->targetUp());
    }

    /** @test **/
    public function target_host_property_returns_correct_target_host(): void
    {
        // Arrange
        $mtrResult = $this->fakeMtrResult();
        $mtrResult['mtr']['dst'] = 'host.com';

        // Act
        $wrapper = new MtrResult($mtrResult);

        // Assert
        $this->assertEquals('host.com', $wrapper->targetHost);
    }

    /** @test **/
    public function hop_count_property_returns_correct_hop_count(): void
    {
        // Arrange
        $mtrResult = $this->fakeMtrResult();
        $realCount = count($mtrResult['hubs']);

        // Act
        $wrapper = new MtrResult($mtrResult);

        // Assert
        $this->assertEquals($realCount, $wrapper->hopCount);
    }


    /**
     * @test
     **/
    public function the_hops_method_returns_a_collection_of_hop_wrappers(): void
    {
        // Arrange
        $mtrResult = $this->fakeMtrResult();
        $firstHop = $mtrResult['hubs'][0];

        // Act
        $wrapper = new MtrResult($mtrResult);

        // Assert
        $resultHops = $wrapper->hops();

        $firstHopDto = $resultHops[0];

        $this->assertSame($firstHop['count'], $firstHopDto->hopPositionCount);
        $this->assertSame($firstHop['host'], $firstHopDto->hopHost);
        $this->assertSame($firstHop['Loss%'], $firstHopDto->packetLoss);
        $this->assertSame($firstHop['Drop'], $firstHopDto->droppedPackets);
        $this->assertSame($firstHop['Rcv'], $firstHopDto->receivedPackets);
        $this->assertSame($firstHop['Snt'], $firstHopDto->sentPackets);
        $this->assertSame($firstHop['Last'], $firstHopDto->lastRttValue);
        $this->assertSame($firstHop['Best'], $firstHopDto->lowestRttValue);
        $this->assertSame($firstHop['Avg'], $firstHopDto->averageRttValue);
        $this->assertSame($firstHop['Wrst'], $firstHopDto->highestRttValue);
        $this->assertSame($firstHop['StDev'], $firstHopDto->standardDeviation);
        $this->assertSame($firstHop['Gmean'], $firstHopDto->geometricMean);
        $this->assertSame($firstHop['Jttr'], $firstHopDto->jitter);
        $this->assertSame($firstHop['Javg'], $firstHopDto->averageJitter);
        $this->assertSame($firstHop['Jmax'], $firstHopDto->maximumJitter);
        $this->assertSame($firstHop['Jint'], $firstHopDto->interarrivalJitter);
    }



    private function fakeMtrResult($overrides = []): array
    {
        return array_merge([
            "mtr" => [
                "src" => "MacBook-Air",
                "dst" => "10.10.10.10",
                "tos" => 0,
                "tests" => 10,
                "psize" => "64",
                "bitpattern" => "0x00",
            ],
            "hubs" => [
                [
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
                ],
            ],
        ], $overrides);
    }
}