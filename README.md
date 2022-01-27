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
        $this
            ->app
            ->when(MTR::class)
            ->needs('$mtrOptions')
            ->giveTagged('services.mtr_options');
            
        $this->app->simgleton(MTR::class, fn() => new MTR);
    }
}
```

## MTR options

```php
// services.php
return [
    // ...
    'mtr_options' => new \Xbnz\Mtr\MtrOptionsConfigDto(
        
    )
]

```

## Bulk async MTR
```php
use Xbnz\Mtr;

public function __construct(private MTR $mtr)
{}

public function example()
{
    $results = $this->mtr->withIp('1.1.1.1', '8.8.8.8');
    // OR
    $results = $this->mtr->withIp(...['1.1.1.1', '8.8.8.8']);
    
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
        ->each(fn($targetWithHops) => Assert::containsOnlyInstancesOf(MtrHop::class, $targetWithHops));
    
    $aliveTargetHopPairs
        ->each(function ($hops, $ip) {
            if ($hops->last()->packetLoss > 5) {
                $this->logger->log("Target {$ip}, hop {$hops->last()->hopNumber} has {$hops->last()->packetLoss}% loss")
            }
        });
}
```

## Or just one IP

```php
use Xbnz\Mtr;

public function __construct(private MTR $mtr)
{}

public function example()
{
    $result = $this->mtr->withIp('1.1.1.1');
    
    Assert::instancesOf(
        MtrResult::class,
        $result
    );
 
    if ($result->targetUp() && $result->hops->last()->packetLoss > 5) {
        $logMsg = "Target {$result->taegetHost}";
        $logMsg .= " hop {$hops->last()->hopNumber}";
        $logMsg .= " has {$hops->last()->packetLoss}% loss";
        $this->logger->log($logMsg);
    }
}
```