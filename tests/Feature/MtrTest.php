<?php

namespace Xbnz\Mtr\Tests\Feature;

use IPTools\IP;
use IPTools\Network;
use Xbnz\Mtr\MTR;
use Xbnz\Mtr\MtrOptions;
use Xbnz\Mtr\MtrOptionsConfigDto;

class MtrTest extends \PHPUnit\Framework\TestCase
{
    /** @test **/
    public function messing_around(): void
    {
        MTR::build()->withIp(
            'fuckyoulmao',
            'google.com',
            'google.com',
            'bing.com',
            '1.1.1.1',
            '8.8.8.8/32',
            '3289749832',
            'http://google.com',
            'i have no idea what im doing',
        );
    }
}