# drop database FIDELIZA;
CREATE DATABASE IF NOT EXISTS FIDELIZA CHARACTER SET 'UTF8' COLLATE 'UTF8_SPANISH_CI';
USE FIDELIZA;

CREATE TABLE clientes (
  clienteid smallint NOT NULL auto_increment,
  cnombre varchar(50) NULL,
  capellido varchar(50) NULL,
  cclave varchar(50) NOT NULL,
  cemail varchar(100) NOT NULL UNIQUE,
  PRIMARY KEY (clienteid)
) engine=innodb;

INSERT INTO clientes VALUES (NULL,'Marcos','Magaña','1111','marcos@jardineria.es'); # 1
INSERT INTO clientes VALUES (NULL,'Ruben','López','2222','rlopez@jardineria.es');   # 2
INSERT INTO clientes VALUES (NULL,'Alberto','Soria','3333','asoria@jardineria.es'); # 3
INSERT INTO clientes VALUES (NULL,'Maria','Solís','4444','msolis@jardineria.es');   # 4
INSERT INTO clientes VALUES (NULL,'Felipe','Rosas','5555','frosas@jardineria.es');  # 5

create table premios (
premioid tinyint NOT NULL auto_increment,
ddescrip varchar(100) not null,
fechai_validez date null,
fechaf_validez date null,
PRIMARY KEY (premioid)
) engine=innodb;

INSERT INTO premios VALUES (NULL,'Descuento 5€ por compras superiores a 60€',null,null); # 1
INSERT INTO premios VALUES (NULL,'Descuento 10€ por compras superiores a 100€',null,null);   # 2
INSERT INTO premios VALUES (NULL,'25% descuento en quesos nacionales','2024/01/01','2024/02/28'); # 3
INSERT INTO premios VALUES (NULL,'25% descuento en bebidas alcohólicas excepto cervezas','2024/03/01','2024/04/01');   # 4
INSERT INTO premios VALUES (NULL,'Regalo rollo de cocina','2024/02/09','2024/02/11');  # 5
INSERT INTO premios VALUES (NULL,'25% descuento en cervezas nacionales','2024/03/01','2024/04/01');  # 6
INSERT INTO premios VALUES (NULL,'25% descuento en sardinillas calvo','2024/03/01','2024/04/01');  # 7


create table cupones (
clienteid smallint not null,
premioid tinyint NOT NULL,
fechai_validez date not null,
fechaf_validez date null,
PRIMARY KEY (premioid,clienteid,fechai_validez),
constraint fk_cuc foreign key (clienteid) references clientes(clienteid) on delete cascade on update cascade,
constraint fk_cup foreign key (premioid) references premios(premioid) on delete cascade on update cascade
) engine=innodb;

INSERT INTO cupones VALUES (1,3,'2023/02/02','2023/02/09');
INSERT INTO cupones VALUES (1,1,'2024/02/02','2024/02/09');
INSERT INTO cupones VALUES (1,5,'2024/02/09','2024/02/011');
INSERT INTO cupones VALUES (2,1,'2024/02/02','2024/02/09');
INSERT INTO cupones VALUES (3,1,'2024/02/02','2024/02/09');

CREATE TABLE articulos(
articuloid smallint not null auto_increment,
anombre VARCHAR(20)NOT NULL,
amarca	varchar(100) NOT NULL,
precio decimal(5,2),
PRIMARY KEY (articuloid)
)ENGINE=INNODB;

INSERT INTO ARTICULOS VALUES (null,'Macarrones','Gallo',1);  	#1 
INSERT INTO ARTICULOS VALUES (null,'Tallarines','Gallo',1.5); 	#2
INSERT INTO ARTICULOS VALUES (null,'Fideos','Carrefour',1);		#3
INSERT INTO ARTICULOS VALUES (null,'Espaguetis','Carrefour',1);	#4
INSERT INTO ARTICULOS VALUES (null,'Atun','Calvo',3);			#5
INSERT INTO ARTICULOS VALUES (null,'Pan','Bimbo',2.5);			#6
INSERT INTO ARTICULOS VALUES (null,'Galletas','Gullón',3);		#7
INSERT INTO ARTICULOS VALUES (null,'Galletas Cuadradas','Cuétara',1);	#8
INSERT INTO ARTICULOS VALUES (null,'Sardinillas','Calvo',2);	#9
INSERT INTO ARTICULOS VALUES (null,'Galletas','Carrefour',2);	#10
INSERT INTO ARTICULOS VALUES (null,'Atun','Isabel',1.5);		#11
INSERT INTO ARTICULOS VALUES (null,'Mejillones','Isabel',1);	#12
INSERT INTO ARTICULOS VALUES (null,'Mejillones','Calvo', 1);	#13
INSERT INTO ARTICULOS VALUES (null,'Leche desnatada','Pascual',1.25);	#14
INSERT INTO ARTICULOS VALUES (null,'Leche semidesnatada','Pascual',1.25);	#15
INSERT INTO ARTICULOS VALUES (null,'Leche entera','Pascual',1);			#16
INSERT INTO ARTICULOS VALUES (null,'Mantequilla','Carrefour',3);		#17	

create table compras (
 compraid smallint not null auto_increment,
 importe decimal(6,2) not null,
 fecha_compra date,
 clienteid smallint,
 PRIMARY KEY (compraid),
 constraint fk_cc foreign key (clienteid) references clientes(clienteid) on delete cascade on update cascade
)engine innodb;

INSERT INTO compras VALUES (NULL,25,'2024/01/01',1);  #1
INSERT INTO compras VALUES (NULL,45,'2023/12/01',2);  #2
INSERT INTO compras VALUES (NULL,55,'2023/12/15',3);  #3
INSERT INTO compras VALUES (NULL,125,'2024/02/02',1); #4


create table itemcompras (
 compraid smallint not null,
 articuloid smallint not null,
 unidades tinyint not null,
 PRIMARY KEY (compraid,articuloid),
 constraint fk_ic foreign key (compraid) references compras(compraid) on delete cascade on update cascade,
 constraint fk_ia foreign key (articuloid) references articulos(articuloid) on delete cascade on update cascade
)engine innodb;

INSERT INTO itemcompras VALUES (1,8,3); # Galletas cuadradas
INSERT INTO itemcompras VALUES (1,1,3);  # Macarrones gallo
INSERT INTO itemcompras VALUES (1,2,3);	# Tallarines gallo 
INSERT INTO itemcompras VALUES (4,3,3); # Fideos carrefour
INSERT INTO itemcompras VALUES (4,8,3); # Galletas cuadradas

INSERT INTO itemcompras VALUES (2,8,1); # Galletas cuadradas
INSERT INTO itemcompras VALUES (2,5,1);  # Atún calvo
INSERT INTO itemcompras VALUES (3,6,1);	# Pan bimbo 
INSERT INTO itemcompras VALUES (3,7,1); # Galletas gullón
INSERT INTO itemcompras VALUES (3,9,3); # Sardinillas calvo

