<?php

/**
 * Helper class used to generate patch file on file system
 *
 */
class patchfilehelper {
    /*
     * Bundle files and store into a patch SQL file
     */

    private function bundleFilesToPatchFile($filePaths) {

        // Open file for writing
        $openedFile = fopen($this->base_folder . "/" .
                $this->dbDefaultPatchTrackingFile, "w");

        if (!$openedFile)
            die("cannot open patch file");

        try {

            fwrite($openedFile, "start transaction;");

            foreach ($filePaths as $file => $path) {
                if (isset($path)) {
                    $sqlFileToRead = fopen($path, "r");
                    if (isset($path)) {
                        while (!feof($sqlFileToRead))
                            fwrite($openedFile, fgets($sqlFileToRead));
                    } else {
                        echo "cannot open file> $p - skipping";
                    }

                    fclose($sqlFileToRead);
                }
            }

            fwrite($openedFile, "commit;");
        } catch (Exception $e) {
            echo "{$e->getMessage()}\n";
        }

        fclose($openedFile);
    }

}

?>
