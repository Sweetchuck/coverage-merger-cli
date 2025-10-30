<?php

declare(strict_types = 1);

namespace Sweetchuck\CoverageMergerCli;

use Sweetchuck\CoverageMerger\CoverageMerger;
use Sweetchuck\CoverageMergerCli\Command\MergeFiles;
use Symfony\Component\Console\Application as ApplicationBase;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition as ServiceDefinition;
use Symfony\Component\DependencyInjection\Reference as ServiceReference;

class Application extends ApplicationBase
{

    public function initialize(): static
    {
        $container = new ContainerBuilder();

        $service = new ServiceDefinition(ConsoleOutput::class);
        $container->setDefinition('output', $service);

        $service = new ServiceDefinition(ConsoleLogger::class);
        $service->addArgument(new ServiceReference('output'));
        $container->setDefinition('logger', $service);

        $container->register('coverage_merger', CoverageMerger::class);

        $cmdMerge = new MergeFiles(
            'merge:files',
            // @phpstan-ignore-next-line
            $container->get('coverage_merger'),
            // @phpstan-ignore-next-line
            $container->get('logger'),
        );
        $this->add($cmdMerge);

        return $this;
    }
}
