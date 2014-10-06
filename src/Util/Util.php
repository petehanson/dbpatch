<?php

namespace uarsoftware\dbpatch\Util;

class Util {
    public static function getAbsolutePath($path) {
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $absolutes);
    }

    public static function recursiveDirectoryFileSearch($startingDirectory,$searchFile) {

        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($startingDirectory), \RecursiveIteratorIterator::SELF_FIRST);
        foreach($objects as $name => $object){
            if ($object->getFilename() == $searchFile) {
                return $object->getPathname();
            }

        }

        return false;
    }
}