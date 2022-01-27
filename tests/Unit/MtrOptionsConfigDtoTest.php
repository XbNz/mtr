<?php

declare(strict_types=1);

namespace Xbnz\Mtr\Tests\Unit;

use Xbnz\Mtr\Exceptions\MtrConfigurationException;
use Xbnz\Mtr\MtrOptionsConfigDto;

final class MtrOptionsConfigDtoTest extends \PHPUnit\Framework\TestCase
{
    /** @test **/
    public function tcp_and_udp_are_mutually_exclusive(): void
    {
        $this->expectException(MtrConfigurationException::class);
        $dto = new MtrOptionsConfigDto(
            udp: true,
            tcp: true
        );
    }
}