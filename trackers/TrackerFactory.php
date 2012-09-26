<?php

/**
 * Tracker factory - generates different flavors of version trackers (file, db.. etc.)
 *
 * @author fxneo
 */
class Tracker_Factory {
    public static function Create($dbName, $printer, $baseFolder, $storeInFile, $db)
    {
        if ($storeInFile)
            return new File_Version_Tracker ($dbName, $baseFolder, $db);
        
        return new DbVersionTracker($db);
    }
}

?>
