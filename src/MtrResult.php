<?php

declare(strict_types=1);

namespace Xbnz\Mtr;

use Illuminate\Support\Collection;
use Webmozart\Assert\Assert;
use Xbnz\Mtr\Factories\MtrHopFactory;

final class MtrResult extends Collection
{
    public readonly string $targetHost;
    public readonly int $hopCount;

    public function __construct($items = [])
    {
        parent::__construct($items);

        $this->mtrResultIntegrityCheck($items);

        $this->targetHost = $items['mtr']['dst'];
        $this->hopCount = count($items['hubs']);
    }

    public function targetDown(): bool
    {
        $lastHop = Collection::make(
            $this->get('hubs')
        )->last();

        return $lastHop['host'] === '???';
    }

    public function targetUp(): bool
    {
        $lastHop = Collection::make(
            $this->get('hubs')
        )->last();

        return $lastHop['host'] !== '???';
    }

    /**
     * @return Collection<MtrHopDto>
     */
    public function hops(): Collection
    {
        return Collection::make(
            $this->get('hubs')
        )->map(
            fn($rawHop) => MtrHopFactory::fromRawHop($rawHop)
        );
    }

    private function mtrResultIntegrityCheck(array $items): void
    {
        Assert::keyExists($this->items, 'hubs');
        Assert::keyExists($this->items, 'mtr');

        Assert::notEmpty($this->items['hubs'], 'Did not receive hop statistics. Try increasing count/interval');

        foreach ($items['hubs'] as $hub) {
            Assert::notEmpty($hub['count'], 'Did not receive hop statistics. Try increasing count/interval');
        }
    }
}