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

    public function getConfig($path) {
        if (!file_exists($path)) {
            throw new \exception("Config file loading failed. Config file does not exist at {$path}");
        }
        $config = require($path);
        return $config;
    }
}
