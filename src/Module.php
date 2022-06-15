<?php declare(strict_types=1);

namespace Swoft\Module;

use Swoft;
use Swoft\Bean\BeanFactory;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Config\Annotation\Mapping\Config;
use Swoft\Module\Exception\ModuleException;

/**
 * Author:Robert
 *
 * Class Handle
 * @Bean()
 * @package Swoft\Module
 */
class Module implements ModuleInterface
{

    /**
     * @Config("app.module")
     * @var string
     */
    protected $path = "@app/Module/";

    /**
     * @return void
     */
    public function init(): void
    {
        $this->loadBean();
    }


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
     * @param string $bean
     * @return mixed
     * @throws ModuleException
     */
    public function getBean(string $module, string $bean)
    {
        $bean = $module . '.' . $bean;
        if (!Swoft::hasBean($bean)) {
            throw new ModuleException('Bean not exit');
        }
        return Swoft::getBean($bean);
    }

    /**
     * @param string $module
     * @param string $logic
     * @return mixed|object
     * @throws ModuleException
     */
    public function getLogic(string $module, string $logic)
    {
        $module = ucwords($module);
        if (!$this->exist($module)) {
            throw new ModuleException("module not exist");
        }
        $class = "App\\Module\\$module\\Logic\\" . $logic;
        if (!class_exists($class)) {
            throw new ModuleException("module class not exist");
        }
        return Swoft::getBean($class);
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
        return ($this->getLogic($module, $logic))->$method(...(!is_array($args) ? [$args] : $args));
    }


    /**
     * @return void
     */
    private function loadBean(): void
    {
        $basePath = alias($this->path);
        $dir = scandir($basePath);
        foreach ($dir as $module) {
            if ($module === '.' || $module === '..') {
                continue;
            }
            $path = $basePath . $module . '/bean.php';
            if (!file_exists($path)) {
                continue;
            }
            $config = require($path);
            foreach ($config as $bean => $cnf) {
                BeanFactory::createBean($module . '.' . $bean, $cnf);
            }
        }
    }

}
