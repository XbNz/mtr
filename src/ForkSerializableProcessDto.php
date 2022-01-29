<?php

namespace Xbnz\Mtr;

class ForkSerializableProcessDto
{
    public function __construct(
        public readonly string $host,
        public readonly ?array $output,
        public readonly ?string $errorOutput,
        public readonly ?string $commandLine,
        public readonly ?int $exitCode,
    )
    {}
}