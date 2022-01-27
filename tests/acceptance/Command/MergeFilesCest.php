<?php

declare(strict_types = 1);

namespace Sweetchuck\CoverageMergerCli\Tests\Acceptance\Command;

use Sweetchuck\CoverageMergerCli\Tests\AcceptanceTester;

class MergeFilesCest
{
    public function mergeFilesInputFileNamesAsArgs(AcceptanceTester $I)
    {
        $fixturesDir = codecept_data_dir('fixtures/case-01');
        $I->runPhpunit($fixturesDir, 'ATest.php');
        $I->runPhpunit($fixturesDir, 'BTest.php');
        $I->runPhpunit($fixturesDir, 'CTest.php');

        $pharPath = $I->grabPharPath();
        $I->assertNotEmpty($pharPath);
        $I->runShellCommand(
            sprintf(
                '%s merge:files %s %s %s',
                escapeshellcmd($pharPath),
                escapeshellarg("$fixturesDir/reports/A.php"),
                escapeshellarg("$fixturesDir/reports/B.php"),
                escapeshellarg("$fixturesDir/reports/C.php"),
            ),
        );

        $I->assertSame(
            strtr(
                file_get_contents("$fixturesDir/expected/merged.php"),
                [
                    '{{ baseDir }}' => getcwd(),
                ],
            ),
            strtr(
                $I->grabShellOutput() . "\n",
                [
                    ' ' . \PHP_EOL => \PHP_EOL,
                ],
            ),
            'stdOutput',
        );
    }

    public function mergeFilesInputFileNamesFromStdInput(AcceptanceTester $I)
    {
        $fixturesDir = codecept_data_dir('fixtures/case-01');
        $I->runPhpunit($fixturesDir, 'ATest.php');
        $I->runPhpunit($fixturesDir, 'BTest.php');
        $I->runPhpunit($fixturesDir, 'CTest.php');

        $pharPath = $I->grabPharPath();
        $I->assertNotEmpty($pharPath);

        $I->runShellCommand(
            sprintf(
                "find %s -type f | %s merge:files",
                escapeshellarg("$fixturesDir/reports"),
                escapeshellcmd($pharPath),
            ),
        );

        $I->assertSame(
            strtr(
                file_get_contents("$fixturesDir/expected/merged.php"),
                [
                    '{{ baseDir }}' => getcwd(),
                ],
            ),
            strtr(
                $I->grabShellOutput() . "\n",
                [
                    ' ' . \PHP_EOL => \PHP_EOL,
                ],
            ),
            'stdOutput',
        );
    }
}
