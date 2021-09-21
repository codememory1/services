<?php

namespace Codememory\Components\Services\Exceptions;

use JetBrains\PhpStorm\Pure;

/**
 * Class ServiceNotExistException
 *
 * @package Codememory\Components\Service\Exceptions
 *
 * @author  Codememory
 */
class ServiceNotExistException extends ServiceException
{

    /**
     * @param string $model
     */
    #[Pure]
    public function __construct(string $model)
    {

        parent::__construct(sprintf('The %s model does not exist', $model));

    }

}