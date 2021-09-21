<?php

namespace Codememory\Components\Services\Interfaces;

use ReflectionClass;

/**
 * Interface ServiceInterface
 *
 * @package Codememory\Components\Service\Interfaces
 *
 * @author  Codememory
 */
interface ServiceInterface
{

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Returns the Reflection Class of the service, or if the class does
     * not exist, a ServiceNotExistException will be thrown
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param string $name
     *
     * @return ReflectionClass
     */
    public function getServiceReflector(string $name): ReflectionClass;

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Generates and returns the complete service namespace
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param string $name
     *
     * @return string
     */
    public function generateServiceNamespace(string $name): string;

}