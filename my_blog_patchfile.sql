start transaction;

DELIMITER $$ 
DROP TRIGGER IF EXISTS after_insert_myfoo$$

CREATE TRIGGER after_insert_myfoo  
    AFTER INSERT ON `myfoo` FOR EACH ROW  
    BEGIN  
        INSERT INTO `myfoo_history` (id, name, lastupdated)  
        VALUES (NEW.id, NEW.name, CURRENT_TIMESTAMP);  
    END$$
DELIMITER ;
create database if not exists foo_store;
GRANT ALL  ON foo_store.* to nonadmin@localhost;
commit;