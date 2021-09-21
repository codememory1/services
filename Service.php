<?php

namespace Codememory\Components\Services;

use Codememory\Components\Services\Exceptions\ServiceNotExistException;
use Codememory\Components\Services\Interfaces\ServiceInterface;
use ReflectionClass;

/**
 * Class Service
 *
 * @package Codememory\Components\Service
 *
 * @author  Codememory
 */
class Service implements ServiceInterface
{

    /**
     * @var Utils
     */
    private Utils $utils;

    /**
     * Service Construct
     */
    public function __construct()
    {

        $this->utils = new Utils();

    }

    /**
     * @inheritDoc
     * @throws ServiceNotExistException
     */
    public function getServiceReflector(string $name): ReflectionClass
    {

        $serviceNamespace = $this->generateServiceNamespace($name);

        if (!class_exists($serviceNamespace)) {
            throw new ServiceNotExistException($serviceNamespace);
        }

        return new ReflectionClass($serviceNamespace);

    }

    /**
     * @inheritDoc
     */
    public function generateServiceNamespace(string $name): string
    {

        $serviceNamespace = $this->utils->getNamespaceService();
        $serviceSuffix = $this->utils->getServiceSuffix();

        return $serviceNamespace . $name . $serviceSuffix;

    }

}