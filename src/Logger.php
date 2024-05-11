<?php

declare(strict_types=1);

namespace Woox\WebmanException;

class Logger extends \support\Log
{
    /**
     * @desc __callStatic
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        if ($name === 'error') {
            return parent::__callStatic($name, $arguments);
        }
    }
}
