<?php

declare(strict_types = 1);

namespace Sweetchuck\CoverageMergerCli\Command;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Sweetchuck\CoverageMerger\CoverageMergerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

#[AsCommand(
    name: 'coverage:merge',
    description: 'Merges two or more coverage PHP files into one.',
)]
class MergeFiles extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        string $name,
        protected CoverageMergerInterface $coverageMerger,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Merges two or more coverage PHP files into one.')
            ->addOption(
                'output-file',
                'o',
                InputOption::VALUE_REQUIRED,
                'Destination for the final coverage PHP file.',
                'php://stdout',
            )
            ->addArgument(
                'input-files',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Coverage PHP filenames to merge into one file. By default filenames will be read from the stdInput.',
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputFiles = $this->createInputFilesIterator($input);
        $exitCode = 0;

        try {
            $this->prepareOutputDirectory($input);
            $output = $this->createOutput($input);
            $this->coverageMerger->merge($inputFiles);
            $output->write($this->coverageMerger->getFileContent());
            $this->tearDownOutput($output);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $exitCode = max((int) $exception->getCode(), 1);
        }

        return $exitCode;
    }

    protected function createInputFilesIterator(InputInterface $input): \Iterator
    {
        $inputFiles = $input->getArgument('input-files');

        return count($inputFiles) ?
            new \ArrayIterator($inputFiles)
            : new \SplFileObject('php://stdin');
    }

    protected function prepareOutputDirectory(InputInterface $input): static
    {
        $fileName = $input->getOption('output-file');
        if ($fileName === null || $fileName === '') {
            return $this;
        }

        $scheme = parse_url($fileName, \PHP_URL_SCHEME);
        if ($scheme && !in_array($scheme, ['file', 'vfs'])) {
            // Unsupported schema.
            return $this;
        }

        $dir = dirname($fileName);
        if (file_exists($dir) || mkdir($dir, 0777 - umask(), true)) {
            return $this;
        }

        throw new \RuntimeException("destination directory for file '$fileName' could not be created", 1);
    }

    protected function createOutput(InputInterface $input): OutputInterface
    {
        $fileName = $input->getOption('output-file');
        $fileHandler = $fileName === null || $fileName === ''
            ? \STDOUT
            : fopen($fileName, 'w+');
        if ($fileHandler === false) {
            throw new \RuntimeException("could not open file '$fileName' for writing", 1);
        }

        return new StreamOutput(
            $fileHandler,
            OutputInterface::VERBOSITY_VERY_VERBOSE | OutputInterface::OUTPUT_RAW,
            false,
        );
    }

    protected function tearDownOutput(OutputInterface $output): static
    {
        if ($output instanceof StreamOutput) {
            fclose($output->getStream());
        }

        return $this;
    }
}
