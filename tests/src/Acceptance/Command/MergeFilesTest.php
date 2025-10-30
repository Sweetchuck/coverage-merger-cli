<?php

declare(strict_types = 1);

namespace Sweetchuck\CoverageMergerCli\Tests\Acceptance\Command;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class MergeFilesTest extends TestCase
{

    protected function getFixturesDir(): string
    {
        return dirname(__DIR__, 3) . '/fixtures';
    }

    public function grabPhpVersionMajorMinor(): string
    {
        $full = str_pad((string) \PHP_VERSION_ID, 6, '0', \STR_PAD_LEFT);

        return substr($full, 0, 4);
    }

    protected function getPharPath(): string
    {
        return './artifacts/coverage-merger.phar';
    }

    protected function runPhpunit(string $cwd, string $testFile): Process
    {
        $phpunitExecutable = realpath('vendor/bin/phpunit');
        $command = [
            $phpunitExecutable,
            '--do-not-cache-result',
            "--coverage-php=reports/" . preg_replace('@Test\.php$@', '.php', $testFile),
            "tests/$testFile",
        ];

        $process = new Process($command, $cwd);
        $process->run();

        return $process;
    }

    #[Test]
    public function testMergeFilesInputFileNamesAsArgs(): void
    {
        $fixturesDir = $this->getFixturesDir();
        $case01Dir = "$fixturesDir/case-01";

        $this->runPhpunit($case01Dir, 'ATest.php');
        $this->runPhpunit($case01Dir, 'BTest.php');
        $this->runPhpunit($case01Dir, 'CTest.php');

        $pharPath = $this->getPharPath();
        static::assertNotEmpty($pharPath);

        $process = new Process(
            [
                $pharPath,
                'merge:files',
                "$case01Dir/reports/A.php",
                "$case01Dir/reports/B.php",
                "$case01Dir/reports/C.php",
            ],
        );
        $process->run();

        $expectedFileName = 'merged-' . $this->grabPhpVersionMajorMinor() . '-acceptance.php';
        if (!file_exists("$case01Dir/expected/$expectedFileName")) {
            $expectedFileName = 'merged-' . $this->grabPhpVersionMajorMinor() . '-unit.php';
        }

        $expectedFilePath = "$case01Dir/expected/$expectedFileName";
        $expectedContent = file_get_contents($expectedFilePath) ?: '';

        $actualContent = strtr(
            $process->getOutput() . "\n",
            [
                ' ' . \PHP_EOL => \PHP_EOL,
            ],
        );

        static::assertSame(
            substr($expectedContent, 0, 70),
            substr($actualContent, 0, 70),
            'stdOutput',
        );
    }

    #[Test]
    public function testMergeFilesInputFileNamesFromStdInput(): void
    {
        $fixturesDir = $this->getFixturesDir();
        $case01Dir = "$fixturesDir/case-01";
        $this->runPhpunit($case01Dir, 'ATest.php');
        $this->runPhpunit($case01Dir, 'BTest.php');
        $this->runPhpunit($case01Dir, 'CTest.php');

        $pharPath = $this->getPharPath();
        static::assertNotEmpty($pharPath);

        $process = new Process(
            [
                'bash',
                '-c',
                sprintf(
                    "find %s -type f -name '*.php' | %s %s merge:files",
                    escapeshellarg("$case01Dir/reports"),
                    escapeshellarg(\PHP_BINARY),
                    escapeshellcmd($pharPath),
                ),
            ],
        );
        $process->run();

        $expectedFileName = 'merged-' . $this->grabPhpVersionMajorMinor() . '-acceptance.php';
        if (!file_exists("$case01Dir/expected/$expectedFileName")) {
            $expectedFileName = 'merged-' . $this->grabPhpVersionMajorMinor() . '-unit.php';
        }

        $expectedFilePath = "$case01Dir/expected/$expectedFileName";
        $expectedContent = file_get_contents($expectedFilePath) ?: '';
        $actualContent = $process->getOutput();
        static::assertSame(
            substr($expectedContent, 0, 556),
            substr($actualContent, 0, 556),
            'stdOutput prefix',
        );
        static::assertSame(
            substr($expectedContent, -1260),
            substr($actualContent, -1260),
            'stdOutput suffix',
        );
    }
}
