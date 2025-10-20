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

        $expectedFileName = 'merged-' . $I->grabPhpVersionMajorMinor() . '-acceptance.php';
        if (!file_exists("$fixturesDir/expected/$expectedFileName")) {
            $expectedFileName = 'merged-' . $I->grabPhpVersionMajorMinor() . '-unit.php';
        }

        $expectedFilePath = "$fixturesDir/expected/$expectedFileName";
        $expectedContent = file_get_contents($expectedFilePath);

        $actualContent = strtr(
            $I->grabShellOutput() . "\n",
            [
                ' ' . \PHP_EOL => \PHP_EOL,
            ],
        );

        $I->assertSame(
            substr($expectedContent, 0, 70),
            substr($actualContent, 0, 70),
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

        $expectedFileName = 'merged-' . $I->grabPhpVersionMajorMinor() . '-acceptance.php';
        if (!file_exists("$fixturesDir/expected/$expectedFileName")) {
            $expectedFileName = 'merged-' . $I->grabPhpVersionMajorMinor() . '-unit.php';
        }

        $expectedFilePath = "$fixturesDir/expected/$expectedFileName";
        $expectedContent = file_get_contents($expectedFilePath);
        $actualContent = strtr(
            $I->grabShellOutput() . "\n",
            [
                ' ' . \PHP_EOL => \PHP_EOL,
            ],
        );

        $I->assertSame(
            substr($expectedContent, 0, 70),
            substr($actualContent, 0, 70),
            'stdOutput',
        );
    }
}
