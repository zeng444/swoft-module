<?php declare(strict_types=1);

namespace Swoft\Module;


/**
 * Author:Robert
 *
 * Class Handle
 * @package Swoft\Module
 */
interface ModuleInterface
{

    /**
     * @param string $module
     * @return bool
     */
    public function exist(string $module): bool;

    /**
     * @param string $module
     * @param string $logic
     * @return mixed
     */
    public function getBean(string $module, string $logic);

    /**
     * @param string $module
     * @param string $logic
     * @param string $method
     * @param $args
     * @return mixed
     */
    public function call(string $module, string $logic, string $method, $args);

    /**
     * @param string $module
     * @param string $logic
     * @return mixed
     */
    public function getLogic(string $module, string $logic);
}
