<?php

declare(strict_types=1);

namespace Xbnz\Mtr;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use IPTools\IP;
use IPTools\Network;
use Webmozart\Assert\Assert;
use Xbnz\Mtr\Actions\CreateParamsArrayFromDtoAction;
use Xbnz\Mtr\Actions\CreateParamStringFromDtoAction;

final class MTR
{
    private readonly array $parameterArray;
    private array $hosts;

    public function __construct(MtrOptionsConfigDto $configDto)
    {
        $this->parameterArray = (new CreateParamsArrayFromDtoAction())($configDto);
    }

    public static function build(MtrOptionsConfigDto $configDto = new MtrOptionsConfigDto()): self
    {
        return new self($configDto);
    }

    public function withIp(string ...$hosts): void
    {
        Collection::make($hosts)
            ->filter(function ($host) {
                try {
                    Network::parse($host);
                    return true;
                } catch (\Exception $e) {
                    if ($this->validHostname($host)) {
                        $this->hosts[] = $host;
                    }
                    return false;
                }
            })
            ->map(
                fn($parsableNetwork) => Collection::make(
                    Network::parse($parsableNetwork)
                )->map(fn($oneIp) => (string) $oneIp)
            )
            ->flatten()
            ->tap(fn(Collection $finishedIpList) => $this->hosts[] = $finishedIpList->toArray());

        $this->hosts = Collection::make($this->hosts)
            ->flatten()
            ->unique()
            ->toArray();
    }

    public function execute()
    {
        dd($this->parameterArray);
    }

    private function validHostname(string $host): bool
    {
        return filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
    }

}


