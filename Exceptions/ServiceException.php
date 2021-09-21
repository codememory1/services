<?php

namespace Codememory\Components\Services\Exceptions;

use ErrorException;
use JetBrains\PhpStorm\Pure;

/**
 * Class ServiceException
 *
 * @package Codememory\Components\Service\Exceptions
 *
 * @author  Codememory
 */
abstract class ServiceException extends ErrorException
{

    /**
     * @param string|null $message
     */
    #[Pure]
    public function __construct(string $message = null)
    {

        parent::__construct($message ?: '');

    }

}