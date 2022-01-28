<?php

declare(strict_types=1);

namespace Xbnz\Mtr\Factories;

use Xbnz\Mtr\MtrHopDto;
use Xbnz\Mtr\MtrResult;

final class MtrHopFactory
{
    public static function fromRawHop(array $rawHop): MtrHopDto
    {
        return new MtrHopDto(
            $rawHop['count'],
            $rawHop['host'],
            $rawHop['ASN'] === 'AS???' ? null : $rawHop['ASN'],
            $rawHop['Loss%'] ?? null,
            $rawHop['Drop'] ?? null,
            $rawHop['Rcv'] ?? null,
            $rawHop['Snt'] ?? null,
            $rawHop['Last'] ?? null,
            $rawHop['Best'] ?? null,
            $rawHop['Avg'] ?? null,
            $rawHop['Wrst'] ?? null,
            $rawHop['StDev'] ?? null,
            $rawHop['Gmean'] ?? null,
            $rawHop['Jttr'] ?? null,
            $rawHop['Javg'] ?? null,
            $rawHop['Jmax'] ?? null,
            $rawHop['Jint'] ?? null,
        );
    }
}