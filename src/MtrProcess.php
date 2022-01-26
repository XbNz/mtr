<?php

declare(strict_types=1);

namespace Xbnz\Mtr;

use Symfony\Component\Process\Process;

final class MtrProcess extends Process
{

    public function __construct()
    {
        //TODO: Timeout is limited to 60 secs. Increase.
        parent::__construct(
            [''],
            timeout: 3600
        );
    }

}