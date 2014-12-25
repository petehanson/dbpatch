<?php

// need to run with phpunit --bootstrap boostrap.php path/to/test.php

namespace uarsoftware\dbpatch\App;

use uarsoftware\dbpatch\App\Config;
use uarsoftware\dbpatch\App\Patch;
use uarsoftware\dbpatch\App\PatchApplierSql;
use uarsoftware\dbpatch\App\PatchApplierPhp;
use uarsoftware\dbpatch\App\PatchApplierAbstract;
use uarsoftware\dbpatch\App\PatchApplierInterface;

class PatchTest extends \PHPUnit_Framework_TestCase
{
    protected $config;
    protected $files;
    protected $db;

    public function setUp() {
        \TestFiles::setUpFiles();
        $this->files = \TestFiles::$files;

        $this->files[3] = $this->makeFile(\TestFiles::$schemaPath . DIRECTORY_SEPARATOR . "4test.sql",$this->content4());
        $this->files[4] = $this->makeFile(\TestFiles::$schemaPath . DIRECTORY_SEPARATOR . "5test.sql",$this->content5());
        $this->files[5] = $this->makeFile(\TestFiles::$schemaPath . DIRECTORY_SEPARATOR . "6test.sql",$this->content6());
        $this->files[6] = $this->makeFile(\TestFiles::$schemaPath . DIRECTORY_SEPARATOR . "7test.php",$this->content7());
        $this->files[7] = $this->makeFile(\TestFiles::$schemaPath . DIRECTORY_SEPARATOR . "8test.php",$this->content8());
        $this->files[8] = $this->makeFile(\TestFiles::$schemaPath . DIRECTORY_SEPARATOR . "9test.php",$this->content9());

        $this->config = new Config("test","mysql","localhost","test","root","root");
        $this->config->setBasePath(realpath("/tmp"));

        $this->db = new \MockDatabase($this->config);

    }

    public function tearDown() {
        unlink($this->files[8]);
        unlink($this->files[7]);
        unlink($this->files[6]);
        unlink($this->files[5]);
        unlink($this->files[4]);
        unlink($this->files[3]);


        // run this last, since it removes the folders that unlinks above look for.
        \TestFiles::tearDownFiles();
    }

    public function testInstantiation() {
        $patch = new Patch("test.sql");
        $this->assertInstanceOf('uarsoftware\dbpatch\App\Patch',$patch);
    }

    public function testParameters() {
        $patch = new Patch("sql/schema/test.sql");
        $this->assertEquals("test.sql",$patch->getBaseName());
    }

    public function testIsRealFile() {
        $patch = new Patch($this->files[0]);
        $this->assertTrue($patch->isRealFile());

        $patch = new Patch("3test.sql");
        $this->assertFalse($patch->isRealFile());

    }

    public function testAppliedPatch() {
        $patch = new Patch($this->files[0]);
        $this->assertFalse($patch->hasBeenApplied());

        $patch = new Patch("3test.sql");
        $patch->setAsAppliedPatch();
        $this->assertTrue($patch->hasBeenApplied());

    }

    public function testPatchSuccess() {
        $patch = new Patch($this->files[0]);
        $patch->setSuccessful();
        $this->assertTrue($patch->isSuccessful());


        $patch = new Patch($this->files[0]);
        $patch->setFailed(100,"test message");

        $this->assertFalse($patch->isSuccessful());
        $this->assertEquals(100,$patch->getErrorCode());
        $this->assertEquals("test message",$patch->getErrorMessage());

    }


    public function testPatchStatements() {
        // single statement
        $patch = new Patch($this->files[0]);
        $pa = $patch->getPatchApplier();
        $pa->apply($patch,$this->db);
        $this->assertEquals(1,$pa->getStatementCount());

        // patch with three statements
        $patch = new Patch($this->files[3]);
        $pa = $patch->getPatchApplier();
        $pa->apply($patch,$this->db);
        $this->assertEquals(3,$pa->getStatementCount());

        // patch with statements where we've put a ; in a string somewhere
        $patch = new Patch($this->files[4]);
        $pa = $patch->getPatchApplier();
        $pa->apply($patch,$this->db);
        $this->assertEquals(2,$pa->getStatementCount());

        // a more complex patch file, with a multi-line create table statement
        $patch = new Patch($this->files[5]);
        $pa = $patch->getPatchApplier();
        $pa->apply($patch,$this->db);
        $this->assertEquals(4,$pa->getStatementCount());
    }

    public function testPhpScripts() {

        // test a PHP script that returns true
        $patch = new Patch($this->files[6]);
        $pa = $patch->getPatchApplier();
        $result = $pa->apply($patch,$this->db);
        $this->assertTrue($result);

        // test a PHP script that returns false
        $patch = new Patch($this->files[7]);
        $pa = $patch->getPatchApplier();
        $result = $pa->apply($patch,$this->db);
        $this->assertFalse($result);

    }

    public function testPhpScriptException() {
        $this->setExpectedException('\exception');

        // test exception capturing from the executed script
        $patch = new Patch($this->files[8]);
        $pa = $patch->getPatchApplier();
        $result = $pa->apply($patch,$this->db);
        $this->assertFalse($result);

    }


    public function testPatchExtensionIdentification() {
        $patch = new Patch($this->files[5]);
        $this->assertInstanceOf('uarsoftware\dbpatch\App\PatchApplierSql',$patch->getPatchApplier());

        $patch = new Patch($this->files[6]);
        $this->assertInstanceOf('uarsoftware\dbpatch\App\PatchApplierPhp',$patch->getPatchApplier());

    }

    protected function makeFile($path,$content) {
        file_put_contents($path,$content);
        return $path;
    }

    protected function content4() {
$content = <<<EOF
create table test1 (id int);
alter table test1 add name varchar(255);
alter table test1 add dob date;
EOF;
        return $content;
    }

    protected function content5() {
        $content = <<<EOF
insert into test1 values ('foobar; today');
insert into test1 values ('hello \'world','test test2','test test 3',"test4");
EOF;
        return $content;
    }

    protected function content6() {
        $content = <<<EOF
create table test1(
 id int not null auto_increment,
 field1 varchar(255) not null,
 field2 varchar(255) not null,
 field3 date not null,
 field4 timestamp default value current_timestamp,
 field5 int not null,
 primary key (id),
 constraint test1_fk_0 foreign key test2 (id)
 );
insert into test1 values ('foobar; today');
insert into test1 values ('hello \'world','test test2','test test 3',"test4");
select * from test1 where field1 like '%foo%';
EOF;
        return $content;
    }

    protected function content7() {
        $content = <<<EOF
<?php

return true;
EOF;
        return $content;
    }

    protected function content8() {
        $content = <<<EOF
<?php

return false;
EOF;
        return $content;
    }

    protected function content9() {
        $content = <<<EOF
<?php

throw new exception("I failed");
EOF;
        return $content;
    }

}
