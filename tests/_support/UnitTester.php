<?php

declare(strict_types = 1);

namespace Sweetchuck\CoverageMergerCli\Tests;

use Codeception\Actor;
use Symfony\Component\Process\Process;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
 */
class UnitTester extends Actor
{
    use _generated\UnitTesterActions;

    public function runPhpunit(string $dir, string $testFile)
    {
        $phpunitExecutable = realpath('vendor/bin/phpunit');
        $command = [
            $phpunitExecutable,
            "--coverage-php=reports/" . preg_replace('@Test\.php$@', '.php', $testFile),
            "tests/$testFile",
        ];

        $process = new Process($command, $dir);
        $process->run();
        if ($process->getExitCode() !== 0) {
            throw new \Exception($process->getOutput() . "\n\n" . $process->getErrorOutput());
        }
    }

    public function grabPhpVersionMajorMinor(): string
    {
        $full = str_pad((string) \PHP_VERSION_ID, 6, '0', \STR_PAD_LEFT);

        return substr($full, 0, 4);
    }
}
