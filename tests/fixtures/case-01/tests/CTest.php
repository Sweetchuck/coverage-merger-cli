<?php

declare(strict_types = 1);

namespace Sweetchuck\CoverageMergerCli\Tests\Fixtures\Case01\Tests;

use PHPUnit\Framework\TestCase;
use Sweetchuck\CoverageMergerCli\Tests\Fixtures\Case01\C;

/**
 * @covers \Sweetchuck\CoverageMergerCli\Tests\Fixtures\Case01\C
 */
class CTest extends TestCase
{
    public function testCreate()
    {
        $c = new c();
        $this->assertSame('pong', $c->ping());
    }
}
