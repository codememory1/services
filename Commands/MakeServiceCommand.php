<?php

namespace Codememory\Components\Services\Commands;

use Codememory\Components\Console\Command;
use Codememory\Components\Services\Utils;
use Codememory\FileSystem\File;
use Codememory\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MakeServiceCommand
 *
 * @package Codememory\Components\Service\Commands
 *
 * @author  Codememory
 */
class MakeServiceCommand extends Command
{

    /**
     * @inheritDoc
     */
    protected ?string $command = 'make:service';

    /**
     * @inheritDoc
     */
    protected ?string $description = 'Create a service for processing logic';

    /**
     * @inheritDoc
     */
    protected function wrapArgsAndOptions(): Command
    {

        $this->addArgument('name', InputArgument::REQUIRED, 'Service name without suffix');
        $this->addOption('re-create', null, InputOption::VALUE_NONE, 'Recreate the service if a service with the same name already exists');

        return $this;

    }

    /**
     * @inheritDoc
     */
    protected function handler(InputInterface $input, OutputInterface $output): int
    {

        $filesystem = new File();
        $utils = new Utils();
        $serviceName = $input->getArgument('name');
        $className = $serviceName . $utils->getServiceSuffix();
        $fullPath = sprintf('%s%s.php', $utils->getPathWithServices(), $className);
        $namespace = Str::trimAfterSymbol($utils->getNamespaceService(), '\\', false);

        if (!$filesystem->exist($utils->getPathWithServices())) {
            $filesystem->mkdir($utils->getPathWithServices(), 0777, true);
        }

        $stubService = $this->getBuiltService($namespace, $className);

        if ($filesystem->exist($fullPath) && !$input->getOption('re-create')) {
            $this->io->error(sprintf('A service named %s already exists', $serviceName));

            return self::FAILURE;
        }

        file_put_contents($fullPath, $stubService);

        $this->io->success([
            sprintf('Service %s created successfully', $serviceName),
            sprintf('Path: %s', $fullPath)
        ]);

        return self::SUCCESS;

    }

    /**
     * @param string $namespace
     * @param string $className
     *
     * @return string
     */
    private function getBuiltService(string $namespace, string $className): string
    {

        return str_replace([
            '{namespace}',
            '{className}'
        ], [
            $namespace,
            $className
        ], $this->getServiceStub());

    }

    /**
     * @return string
     */
    private function getServiceStub(): string
    {

        return file_get_contents(__DIR__ . '/Stubs/ServiceStub.stub');

    }

}