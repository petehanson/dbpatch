<?php

/**
 * The sole responsibility of this
 * component is to generate a patch file on file system
 *
 */
class Patch_File_Bundler {
    
    protected $dbName;
    protected $rootFolder;
    
    public function __construct($dbName, $rootFolder) {
        $this->dbName = $dbName;
        $this->rootFolder = $rootFolder;
    }
    
    /**
     * Bundle files and store into a patch SQL file on disk
     */
    public function bundleFilesToDefaultPatchFile($filePaths) {
        $succeeded = false;
        
        // Open file for writing
        $openedFile = fopen($this->rootFolder . "/" .
                $this->dbName . "_patchfile.sql", "w"); 

        if (!$openedFile)
            die ("Could not open file to bundle resources! check your permissions.");

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
            
            $succeeded = true;
            
        } catch (Exception $e) {
            echo "{$e->getMessage()}\n";
            $succeeded = false;
        }

        fclose($openedFile);
        
        return $succeeded;
    }
}

?>
