<?php

declare(strict_types = 1);

namespace Sweetchuck\CoverageMergerCli\Test\Fixtures\Case01\Tests;

use PHPUnit\Framework\TestCase;
use Sweetchuck\CoverageMergerCli\Test\Fixtures\Case01\C;

/**
 * @covers \Sweetchuck\CoverageMergerCli\Test\Fixtures\Case01\C
 */
class CTest extends TestCase
{
    public function testCreate()
    {
        $c = new c();
        $this->assertSame('pong', $c->ping());
    }
}
