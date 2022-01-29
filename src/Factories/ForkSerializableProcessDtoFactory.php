<?php

namespace Xbnz\Mtr\Factories;

use Symfony\Component\Process\Process;
use Xbnz\Mtr\ForkSerializableProcessDto;

class ForkSerializableProcessDtoFactory
{
    public static function fromMtrForkCallable(Process $process, string $host): ForkSerializableProcessDto
    {
        return new ForkSerializableProcessDto(
            $host,

            $process->getOutput() === '' ? null :
            json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR),

            $process->getErrorOutput() === '' ? null :
            $process->getErrorOutput(),

            $process->getCommandLine(),

            $process->getExitCode()
        );
    }
}