<?php

declare(strict_types=1);

namespace Xbnz\Mtr;

use Webmozart\Assert\Assert;

final class MtrHopDto
{
    public function __construct(
        public readonly int $hopPositionCount,
        public readonly string $hopHost,
        public readonly ?string $asNumber,
        public readonly ?float $packetLoss,
        public readonly ?int $droppedPackets,
        public readonly ?int $receivedPackets,
        public readonly ?int $sentPackets,
        public readonly ?float $lastRttValue,
        public readonly ?float $lowestRttValue,
        public readonly ?float $averageRttValue,
        public readonly ?float $highestRttValue,
        public readonly ?float $standardDeviation,
        public readonly ?float $geometricMean,
        public readonly ?float $jitter,
        public readonly ?float $averageJitter,
        public readonly ?float $maximumJitter,
        public readonly ?float $interarrivalJitter,
    ) {
        Assert::nullOrNotContains($asNumber, '?');
    }
}