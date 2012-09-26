start transaction;
create table myfoo (
	id int not null auto_increment primary key,
	name varchar(255));
insert into table1 values (DEFAULT,'Pete');
insert into table1 values (DEFAULT,'Bob');
insert into table1 values (DEFAULT,'Fred');
insert into table2 values (DEFAULT,1,1);
insert into table2 values (DEFAULT,1,2);
insert into table2 values (DEFAULT,2,1);
insert into table2 values (DEFAULT,2,2);
insert into table1 values (DEFAULT,'I should not be here');
-- this is a test insert
insert into table3 (table1_id,alt_name) values (1,'bob');
insert into table3 (table1_id,alt_name) values (2,'fred');
insert into table3 (table1_id,alt_name) values
 (3,'sam');
-- end test
	-- a third comment
insert into table3 (table1_id,alt_name) values (1,'faris');
insert into table3 (table1_id,alt_name) values (2,'crocop');
insert into table3 (table1_id,alt_name) values
 (3,'mickey mouse');
commit;