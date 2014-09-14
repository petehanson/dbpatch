<?php

// need to run with phpunit --bootstrap boostrap.php path/to/test.php

namespace uarsoftware\dbpatch\App;

use uarsoftware\dbpatch\App\Config;
use uarsoftware\dbpatch\App\Patch;

class PatchTest extends \PHPUnit_Framework_TestCase
{
    protected $config;

    public function testInstantiation() {
        $patch = new Patch("test.sql");
        $this->assertInstanceOf('uarsoftware\dbpatch\App\Patch',$patch);
    }

    public function testParameters() {
        $patch = new Patch("sql/schema/test.sql");
        $this->assertEquals("test.sql",$patch->getBaseName());
    }


}
