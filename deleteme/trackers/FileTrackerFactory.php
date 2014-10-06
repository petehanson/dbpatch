<?php

/**
 * Tracker factory - generates different flavors of file version trackers (xml, json.. etc.)
 *
 * @author fxneo
 */
class File_Tracker_Factory {

    public static function Create($dbName, $printer, $baseFolder, $dbAppliedPatchNames) {
        return new Xml_File_Version_Tracker($dbName, $baseFolder, $dbAppliedPatchNames);
    }

}

?>
