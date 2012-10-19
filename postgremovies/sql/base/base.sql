--
-- Table structure for table films
--
DROP TABLE IF EXISTS films;

CREATE TABLE films (
    id integer PRIMARY KEY,
    title varchar(40) NOT NULL
);

DROP TABLE IF EXISTS distributors;

CREATE TABLE distributors (
    id integer PRIMARY KEY,
    name varchar(255) NOT NULL CHECK (name <> '')
);
