<?php

declare(strict_types = 1);

namespace Sweetchuck\CoverageMergerCli\Tests\Helper;

use Codeception\Module;

class Acceptance extends Module
{
    protected $requiredFields = [];

    protected $config = [
        'pharPath' => './artifacts/coverage-merger.phar',
    ];

    public function grabPharPath(): string
    {
        return $this->config['pharPath'];
    }
}
