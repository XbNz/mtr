<?php

declare(strict_types=1);

namespace Xbnz\Mtr;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use IPTools\IP;
use IPTools\Network;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Spatie\Fork\Fork;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;
use Xbnz\Mtr\Actions\CreateParamsArrayFromDtoAction;
use Xbnz\Mtr\Actions\CreateParamStringFromDtoAction;
use Xbnz\Mtr\Factories\ForkSerializableProcessDtoFactory;

final class MTR
{
    private readonly array $parameterArray;
    private array $hosts = [];

    public function __construct(
        MtrOptionsConfigDto $configDto = new MtrOptionsConfigDto(),
        private LoggerInterface $logger = new NullLogger(),
    ) {
        $this->parameterArray = (new CreateParamsArrayFromDtoAction())($configDto);
    }

    public static function build(
        MtrOptionsConfigDto $configDto = new MtrOptionsConfigDto(),
        LoggerInterface $logger = new NullLogger(),
    ): self {
        return new self($configDto, $logger);
    }

    public function withIp(string|int ...$hosts): self
    {
        Collection::make($hosts)
            ->filter(fn($host) => $this->validIpNetwork($host))
            ->map(
                fn($parsableNetwork) => Collection::make(
                    Network::parse($parsableNetwork)
                )->map(fn($oneIp) => (string) $oneIp)
            )
            ->flatten()
            ->tap(fn(Collection $finishedIpList) => $this->hosts[] = $finishedIpList->toArray());

        Collection::make($hosts)
            ->reject(fn($host) => $this->validIpNetwork($host))
            ->filter(fn($host) => $this->validHostname($host))
            ->tap(fn(Collection $finishedHostnameList) => $this->hosts[] = $finishedHostnameList->toArray());

        $this->hosts = Collection::make($this->hosts)
            ->flatten()
            ->map(fn($host) => trim($host))
            ->unique()
            ->toArray();

        return $this;
    }

    public function raw(
        int $consoleTimeout = 3600,
        int $concurrentProcesses = 30,
        ?callable $callback = null,
    ): Collection {
        Assert::true(count($this->hosts) > 0);
        Assert::positiveInteger($consoleTimeout);
        Assert::positiveInteger($concurrentProcesses);

        $forks = Collection::make($this->hosts)
            ->map(function (string $host) use ($consoleTimeout, $callback) {
                return function () use ($host, $consoleTimeout, $callback): ForkSerializableProcessDto {
                    $process = new Process(
                        ['sudo', 'mtr', $host, ...$this->parameterArray], timeout: $consoleTimeout
                    );

                    $process->run();
                    $dto = ForkSerializableProcessDtoFactory::fromMtrForkCallable($process, $host);

                    if (is_callable($callback)) {
                        $callback($dto);
                    }

                    return $dto;
                };
            })->toArray();

        $forkResults = Fork::new()
            ->concurrent($concurrentProcesses)
            ->run(... $forks);


        foreach ($forkResults as $result) {
            if (! $result instanceof ForkSerializableProcessDto){
                dump($result);
            }
        }

        Assert::allIsInstanceOf($forkResults, ForkSerializableProcessDto::class);

        [$successful, $failed] = Collection::make($forkResults)
            ->partition(fn(ForkSerializableProcessDto $result) => $result->errorOutput === null);

        $failed->whenNotEmpty(fn(Collection $failedBatch) => $this->logErrors($failedBatch));

        return $successful
            ->keyBy(fn(ForkSerializableProcessDto $dto) => $dto->host)
            ->map(fn(ForkSerializableProcessDto $dto) => $dto->output['report']);
    }


    /**
     * @return Collection<MtrResult>
     */
    public function wrap(
        int $consoleTimeout = 3600,
        int $concurrentProcesses = 30
    ): Collection {
        $results = $this->raw($consoleTimeout, $concurrentProcesses);

        return Collection::make($results)
            ->map(fn($result) => new MtrResult($result));
    }


    private function validHostname(string $host): bool
    {
        return filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
    }

    private function validIpNetwork(string|int $host): bool
    {
        try {
            Network::parse($host);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param Collection<ForkSerializableProcessDto> $failedProcesses
     * @return void
     */
    private function logErrors(Collection $failedProcesses): void
    {
        Assert::allIsInstanceOf($failedProcesses, ForkSerializableProcessDto::class);

        foreach ($failedProcesses as $dto) {
            $logOutput = "MTR command failed:" . PHP_EOL;
            $logOutput .= "Command: {$dto->commandLine}" . PHP_EOL;
            $logOutput .= "Error: {$dto->errorOutput}" . PHP_EOL;
            $logOutput .= "Exit code: {$dto->exitCode}" . PHP_EOL;

            $this->logger->error($logOutput);
        }
    }
}


