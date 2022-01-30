<?php

declare(strict_types=1);

namespace Xbnz\Mtr\Tests\Feature;

use Cassandra\Uuid;
use IPTools\IP;
use IPTools\Network;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;
use Xbnz\Mtr\ForkSerializableProcessDto;
use Xbnz\Mtr\MTR;
use Xbnz\Mtr\MtrHopDto;
use Xbnz\Mtr\MtrOptions;
use Xbnz\Mtr\MtrOptionsConfigDto;
use Xbnz\Mtr\MtrResult;
use Xbnz\Mtr\Tests\MockeryTestCase;

class MtrTest extends MockeryTestCase
{
    /** @test **/
    public function it_saves_and_trashes_the_desired_values(): void
    {
        $shouldBeTrashed = [
            'this is obviously not a hostname or ip',
            '1. 1.1 .1',
            'https://google.com',
            'google.com' // Duplicate
        ];

        $shouldBeSaved = [
            '1.1.1.1',
            '8.8.8.8/24',
            '01283092182222',
            0x1111111,
            1111111111111111,
            'google.com',
        ];

        $mtrObject = MTR::build()->withIp(...$shouldBeTrashed, ...$shouldBeSaved);
        $reflectOnHostsProperty = (new \ReflectionProperty(MTR::class, 'hosts'))->getValue($mtrObject);

        $this->assertCount(261, $reflectOnHostsProperty);
    }


    /** @test **/
    public function raw_collection_is_returned_with_expected_keys(): void
    {
        // Arrange
        $mtr = MTR::build(new MtrOptionsConfigDto(count: 1, noDns: true))
            ->withIp('1.1.1.1');

        // Act
        $collection = $mtr->raw();

        // Assert

        $collection
            ->each(fn($report) => $this->assertArrayHasKey('mtr', $report))
            ->each(fn($report) => $this->assertArrayHasKey('hubs', $report));
    }

    /** @test **/
    public function it_throws_exception_if_no_host_is_provided(): void
    {
        // Arrange
        $mtr = MTR::build(new MtrOptionsConfigDto(count: 1, noDns: true));

        // Act & assert
        $this->expectException(InvalidArgumentException::class);
        $mtr->raw();
    }

    /** @test **/
    public function console_timeout_may_only_be_positive(): void
    {
        // Arrange
        $mtr = MTR::build(new MtrOptionsConfigDto(count: 1, noDns: true));

        // Act & assert
        $this->expectException(InvalidArgumentException::class);
        $mtr->raw(-300);
    }


    /** @test **/
    public function it_logs_errors_from_the_console(): void
    {
        $this->expectNotToPerformAssertions();
        $mockLogger = \Mockery::mock(NullLogger::class);
        $mockLogger->shouldReceive('error')->twice();

        $mtr = MTR::build(new MtrOptionsConfigDto(noDns: true, count: 1), $mockLogger);

        $hostsProperty = new \ReflectionProperty($mtr, 'hosts');

        $hostsProperty->setAccessible(true);
        $hostsProperty->setValue($mtr,
            [
                'these should bypass the valid host checks',
                'and make the process fail, which is what we need to test logging',
            ]
        );

        $mtr->raw();

    }

    /** @test **/
    public function the_wrap_method_returns_a_collection_of_one_result_class_for_single_queries(): void
    {
        // Arrange
        $mtr = MTR::build(new MtrOptionsConfigDto(noDns: true, count: 1))
            ->withIp('1.1.1.1');

        // Act
        $collectionOfOneMtrResult = $mtr->wrap();

        // Assert

        $this->assertContainsOnlyInstancesOf(MtrResult::class, $collectionOfOneMtrResult);
        $this->assertContainsOnlyInstancesOf(MtrHopDto::class, $collectionOfOneMtrResult['1.1.1.1']->hops());

        $this->assertEquals('1.1.1.1', $collectionOfOneMtrResult['1.1.1.1']->targetHost);
        $this->assertEquals(
            $collectionOfOneMtrResult['1.1.1.1']->hopCount,
            count($collectionOfOneMtrResult['1.1.1.1']->hops())
        );
    }

    /** @test **/
    public function the_wrap_method_returns_a_collection_of_custom_result_classes_for_bulk_queries(): void
    {
        // Arrange
        $mtrResults = MTR::build(new MtrOptionsConfigDto(noDns: true, count: 1))
            ->withIp('8.8.8.8/27');

        // Act
        $collectionOfManyMtrResults = $mtrResults->wrap();

        // Assert
        $this->assertContainsOnlyInstancesOf(MtrResult::class, $collectionOfManyMtrResults);

        foreach ($collectionOfManyMtrResults as $mtrResult) {
            $this->assertContainsOnlyInstancesOf(MtrHopDto::class, $mtrResult->hops());

            $this->assertEquals(
                $mtrResult->hopCount,
                count($mtrResult->hops())
            );
        }
    }


    static $test = false;
    /** @test **/
    public function the_optional_callable_is_executed(): void
    {
        $mtr = MTR::build(new MtrOptionsConfigDto(noDns: true, count: 1))
            ->withIp('8.8.8.8');

        try {
            $mtr->raw(callback: function (ForkSerializableProcessDto $dto) {
                Assert::isInstanceOf($dto, ForkSerializableProcessDto::class);
            });
        } catch (InvalidArgumentException) {
            $this->fail('Mozart assertions failed');
        }

        $this->assertTrue(true);
    }
}