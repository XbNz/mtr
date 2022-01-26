<?php

declare(strict_types=1);

namespace Xbnz\Mtr\Actions;

use Illuminate\Support\Collection;
use Webmozart\Assert\Assert;
use Xbnz\Mtr\MtrOptions;
use Xbnz\Mtr\MtrOptionsConfigDto;

final class CreateParamStringFromDtoAction
{
    public function __invoke(MtrOptionsConfigDto $configDto): string
    {
        $stringsCollection = Collection::make($configDto)
            ->reject(fn($property) => is_null($property) || $property === false)
            ->map(function ($mtrParamValue, $mtrParamName) {
                $uppercaseName = strtoupper($mtrParamName);
                $paramString = (new \ReflectionClassConstant(MtrOptions::class, $uppercaseName))->getValue();
                Assert::startsWith($paramString, ' ');
                return "{$paramString} {$mtrParamValue}";
            });

        return implode(' ', $stringsCollection->toArray());
    }
}