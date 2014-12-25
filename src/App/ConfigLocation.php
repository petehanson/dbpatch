<?php

namespace uarsoftware\dbpatch\App;
use uarsoftware\dbpatch\Util\Util;

class ConfigLocation {

    protected $path;

    protected $configLocations = array();

    public function __construct($path) {
        $this->path = $path;

        $this->processFile();
    }

    protected function processFile() {

        $lines = file($this->path);
        $configLocations = array();

        foreach ($lines as $line) {
            $parts = explode(":",$line);
            $configLocations[trim($parts[0])] = trim($parts[1]);
        }

        $this->configLocations = $configLocations;
    }

    public function doesFileExist() {
        return file_exists($this->path);
    }

    public function configLocationCount() {
        return count($this->configLocations);
    }

    public function doesConfigLocationExist($key) {
        return array_key_exists($key,$this->configLocations);
    }

    public function getConfigLocation($key) {
        if ($this->doesConfigLocationExist($key)) {
            return $this->configLocations[$key];
        } else {
            return null;
        }
    }

    public function getFirstConfigLocation() {
        return array_shift(array_values($this->configLocations));
    }
}
