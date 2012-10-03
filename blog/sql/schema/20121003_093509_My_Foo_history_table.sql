create table myfoo_history (
	id int not null auto_increment primary key,
	name varchar(255),
        lastupdated timestamp ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);