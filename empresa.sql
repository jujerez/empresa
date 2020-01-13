DROP TABLE IF EXISTS departamentos CASCADE;

CREATE TABLE departamentos
(
    id        bigserial    PRIMARY KEY
  , num_dep   numeric(2)   NOT NULL UNIQUE
  , dnombre   varchar(255) NOT NULL
  , localidad varchar(255) CONSTRAINT ck_localidad_no_vacia
                           CHECK (localidad != '')
);

DROP TABLE IF EXISTS usuarios CASCADE;

CREATE TABLE usuarios
(
     id       bigserial    PRIMARY KEY
   , login    varchar(255) NOT NULL UNIQUE
   , password varchar(255) NOT NULL
   , email    varchar(255) CONSTRAINT email_no_vacia
                           CHECK (email != '')
   , rol      varchar(255)
);

DROP TABLE IF EXISTS empleados CASCADE;

CREATE TABLE empleados
(
    id              bigserial    PRIMARY KEY
  , num_emp         numeric(4)   NOT NULL UNIQUE
  , nombre          varchar(255) NOT NULL
  , salario         numeric(6,2)
  , departamento_id bigint       NOT NULL REFERENCES departamentos (id)
                                 ON DELETE NO ACTION ON UPDATE CASCADE
);

DROP VIEW IF EXISTS v_departamentos CASCADE;

CREATE VIEW v_departamentos AS
SELECT d.*, COUNT(e.id) AS cantidad, coalesce(round(avg(e.salario),0),2) AS salario_medio
  FROM departamentos d
  LEFT JOIN empleados e
  ON e.departamento_id = d.id
GROUP BY d.id;


INSERT INTO usuarios (login, password, email, rol)
VALUES ('pepe', crypt('pepe', gen_salt('bf', 12)), 'pepe@pepe.com', 'administrador')
     , ('juan', crypt('juan', gen_salt('bf', 12)), 'pepe@pepe.com', 'editor');

INSERT INTO departamentos (num_dep, dnombre, localidad)
VALUES (10, 'Contabilidad', 'Sanlúcar')
     , (20, 'Facturación', 'Chipiona')
     , (30, 'Ventas', 'Trebujena');

INSERT INTO empleados (num_emp, nombre, salario, departamento_id)
VALUES (5555, 'Pepe', 1400, 1)
     , (6666, 'Juan', 1700, 2)
     , (8888, 'María', 2100, 2);
