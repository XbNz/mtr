<?php

namespace Xbnz\Mtr\Actions;

use Illuminate\Support\Collection;
use Webmozart\Assert\Assert;
use Xbnz\Mtr\MtrOptions;
use Xbnz\Mtr\MtrOptionsConfigDto;

class CreateParamsArrayFromDtoAction
{
    public function __invoke(MtrOptionsConfigDto $configDto): array
    {
        $valueAssignableParameters = Collection::make($configDto)
            ->reject(fn($property) => is_null($property))
            ->filter(fn($property) => is_string($property) || is_int($property))
            ->map(function ($mtrParamValue, $mtrParamName) {
                $uppercaseName = strtoupper($mtrParamName);
                $paramString = (new \ReflectionClassConstant(MtrOptions::class, $uppercaseName))->getValue();
                Assert::startsWith($paramString, '--');
                Assert::true(ctype_alpha(mb_substr($paramString, -1)));
                return "{$paramString}=$mtrParamValue";
            });


        $boolParametersMustNotHaveValueAssigned = Collection::make($configDto)
            ->reject(fn($property) => is_null($property))
            ->filter(fn($property) => is_bool($property))
            ->reject(fn($property) => $property === false)
            ->map(function ($mtrParamValue, $mtrParamName) {
                $uppercaseName = strtoupper($mtrParamName);
                $paramString = (new \ReflectionClassConstant(MtrOptions::class, $uppercaseName))->getValue();
                Assert::startsWith($paramString, '--');
                Assert::true(ctype_alpha(mb_substr($paramString, -1)));
                return $paramString;
            });

        return $valueAssignableParameters->merge($boolParametersMustNotHaveValueAssigned)->toArray();
    }
}