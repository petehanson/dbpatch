<?php

// need to run with phpunit --bootstrap boostrap.php path/to/test.php

namespace uarsoftware\dbpatch\App;

use uarsoftware\dbpatch\App\StatementParser;

class StatementParserTest extends \PHPUnit_Framework_TestCase
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
        $this->files[6] = $this->makeFile(\TestFiles::$schemaPath . DIRECTORY_SEPARATOR . "7test.sql",$this->content7());
    }

    public function tearDown() {
        unlink($this->files[6]);
        unlink($this->files[5]);
        unlink($this->files[4]);
        unlink($this->files[3]);


        // run this last, since it removes the folders that unlinks above look for.
        \TestFiles::tearDownFiles();
    }

    public function testInstantiation() {
        $parser = new StatementParser();
        $this->assertInstanceOf('uarsoftware\dbpatch\App\StatementParser',$parser);
    }

    public function testStatements() {

        $parser = new StatementParser();

        // test a statement file with a single SQL statement
        $content = file_get_contents($this->files[0]);
        $statements = $parser->getStatements($content);
        $this->assertCount(1,$statements);

        // test the statement parser with 0 content
        $content = "";
        $statements = $parser->getStatements($content);
        $this->assertCount(0,$statements);


        // patch with three statements
        $content = file_get_contents($this->files[3]);
        $statements = $parser->getStatements($content);
        $this->assertCount(3,$statements);


        // patch with statements where we've put a ; in a string somewhere
        $content = file_get_contents($this->files[4]);
        $statements = $parser->getStatements($content);
        $this->assertCount(2,$statements);

        $this->assertEquals("insert into test1 values ('foobar; today')",$statements[0]);
        $this->assertEquals("insert into test1 values ('hello \'world','test test2','test test 3',\"test4\")",$statements[1]);

        // a more complex patch file, with a multi-line create table statement
        $content = file_get_contents($this->files[5]);
        $statements = $parser->getStatements($content);
        $this->assertCount(4,$statements);

        // test that gets one statement, that isn't terminated by a ;
        $content = file_get_contents($this->files[6]);
        $statements = $parser->getStatements($content);
        $this->assertCount(1,$statements);
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
alter table test1 add person varchar(255) not null
EOF;
        return $content;
    }

}
