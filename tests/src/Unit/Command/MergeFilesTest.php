<?php

declare(strict_types = 1);

namespace Sweetchuck\CoverageMergerCli\Tests\Unit\Command;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sweetchuck\CoverageMergerCli\Application;
use Sweetchuck\CoverageMergerCli\Command\MergeFiles;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Process\Process;

#[CoversClass(MergeFiles::class)]
class MergeFilesTest extends TestCase
{

    protected function getFixturesDir(): string
    {
        return dirname(__DIR__, 3) . '/fixtures';
    }

    protected function grabPhpVersionMajorMinor(): string
    {
        $full = str_pad((string) \PHP_VERSION_ID, 6, '0', \STR_PAD_LEFT);

        return substr($full, 0, 4);
    }

    public function runPhpunit(string $cwd, string $testFile): void
    {
        $phpunitExecutable = realpath('vendor/bin/phpunit');
        $command = [
            \PHP_BINARY,
            $phpunitExecutable,
            "--coverage-php=reports/" . preg_replace('@Test\.php$@', '.php', $testFile),
            "tests/$testFile",
        ];

        $process = new Process($command, $cwd);
        $process->run();
        if ($process->getExitCode() !== 0) {
            throw new \Exception($process->getOutput() . "\n\n" . $process->getErrorOutput());
        }
    }

    #[Test]
    public function testExecute(): void
    {
        $fixturesDir = $this->getFixturesDir();
        $case01Dir = "$fixturesDir/case-01";
        $vfs = vfsStream::setup(
            'root',
            0777,
            [
                __FUNCTION__ => [],
            ],
        );

        $this->runPhpunit($case01Dir, 'ATest.php');
        $this->runPhpunit($case01Dir, 'BTest.php');
        $this->runPhpunit($case01Dir, 'CTest.php');

        $actualFile = $vfs->url() . '/' . __FUNCTION__ . '/merged.php';

        $application = new Application();
        $application->initialize();

        /** @var \Sweetchuck\CoverageMergerCli\Command\MergeFiles $command */
        $command = $application->find('merge:files');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'input-files' => [
                    "$case01Dir/reports/A.php",
                    "$case01Dir/reports/B.php",
                    "$case01Dir/reports/C.php",
                ],
                '--output-file' => $actualFile,
            ],
            [
                'capture_stderr_separately' => true,
            ],
        );

        static::assertSame(0, $commandTester->getStatusCode(), 'exitCode');
        static::assertSame('', $commandTester->getDisplay(), 'stdOutput');
        static::assertSame('', $commandTester->getErrorOutput(), 'stdError');

        $cwd = getcwd() ?: '.';
        $expectedFileName = 'merged-' . $this->grabPhpVersionMajorMinor() . '-unit.php';
        $expectedFilePath = "$case01Dir/expected/$expectedFileName";
        $expectedContent = strtr(
            file_get_contents($expectedFilePath) ?: '',
            [
                '{{ baseDirStr }}' => $cwd,
                '{{ baseDirLength }}' => (string) (strlen($cwd) + 39),
            ],
        );

        $actualContent = strtr(
            file_get_contents($actualFile) ?: '',
            [
                ' ' . \PHP_EOL => \PHP_EOL,
            ],
        );
        static::assertSame(
            substr($expectedContent, 0, 556),
            substr($actualContent, 0, 556),
            'stdOutput prefix',
        );
        static::assertSame(
            substr($expectedContent, -1264),
            substr($actualContent, -1264),
            'stdOutput suffix',
        );
    }
}
