<?php
/**        
 * class printer, implementing a command line echo of supplied
 * user feedback.
 * @package cli.php
 *
 */
/**
 * requiring printerbase.php
 *
 */
require_once("printerbase.php");

/**
 * class printer extending printerbase
 * 
 * @package printer
 *
 */
class printer extends printerbase
{
/**        
 *  function doWrite:  echos the supplied string
 *  @param string $string Data to be output
 *
 */

    public function doWrite($string)
    {
	echo $string . "\n";
    }
}

?>
