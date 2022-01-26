<?php

declare(strict_types=1);

namespace Xbnz\Mtr;

use Webmozart\Assert\Assert;

final class MtrOptions
{
    public const INTERVAL = '--interval';
    public const COUNT = '--report-cycles';
    public const NODNS = '--no-dns';
    public const SHOWIPS = '--show-ips';
    public const ASLOOKUP = '--as-lookup';
    public const PACKETSIZE = '--psize';
    public const BITPATTERN = '--bitpattern';
    public const TOS = '--tos';
    public const FIRSTTTL = '--first-ttl';
    public const MAXTTL = '--max-ttl';
    public const UDP = '--udp';
    public const TCP = '--tcp';
    public const PORT = '--port';
    public const TIMEOUT = '--timeout';
    public const REPORTWIDE = '--report-wide';
    public const JSON = '--json';
    public const ORDER = '--order';

}
