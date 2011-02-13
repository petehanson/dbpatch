<?php
/**        
 *  abstract class printerbase: store level of output desired and 
 *  invoke print function when needed.
 * 
 *  @package printerbase.php
 *  @todo Consider defining static variable mnemonics for variables
 *  to make it easier to understand what levels exist.
 *
 */
/**
 * class printerbase - abstract class for feedback to caller
 * 
 * @package printerbase
 *
 */

abstract class printerbase
{
    protected $printLevel;
    
    public function __construct($level = 1)
    {
	$this->printLevel = $level;
    }

    public function write($string,$level = 1)
    {
	if ($level <= $this->printLevel)
	{
	    $this->doWrite($string);
	}
    }
    
    abstract public function doWrite($string);
}

?>
