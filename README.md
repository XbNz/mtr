![GitHub Workflow Status](https://img.shields.io/github/workflow/status/xbnz/laravel-multi-ip/Run%20tests?label=Tests&style=for-the-badge&logo=appveyor)

# PHP MTR
### Framework-agnostic asynchronous MTR library


| :warning:  The MTR command requires root privileges for low interval, edit your /etc/sudoers file. |
|----------------------------------------------------------------------------------------------------|


# Get up and running
## Require the package
`composer require xbnz/mtr`
## Using MTR in your project (Laravel)
```php
// Optionally configure DI (Laravel example)

class AppServiceProvider extends Provider
{
    public function register()
    {
        $this->app->bind(MTR::class, function (Application $app) {
            return MTR::build(Config::get('services.mtr_options'), $app->make(LoggerInterface::class));
        });
    }
}
```

## MTR options

```php
// services.php
return [
    // ...
    'mtr_options' => new \Xbnz\Mtr\MtrOptionsConfigDto(...)
]

```


| :warning: Important note on configuration options. |
|----------------------------------------------------|

You should not disable the 'report wide' or 'json' options. The package will cease to work.

## Bulk or single IP with async execution
```php
use Xbnz\Mtr;

public function __construct(private MTR $mtr)
{}

// Or without DI...

public function __construct()
{
    $this->mtr = Mtr::build(new MtrOptionsConfigDto(...), new Logger);
}


public function example()
{

    // Consider a high timeout value for large scans. 
    // Async threads above 50 might cause inaccuracies in RTT statistics.

    $results = $this->mtr->withIp('1.1.1.1', '8.8.8.8')->wrap($consoleTimeout, $simultaneousAsync);
    // OR
    $results = $this->mtr->withIp(...['1.1.1.1', '8.8.8.8'])->wrap($consoleTimeout, $simultaneousAsync);
    // OR
    $results = $this->mtr->withIp('1.1.1.1')->wrap($consoleTimeout, $simultaneousAsync);
    // OR
    $results = $this->mtr->withIp(995738574453)->wrap($consoleTimeout, $simultaneousAsync);
    // OR
    $results = $this->mtr->withIp('google.com')->wrap($consoleTimeout, $simultaneousAsync);
    // OR
    $results = $this->mtr->withIp(0x1294812)->wrap($consoleTimeout, $simultaneousAsync);
    
    
    Assert::containsOnlyInstancesOf(
        MtrResult::class,
        $results
    );
  
    /**
    * If last hop of MTR !== supplied IP, the target is dead. 
    * In most circumstances, you'd want to reject dead targets. 
    * You might still want to see the hops for a dead target, so the default policy is not to reject.
    */
    $aliveTargetHopPairs = $results
        ->reject(fn($result) => $result->targetDown()) 
        ->map(fn($result) => [$result->targetHost => $result->hops]);
        ->each(fn($targetWithHops) => Assert::containsOnlyInstancesOf(MtrHopDto::class, $targetWithHops));
    
    $aliveTargetHopPairs
        ->each(function ($hops, $ip) {
            if ($hops->last()->packetLoss > 5) {
                $this->logger->log("Target {$ip}, hop {$hops->last()->hopPositionCount} has {$hops->last()->packetLoss}% loss")
            }
        });
}
```

