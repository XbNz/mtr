<?php

declare(strict_types=1);

namespace Xbnz\Mtr\Tests\Feature;

use IPTools\IP;
use IPTools\Network;
use Webmozart\Assert\Assert;
use Xbnz\Mtr\MTR;
use Xbnz\Mtr\MtrOptions;
use Xbnz\Mtr\MtrOptionsConfigDto;

class MtrTest extends \PHPUnit\Framework\TestCase
{
    /** @test **/
    public function it_saves_and_trashes_the_desired_values(): void
    {
        $shouldBeTrashed = [
            'this is obviously not a hostname or ip',
            '1. 1.1 .1',
            'https://google.com',
        ];

        $shouldBeSaved = [
            '1.1.1.1',
            '8.8.8.8/24',
            '01283092182222',
            0x1111111111,
            'google.com',
        ];

        $mtrObject = MTR::build()->withIp(...$shouldBeTrashed, ...$shouldBeSaved);
        $reflectOnHostsProperty = (new \ReflectionProperty(MTR::class, 'hosts'))->getValue($mtrObject);

        $this->assertCount(260, $reflectOnHostsProperty);
    }

    /** @test **/
    public function fucking_around_for_now(): void
    {
        // Arrange
        MTR::build(new MtrOptionsConfigDto(interval: '0.00000001', count: 3333))
            ->withIp('1.1.1.1', '8.8.8.8')
            ->execute();


        // Act

        // Assert
    }

}