<?php

namespace Codememory\Components\Services;

use Codememory\Components\Configuration\Configuration;
use Codememory\Components\Configuration\Interfaces\ConfigInterface;
use Codememory\Components\GlobalConfig\GlobalConfig;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class Utils
 *
 * @package Codememory\Components\Service
 *
 * @author  Codememory
 */
class Utils
{

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * Utils Construct.
     */
    public function __construct()
    {

        $this->config = Configuration::getInstance()->open(GlobalConfig::get('service.configName'), $this->defaultConfig());

    }

    /**
     * @return string
     */
    public function getPathWithServices(): string
    {

        return trim($this->config->get('pathWithServices'), '/') . '/';

    }

    /**
     * @return string
     */
    public function getNamespaceService(): string
    {

        return trim($this->config->get('namespaceService'), '\\') . '\\';

    }

    /**
     * @return string
     */
    public function getServiceSuffix(): string
    {

        return $this->config->get('serviceSuffix');

    }

    /**
     * @return array
     */
    #[ArrayShape([
        'pathWithServices' => "mixed",
        'namespaceService' => "mixed",
        'serviceSuffix'    => "mixed"
    ])]
    private function defaultConfig(): array
    {

        return [
            'pathWithServices' => GlobalConfig::get('service.pathWithServices'),
            'namespaceService' => GlobalConfig::get('service.namespaceService'),
            'serviceSuffix'    => GlobalConfig::get('service.serviceSuffix')
        ];

    }

}