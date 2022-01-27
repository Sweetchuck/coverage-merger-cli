<?php

declare(strict_types = 1);

namespace Sweetchuck\CoverageMergerCli\Command;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Sweetchuck\CoverageMerger\CoverageMergerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class MergeFiles extends Command implements ContainerAwareInterface, LoggerAwareInterface
{
    use ContainerAwareTrait;
    use LoggerAwareTrait;

    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'merge:files';

    /**
     * {@inheritdoc}
     */
    protected function configure()
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputFiles = $this->createInputFilesIterator($input);
        $merger = $this->createMerger();
        $exitCode = 0;

        try {
            $this->prepareOutputDirectory($input);
            $output = $this->createOutput($input);
            $merger->merge($inputFiles);
            $output->write($merger->getFileContent());
            $this->tearDownOutput($output);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $exitCode = max($e->getCode(), 1);
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

    /**
     * @return $this
     */
    protected function prepareOutputDirectory(InputInterface $input)
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
        $fileHandler = $fileName === null || $fileName === '' ?
            \STDOUT
            : fopen($fileName, 'w+');

        return new StreamOutput(
            $fileHandler,
            OutputInterface::VERBOSITY_VERY_VERBOSE | OutputInterface::OUTPUT_RAW,
            false,
        );
    }

    /**
     * @return $this
     */
    protected function tearDownOutput(OutputInterface $output)
    {
        if ($output instanceof StreamOutput) {
            fclose($output->getStream());
        }

        return $this;
    }

    protected function createMerger(): CoverageMergerInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->container->get('coverage_merger');
    }
}
