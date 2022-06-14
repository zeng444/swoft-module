<?php declare(strict_types=1);

namespace Swoft\Module;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Config\Annotation\Mapping\Config;

/**
 * Author:Robert
 *
 * Class Handle
 * @Bean()
 * @package Swoft\Module
 */
class ModuleCaller implements ModuleInterface
{

    /**
     * @Config("app.module")
     * @var string
     */
    protected $path = "@app/Module/";


    /**
     * @param string $name
     * @return bool
     */
    public function exist(string $name): bool
    {
        return is_dir(@alias($this->path . $name));
    }

    /**
     * @param string $module
     * @param string $logic
     * @return mixed|object
     * @throws ModuleException
     */
    public function getBean(string $module, string $logic)
    {
        $module = ucwords($module);
        if (!$this->exist($module)) {
            throw new ModuleException("module not exist");
        }
        $class = "App\\Module\\$module\\Logic\\" . $logic;
        if (!class_exists($class)) {
            throw new ModuleException("module class not exist");
        }
        return \Swoft::getBean($class);
    }

    /**
     * @param string $module
     * @param string $logic
     * @param string $method
     * @param  $args
     * @return mixed
     * @throws ModuleException
     */
    public function call(string $module, string $logic, string $method, $args)
    {
        return ($this->getBean($module, $logic))->$method(...(!is_array($args) ? [$args] : $args));
    }



}
