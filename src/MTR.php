<?php

declare(strict_types=1);

namespace Xbnz\Mtr;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use IPTools\IP;
use IPTools\Network;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;
use Xbnz\Mtr\Actions\CreateParamsArrayFromDtoAction;
use Xbnz\Mtr\Actions\CreateParamStringFromDtoAction;

final class MTR
{
    private readonly array $parameterArray;
    private array $hosts;

    public function __construct(MtrOptionsConfigDto $configDto, private LoggerInterface $logger = new NullLogger())
    {
        $this->parameterArray = (new CreateParamsArrayFromDtoAction())($configDto);
    }

    public static function build(MtrOptionsConfigDto $configDto = new MtrOptionsConfigDto()): self
    {
        return new self($configDto);
    }

    public function withIp(string ...$hosts): self
    {
        Collection::make($hosts)
            ->filter(function ($host) {
                if ($this->validIpNetwork($host)) {
                    return true;
                }

                if ($this->validHostname($host)) {
                    $this->hosts[] = $host;
                }

                return false;
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
            ->map(fn($host) => trim($host))
            ->unique()
            ->toArray();

        return $this;
    }

    public function execute()
    {
        Assert::notNull($this->hosts);

        /**
         * TODO:
         * Whole bunch of things :(
         * Make wrappers around the final collection returned on successful responses
         * Test this method. Of note: make sure the logger works.
         * Think about a nicer way of dealing with param types. Perhaps all string?
         * Too tired to write this I'm out.
         */

        [$successful, $failed] = Collection::make($this->hosts)
            ->map(fn (string $host) => new Process(['sudo', 'mtr', $host, ...$this->parameterArray]))
            ->chunk(200)
            ->each($this->processChunk(...))
            ->flatten()
            ->partition(fn(Process $process) => $process->getExitCode() === 0);

        $failed->whenNotEmpty(fn(Collection $failedBatch) => $this->logErrors($failedBatch));

        $successful
            ->map(fn(Process $process) => json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR));
    }

    private function validHostname(string $host): bool
    {
        return filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
    }

    private function validIpNetwork(string $host): bool
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
        }
    }
}


