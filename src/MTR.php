<?php

declare(strict_types=1);

namespace Xbnz\Mtr;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use IPTools\IP;
use IPTools\Network;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;
use Xbnz\Mtr\Actions\CreateParamsArrayFromDtoAction;
use Xbnz\Mtr\Actions\CreateParamStringFromDtoAction;

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
        int $simultaneousAsync = 30
    ): Collection {
        Assert::true(count($this->hosts) > 0);
        Assert::positiveInteger($consoleTimeout);
        Assert::positiveInteger($simultaneousAsync);

        [$successful, $failed] = Collection::make($this->hosts)
            ->map(fn (string $host) => new Process(['sudo', 'mtr', $host, ...$this->parameterArray], timeout: $consoleTimeout))
            ->chunk($simultaneousAsync)
            ->each($this->processChunk(...))
            ->flatten()
            ->partition(fn(Process $process) => empty($process->getErrorOutput()));

        $failed->whenNotEmpty(fn(Collection $failedBatch) => $this->logErrors($failedBatch));

        return $successful
            ->map(fn(Process $process) => json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR))
            ->flatten(1);
    }


    /**
     * @return Collection<MtrResult>
     */
    public function wrap(
        int $consoleTimeout = 3600,
        int $simultaneousAsync = 30
    ): Collection {
        $results = $this->raw($consoleTimeout, $simultaneousAsync);

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

    private function processChunk(Collection $processes): Collection
    {
        return $processes
            ->each(fn(Process $process) => $process->start())
            ->each(fn(Process $process) => $process->wait());
    }

    /**
     * @param Collection<Process> $failedProcesses
     * @return void
     */
    private function logErrors(Collection $failedProcesses): void
    {
        Assert::allIsInstanceOf($failedProcesses, Process::class);

        foreach ($failedProcesses as $mtrProcess) {
            $logOutput = "MTR command failed:";
            $logOutput .= "Command: {$mtrProcess->getCommandLine()}";
            $logOutput .= "Error: {$mtrProcess->getErrorOutput()}";
            $logOutput .= "Exit code: {$mtrProcess->getExitCode()}";

            $this->logger->error($logOutput);

            echo "A process failed with exit code {$mtrProcess->getExitCode()}. Find more detail in logs." . PHP_EOL;
        }
    }
}


