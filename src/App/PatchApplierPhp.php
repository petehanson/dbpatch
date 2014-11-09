<?php

namespace uarsoftware\dbpatch\App;

class PatchApplierPhp extends PatchApplierAbstract {

    public function apply(PatchInterface $patch,DatabaseInterface $db) {

        $path = $patch->getPatchName();

        $this->statementCount = 1;

        function localInclude($path) {
            return include($path);
        }

        try {
            $result = localInclude($path);


            if ($result == true) {
                $this->status = true;
            } else {
                $this->status = false;
                $this->errorCode = 1;
                $this->errorMessage = "The script in " . $path . " failed";
            }

        } catch (Exception $e) {

            $this->status = false;
            $this->errorCode = $e->getCode();
            $this->errorMessage = $e->getMessage();
        }

        return $this->status;
    }
}