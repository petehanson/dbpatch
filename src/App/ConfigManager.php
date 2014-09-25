<?php

namespace uarsoftware\dbpatch\App;
use uarsoftware\dbpatch\Util\Util;

class ConfigManager {

    protected $configs = array();

    public function __construct() {
    }

    public function configFullPath($configPath,$basePath) {

        if (file_exists($configPath)) {
            return $configPath;
        }

        $fullPath = $basePath . DIRECTORY_SEPARATOR . $configPath;
        $fullPath = Util::get_absolute_path($fullPath);
        $fullPath = realpath($fullPath);

        if ($fullPath === false) {
            throw new \exception("Full path retrieval for the config file failed, config file or path doesn't exist");
        }

        return $fullPath;
    }

    public function addConfig(Config $config) {
        $this->configs[$config->getID()] = $config;
    }

    public function addConfigsByArray(Array $list) {
        foreach ($list as $config) {
            $this->addConfig($config);
        }
    }

    public function getConfigs() {
        return $this->configs;
    }

    public function getConfig($id) {

        if (array_key_exists($id,$this->configs)) {
            return $this->configs[$id];
        } else {
            throw new exception("configuration {$id} doesn't exist in the configuration manager");
        }

    }

    public function hasMultipleConfigs() {
        if (count($this->configs) > 1) {
            return true;
        } else {
            return false;
        }
    }

    public function hasValidConfig() {
        if (count($this->configs) > 0) {
            return true;
        } else {
            return false;
        }
    }
}
