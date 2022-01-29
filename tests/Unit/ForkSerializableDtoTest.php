<?php

namespace Xbnz\Mtr\Tests\Unit;

use Symfony\Component\Process\Process;
use Xbnz\Mtr\Factories\ForkSerializableProcessDtoFactory;

class ForkSerializableDtoTest extends \PHPUnit\Framework\TestCase
{
    /** @test **/
    public function it_saves_null_into_dto_if_value_is_empty(): void
    {
        // Arrange
        $process = new Process(['gibberish ', 'expecting error output ']);
        $process->run();

        // Act
        $dto = ForkSerializableProcessDtoFactory::fromMtrForkCallable($process, '1.1.1.1');

        // Assert
        $this->assertNull($dto->output);
        $this->assertNotNull($dto->errorOutput);
        $this->assertSame('1.1.1.1', $dto->host);
    }

}