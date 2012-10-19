
CREATE TABLE actors (
    id integer PRIMARY KEY,
    name varchar(255) NOT NULL CHECK (name <> ''),
    surname varchar(255) NOT NULL CHECK (name <> '')

);
