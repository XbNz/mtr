<?php

namespace Xbnz\Mtr\Tests\Unit;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Symfony\Component\Process\Process;
use Xbnz\Mtr\Actions\CreateParamStringFromDtoAction;
use Xbnz\Mtr\MtrOptions;
use Xbnz\Mtr\MtrOptionsConfigDto;

class CreateParamStringFromDtoActionTest extends \PHPUnit\Framework\TestCase
{
    /** @test **/
    public function it_takes_a_dto_and_returns_mtr_parameters_string(): void
    {
        // Arrange
        $action = new CreateParamStringFromDtoAction;

        // Act
        $paramString = $action(new MtrOptionsConfigDto(interval: 4, count: 10));

        // Assert
        $this->assertStringContainsString(MtrOptions::INTERVAL, $paramString);
        $this->assertStringContainsString(MtrOptions::COUNT, $paramString);
        $this->assertStringContainsString('4', $paramString);
        $this->assertStringContainsString('10', $paramString);
    }

    /** @test **/
    public function it_doesnt_halt_execution_if_no_options_are_configured(): void
    {
        $action = new CreateParamStringFromDtoAction;

        try {
            $paramString = $action(new MtrOptionsConfigDto());
        } catch (\Throwable $throwable) {
            $this->fail(
                'CreateParamStringFromDtoAction threw an unexpected exception: ' . $throwable->getMessage()
            );
        }

        $this->expectNotToPerformAssertions();
    }


    /** @test **/
    public function it_still_returns_a_string_with_default_parameters_even_if_no_additional_arguments_are_passed_by_the_calling_code(): void
    {
        // Arrange
        $action = new CreateParamStringFromDtoAction;

        // Act
        $paramString = $action(new MtrOptionsConfigDto());

        // Assert
        $this->assertNotEmpty($paramString);
    }



    /** @test **/
    public function lol(): void
    {
        // Arrange

        $options = ['--report-wide', '-']

        dd((new Process(['sudo', 'mtr', '1.1.1.1']))->get());

        $processes = LazyCollection::make([
            new Process(['sudo', 'mtr', '1.1.1.1']),

        ]);


        $test = $processes
            ->chunk(200)
            ->each($this->processChunk(...))
            ->flatten()
            ->map(fn(Process $process) => json_decode($process->getOutput(), true));

        dd($test->all());

        $this->expectNotToPerformAssertions();
    }

    private function processChunk(LazyCollection $processes): LazyCollection
    {
        return $processes
            ->each(fn(Process $process) => $process->start())
            ->each(fn(Process $process) => $process->wait());
    }
}