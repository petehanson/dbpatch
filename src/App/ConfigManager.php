<?php

namespace uarsoftware\dbpatch\App;

class ConfigManager {

    protected $configs = array();

    public function __construct() {
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
