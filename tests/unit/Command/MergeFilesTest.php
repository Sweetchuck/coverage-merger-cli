<?php

declare(strict_types = 1);

namespace Sweetchuck\CoverageMergerCli\Tests\Unit\Command;

use Codeception\Test\Unit;
use org\bovigo\vfs\vfsStream;
use Sweetchuck\CoverageMergerCli\Application;
use Sweetchuck\CoverageMergerCli\Tests\UnitTester;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \Sweetchuck\CoverageMergerCli\Command\MergeFiles
 */
class MergeFilesTest extends Unit
{
    protected UnitTester $tester;

    public function testExecute()
    {
        $vfs = vfsStream::setup(
            'root',
            0777,
            [
                __FUNCTION__ => [],
            ],
        );
        $mergerFixturesDir = codecept_data_dir('fixtures/case-01');

        $this->tester->runPhpunit($mergerFixturesDir, 'ATest.php');
        $this->tester->runPhpunit($mergerFixturesDir, 'BTest.php');
        $this->tester->runPhpunit($mergerFixturesDir, 'CTest.php');

        $actualFile = $vfs->url() . '/' . __FUNCTION__ . '/merged.php';

        $application = new Application();
        $application->initialize();

        /** @var \Sweetchuck\CoverageMergerCli\Command\MergeFiles $command */
        $command = $application->find('merge:files');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'input-files' => [
                    "$mergerFixturesDir/reports/A.php",
                    "$mergerFixturesDir/reports/B.php",
                    "$mergerFixturesDir/reports/C.php",
                ],
                '--output-file' => $actualFile,
            ],
            [
                'capture_stderr_separately' => true,
            ],
        );

        $this->tester->assertSame(0, $commandTester->getStatusCode(), 'exitCode');
        $this->tester->assertSame('', $commandTester->getDisplay(), 'stdOutput');
        $this->tester->assertSame('', $commandTester->getErrorOutput(), 'stdError');

        $expectedFileName = 'merged-' . $this->tester->grabPhpVersionMajorMinor() . '-unit.php';

        $this->tester->assertSame(
            strtr(
                file_get_contents("$mergerFixturesDir/expected/$expectedFileName"),
                [
                    '{{ baseDirStr }}' => getcwd(),
                    '{{ baseDirLength }}' => (string) (strlen(getcwd()) + 39),
                ],
            ),
            strtr(
                file_get_contents($actualFile),
                [
                    ' ' . \PHP_EOL => \PHP_EOL,
                ],
            ),
        );
    }
}
