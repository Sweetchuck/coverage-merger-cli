<?php

declare(strict_types = 1);

namespace Sweetchuck\CoverageMergerCli\Tests\Fixtures\Case01\Tests;

use PHPUnit\Framework\TestCase;
use Sweetchuck\CoverageMergerCli\Tests\Fixtures\Case01\A;

/**
 * @covers \Sweetchuck\CoverageMergerCli\Tests\Fixtures\Case01\A
 */
class ATest extends TestCase
{
    public function testCreate()
    {
        $a = new A();
        $this->assertSame('pong', $a->ping());
    }
}
