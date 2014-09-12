<?php


class configuration {

    protected $configs = array();

    public function __construct() {
    }

    public function addConfig(Config $config) {
        $this->configs[$config->getID()] = $config;
    }

    public function getConfigs() {
        return $this->configs;
    }

    public function getConfig($id) {
        return $this->configs[$id];
    }
}
