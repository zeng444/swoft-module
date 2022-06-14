<?php declare(strict_types=1);

namespace Swoft\Module;

use Swoft;

/**
 * Author:Robert
 *
 * Class Handle
 * @package Swoft\Module
 */
class Module
{
    /**
     * @param string $name
     * @return bool
     */
    public static function exist(string $name): bool
    {
        return Swoft::getBean('module')->exist($name);
    }

    /**
     * @param string $module
     * @param string $logic
     * @return mixed
     */
    public static function getBean(string $module, string $logic)
    {
        return Swoft::getBean('module')->getBean($module, $logic);
    }

    /**
     * @param string $module
     * @param string $logic
     * @param string $method
     * @param $args
     * @return mixed
     */
    public static function call(string $module, string $logic, string $method, $args)
    {
        return Swoft::getBean('module')->call($module, $logic, $method, $args);
    }
}
