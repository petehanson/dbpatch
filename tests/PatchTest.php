<?php

// need to run with phpunit --bootstrap boostrap.php path/to/test.php

namespace uarsoftware\dbpatch\App;

use uarsoftware\dbpatch\App\Config;
use uarsoftware\dbpatch\App\Patch;

class PatchTest extends \PHPUnit_Framework_TestCase
{
    protected $config;
    protected $files;

    public function setUp() {
        \TestFiles::setUpFiles();
        $this->files = \TestFiles::$files;

        $this->files[3] = $this->makeFile(\TestFiles::$schemaPath . DIRECTORY_SEPARATOR . "4test.sql",$this->content4());
        $this->files[4] = $this->makeFile(\TestFiles::$schemaPath . DIRECTORY_SEPARATOR . "5test.sql",$this->content5());
        $this->files[5] = $this->makeFile(\TestFiles::$schemaPath . DIRECTORY_SEPARATOR . "6test.sql",$this->content6());
    }

    public function tearDown() {
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
        $statements = $patch->getPatchStatements();
        $this->assertCount(1,$statements);

        // patch with three statements
        $patch = new Patch($this->files[3]);
        $statements = $patch->getPatchStatements();
        $this->assertCount(3,$statements);

        // patch with statements where we've put a ; in a string somewhere
        $patch = new Patch($this->files[4]);
        $statements = $patch->getPatchStatements();
        $this->assertCount(2,$statements);

        $this->assertEquals("insert into test1 values ('foobar; today')",$statements[0]);
        $this->assertEquals("insert into test1 values ('hello \'world','test test2','test test 3',\"test4\")",$statements[1]);

        // a more complex patch file, with a multi-line create table statement
        $patch = new Patch($this->files[5]);
        $statements = $patch->getPatchStatements();
        $this->assertCount(4,$statements);

        // test that gets one statement, that isn't terminated by a ;
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

}
