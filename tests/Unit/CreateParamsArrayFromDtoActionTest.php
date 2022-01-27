<?php

declare(strict_types=1);

namespace Xbnz\Mtr\Tests\Unit;

use Illuminate\Support\Collection;
use Webmozart\Assert\InvalidArgumentException;
use Xbnz\Mtr\Actions\CreateParamsArrayFromDtoAction;
use Xbnz\Mtr\Exceptions\MtrConfigurationException;
use Xbnz\Mtr\MtrOptions;
use Xbnz\Mtr\MtrOptionsConfigDto;

final class CreateParamsArrayFromDtoActionTest extends \PHPUnit\Framework\TestCase
{
    /** @test **/
    public function it_takes_a_parameters_dto_and_returns_cli_readable_parameters_array(): void
    {
        // Arrange
        $action = new CreateParamsArrayFromDtoAction;
        $dto = new MtrOptionsConfigDto(
            '100',
            true,
            true,
            50,
            50,
            50,
            4,
            7,
            true,
            false,
            5445,
            10,
            10,
            'LJGV',
            true,
            true,
            true,
        );

        // Act
        $paramsArray = $action($dto);

        // Assert
        Collection::make($dto)
            ->whereNotNull()
            ->reject(fn($property) => $property === false)
            ->each(function ($propertyValue, $propertyName) use ($paramsArray) {
                $this->assertArrayHasKey($propertyName, $paramsArray);
            });
    }
}