<?php declare(strict_types=1);

namespace Swoft\Module;

use Swoft;
use Swoft\Bean\BeanFactory;
use Swoft\Bean\Annotation\Mapping\Bean;
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
     * @var string
     */
    protected $path;

    /**
     * @var bool
     */
    protected $strict = true;

    /**
     * @var string
     */
    protected $autoloaderFiler = "Autoloader.php";

    /**
     * @var array
     */
    private $conf = [];

    /**
     * @var array
     */
    private $moduleVer = [];

    /**
     * @return void
     * @throws ModuleException
     */
    public function init(): void
    {
        $this->loadConf();
        $this->loadBean();
        $this->checkDepends();
    }

    /**
     * @param string $module
     * @param bool $strict
     * @return bool
     */
    public function exist(string $module, bool $strict = true): bool
    {
        return $strict ? isset($this->conf[$module]) : is_dir(@alias($this->path . $module));
    }

    /**
     * @param string $module
     * @param string $bean
     * @return bool
     */
    public function hasBean(string $module, string $bean): bool
    {
        $bean = ucfirst($module) . '.' . $bean;
        return Swoft::hasBean($bean);
    }

    /**
     * @param string $module
     * @param string $bean
     * @return mixed
     * @throws ModuleException
     */
    public function getBean(string $module, string $bean)
    {
        $bean = ucfirst($module) . '.' . $bean;
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
        $module = ucfirst($module);
        if (!$this->exist($module, $this->strict)) {
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
     * @param string $module
     * @return string
     */
    public function getModuleVer(string $module): string
    {
        return $this->moduleVer[ucfirst($module)] ?? '';
    }

    /**
     * @param string $module
     * @return string
     */
    public function getModuleVersion(string $module): string
    {
        return $this->getModuleVer($module);
    }

    /**
     * @param string $module
     * @return string
     */
    public function getModuleVersions(): array
    {
        return $this->moduleVer;
    }

    /**
     * @param string|null $module
     * @return array
     */
    public function getConfig(string $module = null): array
    {
        return $module ? ($this->conf[ucfirst($module)] ?? []) : $this->conf;
    }

    /**
     * @return void
     * @throws ModuleException
     */
    private function loadConf(): void
    {
        $basePath = alias($this->path);
        $dir = scandir($basePath);
        foreach ($dir as $module) {
            if ($module === '.' || $module === '..') {
                continue;
            }
            $path = $basePath . $module . '/' . $this->autoloaderFiler;
            if (!file_exists($path)) {
                continue;
            }
            $config = require($path);
            // Load module version info
            if (!isset($config['module']) || !isset($config['module']['version']) || !$config['module']['version']) {
                throw new ModuleException("Module $module version not defined");
            }
            $config['module']['depends'] = $config['module']['depends'] ?? [];
            $config['module']['verCheck'] = $config['module']['verCheck'] ?? true;
            $this->addConfig($module, $config);
            $this->addModuleVer($module, $config['module']['version']);
        }
    }

    /**
     * @return void
     * @throws ModuleException
     */
    private function checkDepends(): void
    {
        $modules = $this->conf;
        foreach ($modules as $configs) {
            if (!$configs['module']['verCheck']) {
                continue;
            }
            foreach ($configs['module']['depends'] as $depend => $ver) {
                if (version_compare($this->getModuleVer($depend), $ver, '<')) {
                    throw new ModuleException("Module $depend require version >= $ver");
                }
            }
        }
    }

    /**
     * @return void
     */
    private function loadBean(): void
    {
        $modules = $this->getConfig();
        foreach ($modules as $module => $configs) {
            foreach ($configs['bean'] as $bean => $cnf) {
                BeanFactory::createBean($module . '.' . $bean, $cnf);
            }
        }
    }

    /**
     * @param string $module
     * @param string $ver
     * @return void
     */
    private function addModuleVer(string $module, string $ver): void
    {
        $this->moduleVer[$module] = $ver;
    }

    /**
     * @param string $module
     * @param array $config
     * @return void
     */
    private function addConfig(string $module, array $config): void
    {
        $this->conf[$module] = $config;
    }

}
