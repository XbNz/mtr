<?php

namespace Xbnz\Mtr\Tests\Unit;

use Illuminate\Support\Collection;
use Xbnz\Mtr\Actions\CreateParamsArrayFromDtoAction;
use Xbnz\Mtr\MtrOptionsConfigDto;

class CreateParamsArrayFromDtoActionTest extends \PHPUnit\Framework\TestCase
{
    /** @test **/
    public function it_takes_a_parameters_dto_and_returns_cli_readable_parameters_array(): void
    {
        // Arrange
        $action = new CreateParamsArrayFromDtoAction;
        $dto = new MtrOptionsConfigDto(interval: 100, tos: 50);

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