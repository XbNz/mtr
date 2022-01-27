<?php

declare(strict_types=1);

namespace Xbnz\Mtr;

use phpDocumentor\Reflection\PseudoTypes\PositiveInteger;
use PHPUnit\TextUI\XmlConfiguration\Php;
use Spatie\DataTransferObject\DataTransferObject;
use Webmozart\Assert\Assert;
use Xbnz\Mtr\Exceptions\MtrConfigurationException;

final class MtrOptionsConfigDto
{
    /**
     * @throws MtrConfigurationException
     */
    public function __construct(
        public readonly ?string $interval = null,
        public readonly ?bool $noDns = null,
        public readonly ?bool $showIps = null,
        public readonly ?int $packetSize = null,
        public readonly ?int $bitPattern = null,
        public readonly ?int $tos = null,
        public readonly ?int $firstTtl = null,
        public readonly ?int $maxTtl = null,
        public readonly ?bool $udp = null,
        public readonly ?bool $tcp = null,
        public readonly ?int $port = null,
        public readonly ?int $timeout = null,
        public readonly ?int $count = 10,
        public readonly string $order = 'LDRSNBAWVGJMXI',
        public readonly ?bool $asLookup = true,
        public readonly ?bool $reportWide = true,
        public readonly ?bool $json = true,
    ) {
        Assert::nullOrString($interval);
        if ($interval !== null) {
            Assert::numeric($interval);
            Assert::greaterThan($interval, 0);
        }
        Assert::nullOrPositiveInteger($count);
        Assert::nullOrPositiveInteger($bitPattern);
        Assert::nullOrPositiveInteger($tos);
        Assert::lessThan($tos, 255);
        Assert::nullOrPositiveInteger($firstTtl);
        Assert::nullOrPositiveInteger($maxTtl);
        Assert::nullOrPositiveInteger($port);
        Assert::lessThan($port, 65535);
        Assert::nullOrPositiveInteger($timeout);
        if ($udp === true && $tcp === true) {
            throw new MtrConfigurationException('UDP & TCP are mutually exclusive!');
        }
    }
}