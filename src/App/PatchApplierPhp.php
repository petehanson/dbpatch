<?php

namespace uarsoftware\dbpatch\App;

class PatchApplierPhp extends PatchApplierAbstract {

    public function apply(PatchInterface $patch,DatabaseInterface $db) {

        $path = $patch->getPatchName();

        $this->statementCount = 1;

        // this wraps the include call in a local function so that it doesn't have access to any of the other
        // properties or variables
        $localInclude = function ($path) {
            return include($path);
        };

        try {
            $result = $localInclude($path);


            if ($result == true) {
                $this->status = true;
            } else {
                $this->status = false;
                $this->errorCode = 1;
                $this->errorMessage = "The script in " . $path . " failed. Did you remember to \"return true;\"?";
            }

        } catch (Exception $e) {

            $this->status = false;
            $this->errorCode = $e->getCode();
            $this->errorMessage = "The script in " . $path . " threw an exception. Message: " . $e->getMessage();
        }

        return $this->status;
    }
}