DROP SCHEMA IF EXISTS "UniNostra" CASCADE;
CREATE schema "UniNostra";
SET search_path TO "UniNostra";

-- "UniNostra".corsodilaurea definition

-- Drop table

-- DROP TABLE "UniNostra".corsodilaurea;

CREATE TABLE "UniNostra".corsodilaurea (
	codice varchar(10) NOT NULL,
	nome varchar(50) NOT NULL,
	descrizione varchar(200) NULL,
	isattivo bool NOT NULL DEFAULT true,
	durata public."tipocorsolaurea" NOT NULL,
	CONSTRAINT corsodilaurea_codice_check CHECK (((codice)::text <> ''::text)),
	CONSTRAINT corsodilaurea_nome_check CHECK (((nome)::text <> ''::text)),
	CONSTRAINT corsodilaurea_pkey PRIMARY KEY (codice)
);


-- "UniNostra".utente definition

-- Drop table

-- DROP TABLE "UniNostra".utente;

CREATE TABLE "UniNostra".utente (
	idutente serial4 NOT NULL,
	nome varchar(50) NOT NULL,
	cognome varchar(100) NOT NULL,
	email varchar(50) NOT NULL,
	"password" varchar(20) NOT NULL,
	tipo public."tipoutente" NOT NULL,
	cf varchar(16) NOT NULL,
	CONSTRAINT utente_cf_check CHECK (((cf)::text <> ''::text)),
	CONSTRAINT utente_cf_key UNIQUE (cf),
	CONSTRAINT utente_cognome_check CHECK (((cognome)::text <> ''::text)),
	CONSTRAINT utente_email_check CHECK ((((email)::text <> ''::text) AND ((email)::text ~~ '%@%'::text))),
	CONSTRAINT utente_email_key UNIQUE (email),
	CONSTRAINT utente_nome_check CHECK (((nome)::text <> ''::text)),
	CONSTRAINT utente_password_check CHECK (((password)::text <> ''::text)),
	CONSTRAINT utente_pkey PRIMARY KEY (idutente)
);


-- "UniNostra".docente definition

-- Drop table

-- DROP TABLE "UniNostra".docente;

CREATE TABLE "UniNostra".docente (
	idutente int4 NOT NULL,
	indirizzoufficio varchar(100) NOT NULL,
	cellulareinterno varchar(10) NOT NULL,
	CONSTRAINT docente_cellulareinterno_check CHECK (((cellulareinterno)::text <> ''::text)),
	CONSTRAINT docente_indirizzoufficio_check CHECK (((indirizzoufficio)::text <> ''::text)),
	CONSTRAINT docente_pkey PRIMARY KEY (idutente),
	CONSTRAINT docente_idutente_fkey FOREIGN KEY (idutente) REFERENCES "UniNostra".utente(idutente)
);


-- "UniNostra".exinsegnamento definition

-- Drop table

-- DROP TABLE "UniNostra".exinsegnamento;

CREATE TABLE "UniNostra".exinsegnamento (
	codiceinsegnamento int4 NOT NULL,
	iddocente int4 NOT NULL,
	nome varchar(50) NOT NULL,
	cfu int4 NOT NULL,
	annoinizio int4 NOT NULL,
	annofine int4 NOT NULL DEFAULT date_part('year'::text, CURRENT_DATE)::integer,
	CONSTRAINT exinsegnamento_annofine_check CHECK ((annofine > 0)),
	CONSTRAINT exinsegnamento_annoinizio_check CHECK ((annoinizio > 0)),
	CONSTRAINT exinsegnamento_cfu_check CHECK ((cfu > 0)),
	CONSTRAINT exinsegnamento_nome_check CHECK (((nome)::text <> ''::text)),
	CONSTRAINT exinsegnamento_pkey PRIMARY KEY (codiceinsegnamento, iddocente),
	CONSTRAINT exinsegnamento_iddocente_fkey FOREIGN KEY (iddocente) REFERENCES "UniNostra".docente(idutente) ON DELETE CASCADE
);


-- "UniNostra".exstudente definition

-- Drop table

-- DROP TABLE "UniNostra".exstudente;

CREATE TABLE "UniNostra".exstudente (
	matricola int4 NOT NULL,
	nome varchar(100) NOT NULL,
	cognome varchar(100) NOT NULL,
	telefono varchar(20) NOT NULL,
	indirizzoresidenza varchar(100) NOT NULL,
	datanascita date NOT NULL,
	annoiscrizione date NOT NULL,
	incorso bool NULL,
	stato public."tiposatoexstudente" NULL,
	datarimozione date NOT NULL DEFAULT CURRENT_DATE,
	"votolaurea" "UniNostra"."votolaurea" NULL DEFAULT NULL::integer,
	codicecorso varchar(10) NULL,
	idutente int4 NULL,
	CONSTRAINT exstudente_indirizzoresidenza_check CHECK (((indirizzoresidenza)::text <> ''::text)),
	CONSTRAINT exstudente_pkey PRIMARY KEY (matricola),
	CONSTRAINT exstudente_telefono_check CHECK (((telefono)::text <> ''::text)),
	CONSTRAINT exstudente_codicecorso_fkey FOREIGN KEY (codicecorso) REFERENCES "UniNostra".corsodilaurea(codice) ON DELETE CASCADE,
	CONSTRAINT exstudente_idutente_fkey FOREIGN KEY (idutente) REFERENCES "UniNostra".utente(idutente) ON DELETE CASCADE
);


-- "UniNostra".insegnamento definition

-- Drop table

-- DROP TABLE "UniNostra".insegnamento;

CREATE TABLE "UniNostra".insegnamento (
	codice serial4 NOT NULL,
	nome varchar(50) NOT NULL,
	descrizione varchar(200) NULL,
	cfu int4 NOT NULL,
	annoinizio int4 NOT NULL,
	iddocente int4 NOT NULL,
	CONSTRAINT insegnamento_annoinizio_check CHECK ((annoinizio > 0)),
	CONSTRAINT insegnamento_cfu_check CHECK ((cfu > 0)),
	CONSTRAINT insegnamento_nome_check CHECK (((nome)::text <> ''::text)),
	CONSTRAINT insegnamento_pkey PRIMARY KEY (codice),
	CONSTRAINT insegnamento_iddocente_fkey FOREIGN KEY (iddocente) REFERENCES "UniNostra".docente(idutente) ON DELETE CASCADE
);

-- Table Triggers




-- "UniNostra".pianostudi definition

-- Drop table

-- DROP TABLE "UniNostra".pianostudi;

CREATE TABLE "UniNostra".pianostudi (
	codicecorso varchar(10) NOT NULL,
	codiceinsegnamento int4 NOT NULL,
	annoerogazione public."annoesame" NULL,
	CONSTRAINT pianostudi_pkey PRIMARY KEY (codicecorso, codiceinsegnamento),
	CONSTRAINT pianostudi_codicecorso_fkey FOREIGN KEY (codicecorso) REFERENCES "UniNostra".corsodilaurea(codice) ON DELETE CASCADE,
	CONSTRAINT pianostudi_codiceinsegnamento_fkey FOREIGN KEY (codiceinsegnamento) REFERENCES "UniNostra".insegnamento(codice) ON DELETE CASCADE
);

-- Table Triggers




-- "UniNostra".propedeuticita definition

-- Drop table

-- DROP TABLE "UniNostra".propedeuticita;

CREATE TABLE "UniNostra".propedeuticita (
	esame int4 NOT NULL,
	prop int4 NOT NULL,
	codicelaurea varchar(10) NOT NULL,
	CONSTRAINT propedeuticita_pkey PRIMARY KEY (esame, prop, codicelaurea),
	CONSTRAINT propedeuticita_codicelaurea_fkey FOREIGN KEY (codicelaurea) REFERENCES "UniNostra".corsodilaurea(codice) ON DELETE CASCADE,
	CONSTRAINT propedeuticita_esame_fkey FOREIGN KEY (esame) REFERENCES "UniNostra".insegnamento(codice) ON DELETE CASCADE,
	CONSTRAINT propedeuticita_prop_fkey FOREIGN KEY (prop) REFERENCES "UniNostra".insegnamento(codice) ON DELETE CASCADE
);

-- Table Triggers




-- "UniNostra".segretario definition

-- Drop table

-- DROP TABLE "UniNostra".segretario;

CREATE TABLE "UniNostra".segretario (
	idutente int4 NOT NULL,
	indirizzosegreteria varchar(100) NOT NULL,
	nomedipartimento varchar(50) NOT NULL,
	cellulareinterno varchar(10) NOT NULL,
	CONSTRAINT segretario_cellulareinterno_check CHECK (((cellulareinterno)::text <> ''::text)),
	CONSTRAINT segretario_indirizzosegreteria_check CHECK (((indirizzosegreteria)::text <> ''::text)),
	CONSTRAINT segretario_nomedipartimento_check CHECK (((nomedipartimento)::text <> ''::text)),
	CONSTRAINT segretario_pkey PRIMARY KEY (idutente),
	CONSTRAINT segretario_idutente_fkey FOREIGN KEY (idutente) REFERENCES "UniNostra".utente(idutente)
);


-- "UniNostra".studente definition

-- Drop table

-- DROP TABLE "UniNostra".studente;

CREATE TABLE "UniNostra".studente (
	matricola serial4 NOT NULL,
	telefono varchar(20) NOT NULL,
	indirizzoresidenza varchar(100) NOT NULL,
	datanascita date NOT NULL,
	annoiscrizione date NOT NULL DEFAULT CURRENT_DATE,
	incorso bool NOT NULL DEFAULT true,
	idutente int4 NULL,
	idcorso varchar NULL,
	stato public."tiposatoexstudente" NULL,
	votolaur "UniNostra"."votolaurea" NULL DEFAULT NULL::integer,
	CONSTRAINT studente_indirizzoresidenza_check CHECK (((indirizzoresidenza)::text <> ''::text)),
	CONSTRAINT studente_pkey PRIMARY KEY (matricola),
	CONSTRAINT studente_telefono_check CHECK (((telefono)::text <> ''::text)),
	CONSTRAINT studente_idcorso_fkey FOREIGN KEY (idcorso) REFERENCES "UniNostra".corsodilaurea(codice) ON DELETE CASCADE,
	CONSTRAINT studente_idutente_fkey FOREIGN KEY (idutente) REFERENCES "UniNostra".utente(idutente) ON DELETE CASCADE
);

-- Table Triggers




-- "UniNostra".appello definition

-- Drop table

-- DROP TABLE "UniNostra".appello;

CREATE TABLE "UniNostra".appello (
	idappello serial4 NOT NULL,
	codiceinsegnamento int4 NULL,
	aula varchar(20) NOT NULL,
	note varchar(200) NULL,
	dataesame date NOT NULL,
	orainizio time NOT NULL,
	orafine time NOT NULL,
	statoappello public."tipostatoappello" NOT NULL DEFAULT 'aperto'::tipostatoappello,
	cdl varchar(10) NULL,
	CONSTRAINT appello_aula_check CHECK (((aula)::text <> ''::text)),
	CONSTRAINT appello_orafine_check CHECK ((orafine <= '20:00:00'::time without time zone)),
	CONSTRAINT appello_orainizio_check CHECK (((orainizio >= '07:00:00'::time without time zone) AND (orainizio <= '18:00:00'::time without time zone))),
	CONSTRAINT appello_pkey PRIMARY KEY (idappello),
	CONSTRAINT appello_cdl_fkey FOREIGN KEY (cdl) REFERENCES "UniNostra".corsodilaurea(codice) ON DELETE CASCADE,
	CONSTRAINT appello_codiceinsegnamento_fkey FOREIGN KEY (codiceinsegnamento) REFERENCES "UniNostra".insegnamento(codice) ON DELETE CASCADE
);

-- Table Triggers



-- "UniNostra".iscrizioneesame definition

-- Drop table

-- DROP TABLE "UniNostra".iscrizioneesame;

CREATE TABLE "UniNostra".iscrizioneesame (
	matricola int4 NOT NULL,
	id int4 NOT NULL,
	votoesame "UniNostra"."voto" NULL DEFAULT NULL::integer,
	stato public."tipostatovoto" NULL DEFAULT 'Iscritto'::tipostatovoto,
	islode bool NULL,
	CONSTRAINT iscrizioneesame_pkey PRIMARY KEY (matricola, id),
	CONSTRAINT iscrizioneesame_id_fkey FOREIGN KEY (id) REFERENCES "UniNostra".appello(idappello) ON DELETE CASCADE,
	CONSTRAINT iscrizioneesame_matricola_fkey FOREIGN KEY (matricola) REFERENCES "UniNostra".studente(matricola) ON DELETE CASCADE
);

-- Table Triggers



-- "UniNostra".storicovalutazioni definition

-- Drop table

-- DROP TABLE "UniNostra".storicovalutazioni;

CREATE TABLE "UniNostra".storicovalutazioni (
	id serial4 NOT NULL,
	votoesame "UniNostra"."voto" NULL DEFAULT NULL::integer,
	stato public."tipostatovoto" NULL,
	islode bool NULL,
	idappello int4 NULL,
	matricola int4 NULL,
	CONSTRAINT storicovalutazioni_pkey PRIMARY KEY (id),
	CONSTRAINT storicovalutazioni_idappello_fkey FOREIGN KEY (idappello) REFERENCES "UniNostra".appello(idappello) ON DELETE CASCADE,
	CONSTRAINT storicovalutazioni_matricola_fkey FOREIGN KEY (matricola) REFERENCES "UniNostra".exstudente(matricola) ON DELETE CASCADE
);

--Data 

INSERT INTO "UniNostra".appello (codiceinsegnamento,aula,note,dataesame,orainizio,orafine,cdl) VALUES
	 (10,'omega','bho','2023-08-30','11:40:00','13:00:00','FX101'),
	 (4,'sigma','blocca','2023-09-15','18:00:00','19:00:00','FX101'),
	 (11,'lambda','fisica1','2023-09-13','18:00:00','19:50:00','MD101'),
	 (13,'settore didattico','fisica1','2023-09-14','18:00:00','19:50:00','MD101'),
	 (11,'settore didattico','fisica1','2023-05-14','18:00:00','19:50:00','MD101'),
	 (12,'settore didattico','fisica1','2023-05-15','18:00:00','19:50:00','MD101'),
	 (13,'settore didattico','fisica1','2023-05-16','18:00:00','19:50:00','MD101'),
	 (32,'settore didattico','fisica1','2023-05-17','18:00:00','19:50:00','MD101'),
	 (4,'lambda+gamma','appello di algoritmi','2023-09-15','10:00:00','11:00:00','FX101'),
	 (10,'gamma+lambda','bho','2023-09-04','16:00:00','18:00:00','FX101');
INSERT INTO "UniNostra".appello (codiceinsegnamento,aula,note,dataesame,orainizio,orafine,cdl) VALUES
	 (12,'Gamma','Apello di medicina generale','2023-09-22','10:00:00','13:00:00','MD101'),
	 (4,'omega+lambda','Appello Algoritmi','2023-09-16','15:00:00','17:00:00','FX101'),
	 (10,'gamma+lambda','bho','2023-09-05','12:00:00','15:00:00','FX101'),
	 (6,'omega','bho','2023-08-29','10:00:00','11:00:00','FX102'),
	 (10,'omega','bho','2023-08-30','10:00:00','11:00:00','FX102'),
	 (4,'omega','bho','2023-08-31','10:00:00','11:00:00','FX102'),
	 (10,'gamma+lambda','bho','2023-09-05','16:00:00','17:00:00','FX101'),
	 (10,'omega','bho','2023-08-31','10:50:00','15:15:00','FX102'),
	 (6,'gamma+lambda','bho','2023-09-05','08:00:00','09:00:00','FX102'),
	 (10,'gamma+lambda','bho','2023-09-01','08:00:00','11:00:00','FX101');
INSERT INTO "UniNostra".appello (codiceinsegnamento,aula,note,dataesame,orainizio,orafine,cdl) VALUES
	 (4,'gamma+lambda','bho','2023-09-03','12:00:00','15:00:00','FX101'),
	 (4,'omega','bho','2023-09-11','08:00:00','09:00:00','FX101'),
	 (10,'bertone','bho','2023-09-13','10:00:00','13:00:00','FX101'),
	 (4,'omega','bho','2023-09-12','18:00:00','19:50:00','FX101'),
	 (4,'bertoni','bho','2023-09-12','18:00:00','19:50:00','FX101'),
	 (4,'lambda','bho','2023-09-12','18:00:00','19:50:00','FX101'),
	 (10,'lambda','bho','2023-09-13','18:00:00','19:50:00','FX101'),
	 (4,'omega','bho','2023-09-13','18:00:00','19:50:00','FX101'),
	 (10,'omega','bho','2023-09-10','10:50:00','15:15:00','FX101'),
	 (10,'gamma','bho','2023-09-10','10:50:00','15:15:00','FX102');
INSERT INTO "UniNostra".appello (codiceinsegnamento,aula,note,dataesame,orainizio,orafine,cdl) VALUES
	 (6,'omega','Appello prog1 magistrale','2023-09-16','14:00:00','15:00:00','FX102'),
	 (6,'omega','bho','2023-08-04','11:40:00','13:00:00','FX101'),
	 (6,'gamma+lambda','bho','2023-09-03','08:00:00','11:00:00','FX101'),
	 (6,'omega','bho','2023-08-29','10:00:00','11:00:00','FX101'),
	 (6,'gamma+lambda','bho','2023-08-31','13:10:00','15:15:00','FX101'),
	 (6,'omega','bho','2023-09-06','08:00:00','09:00:00','FX101'),
	 (6,'gamma+lambda','bho','2023-09-09','13:10:00','15:15:00','FX101'),
	 (6,'bertone','bho','2023-09-11','10:00:00','13:00:00','FX101'),
	 (6,'omega','bho','2023-09-12','18:00:00','19:50:00','FX101'),
	 (6,'lambda','bho','2023-09-12','18:00:00','19:50:00','FX101');
INSERT INTO "UniNostra".appello (codiceinsegnamento,aula,note,dataesame,orainizio,orafine,cdl) VALUES
	 (6,'omega','Apello prog1','2023-09-16','10:00:00','12:00:00','FX101'),
	 (6,'omega','bho','2023-09-09','18:00:00','19:50:00','FX101');
INSERT INTO "UniNostra".corsodilaurea (codice,nome,descrizione,isattivo) VALUES
	 ('MD102','Medicina','Magistrale in medicina',false),
	 ('FX101','Informatica','Corso di lauerea triennale in informatica',true),
	 ('CO202','chimica organica','chimica organica',false),
	 ('MD101','Medicina','tirnnale di medicina',true),
	 ('FX102','Informatica','Corso di lauerea Magistrale in informatica',true);
INSERT INTO "UniNostra".docente (idutente,indirizzoufficio,cellulareinterno) VALUES
	 (9,'via celoria 18','3331888772'),
	 (24,'via celoria 18','3331888772'),
	 (1,'Via celoria 18 piano 8','393020220'),
	 (4,'Via celoria  ','393020220'),
	 (41,'Celoria 18','2838383');
INSERT INTO "UniNostra".exinsegnamento (codiceinsegnamento,iddocente,nome,cfu,annoinizio,annofine) VALUES
	 (6,1,'Programmazione 1',12,2023,2023),
	 (10,4,'Programmazione 3',12,2023,2023),
	 (7,1,'Programmazione 2',12,2023,2023),
	 (13,24,'Fisica 1',12,2023,2023),
	 (32,24,'Fisica 2',6,2023,2023);
INSERT INTO "UniNostra".exstudente (matricola,nome,cognome,telefono,indirizzoresidenza,datanascita,annoiscrizione,incorso,datarimozione,"votolaurea",codicecorso,idutente) VALUES
	 (2,'Giacomo','Comitani','3930582002','vimodrone mi','2002-11-03','2023-09-05',true,'2023-09-10',108,'FX102',14),
	 (4,'Giacomo','Comitani','3930582002','vimodrone mi','2002-11-03','2023-09-12',true,'2023-09-14',104,'FX101',14),
	 (13,'Mattia','DelleDonne','3838383','Milano, Segrate','2023-09-19','2023-09-19',true,'2023-09-19',NULL,'MD101',28),
	 (14,'Mattia','DelleDonne','272727','Busto','2023-09-19','2023-09-19',true,'2023-09-19',NULL,'MD101',28),
	 (15,'Mattia','DelleDonne','9999999','Milano,segrate','2023-09-19','2023-09-19',true,'2023-09-19',NULL,'MD101',28),
	 (16,'Mattia','DelleDonne','337373','Milano,segrate','2023-09-19','2023-09-19',true,'2023-09-19',NULL,'MD101',28),
	 (17,'Mattia','DelleDonne','272727','Segrate, Varese','1999-02-18','2023-09-19',true,'2023-09-19',60,'MD101',28),
	 (12,'Giacomo','Comitani','0582002','Vimodrone, Provincia di Milano','2023-09-19','2023-09-19',true,'2023-09-20',NULL,'MD101',14),
	 (23,'francesco','capuzzo','3481749850','Busto Arsizio','2000-06-06','2023-09-21',true,'2023-09-21',NULL,'FX101',46);
INSERT INTO "UniNostra".insegnamento (nome,descrizione,cfu,annoinizio,iddocente) VALUES
	 ('Programmazione 1','pighizzini',12,2023,4),
	 ('Programmazione 3','non esiste',12,2023,1),
	 ('Programmazione 2','pighizzini',12,2023,4),
	 ('Medicina Generale','si studiano cose',12,2023,24),
	 ('Anatomia','Si studia il corpo umano',18,2023,9),
	 ('Fisica 1','primo approccio alla fisica',12,2023,9),
	 ('Fisica 2','Esame di fisica 2',6,2023,9),
	 ('Algoritmi e strutture dati','Esame che riguarda pighizzini',12,2023,1),
	 ('anatomia','anatomia',9,2023,1);
INSERT INTO "UniNostra".iscrizioneesame (matricola,id,votoesame,islode) VALUES
	 (1,13,27,false),
	 (1,11,20,NULL),
	 (1,53,NULL,NULL),
	 (11,11,30,true),
	 (11,53,23,false),
	 (1,22,30,true),
	 (1,45,NULL,false),
	 (11,22,29,false),
	 (18,92,26,false),
	 (18,93,26,false);
INSERT INTO "UniNostra".iscrizioneesame (matricola,id,votoesame,islode) VALUES
	 (18,94,26,false),
	 (18,95,26,false),
	 (19,96,NULL,NULL),
	 (11,85,30,true);
INSERT INTO "UniNostra".pianostudi (codicecorso,codiceinsegnamento) VALUES
	 ('FX101',6),
	 ('FX101',10),
	 ('FX102',6),
	 ('FX102',10),
	 ('FX101',4),
	 ('FX102',4),
	 ('MD101',11),
	 ('MD101',12),
	 ('MD101',13),
	 ('MD101',32);
INSERT INTO "UniNostra".pianostudi (codicecorso,codiceinsegnamento) VALUES
	 ('FX102',7);
INSERT INTO "UniNostra".propedeuticita (esame,prop,codicelaurea) VALUES
	 (10,4,'FX102'),
	 (6,10,'FX102'),
	 (32,13,'MD101'),
	 (4,6,'FX101');
INSERT INTO "UniNostra".segretario (idutente,indirizzosegreteria,nomedipartimento,cellulareinterno) VALUES
	 (13,'Via celoria 8','Informatica','1234567891'),
	 (42,'Via celoria 18','  Fisica','328229');
INSERT INTO "UniNostra".storicovalutazioni (votoesame,islode,idappello,matricola) VALUES
	 (27,false,40,2),
	 (28,false,41,2),
	 (29,false,42,2),
	 (NULL,NULL,46,4),
	 (30,true,48,4),
	 (23,false,52,4),
	 (30,true,50,4),
	 (18,false,90,13),
	 (18,false,91,13),
	 (18,false,92,17);
INSERT INTO "UniNostra".storicovalutazioni (votoesame,islode,idappello,matricola) VALUES
	 (18,false,93,17),
	 (18,false,94,17),
	 (18,false,95,17);
INSERT INTO "UniNostra".studente (telefono,indirizzoresidenza,datanascita,annoiscrizione,incorso,idutente,idcorso,votolaur) VALUES
	 ('3930582002','gallarabia mi','2002-11-03','2023-09-15',true,26,'FX101',NULL),
	 ('39305820','Olgiate Olona, Varese','2009-07-15','2023-09-19',true,29,'MD101',NULL),
	 ('3930582002','Vimodrone, Milano','2023-09-20','2023-09-20',true,14,'MD101',NULL),
	 ('222222','Busto Arsizio Va','2002-08-05','2019-05-08',false,11,'FX101',NULL),
	 ('3481749850','Busto Arsizio','2000-06-06','2023-09-21',true,46,'FX101',NULL);
INSERT INTO "UniNostra".utente (nome,cognome,email,"password",cf) VALUES
	 ('Luca','Corradini','luca.corradini@studenti.UniNostra','81dc9bdb52d04dc20036','cf3'),
	 ('Giacomo','Comitani','giacomo.comitani@studenti.UniNostra','81dc9bdb52d04dc20036','cf5'),
	 ('francesco','capuzzo','francesco.capuzzo@studenti.UniNostra','81dc9bdb52d04dc20036','CPZFRN'),
	 ('Fabio','Scotti','fabio.scotti@docenti.UniNostra','81dc9bdb52d04dc20036','cf1'),
	 ('Violetta','Lonati','ViolettaLonati@docenti.UniNostra','81dc9bdb52d04dc20036','cf6'),
	 ('Andrea','Galliano','andrea.galliano@studenti.UniNostra','81dc9bdb52d04dc20036','cf7'),
	 ('Luigi','Pepe','LuigiPepe@segreteria.UniNostra','81dc9bdb52d04dc20036','cf4'),
	 ('Mattia','DelleDonne','mattia.delledonne@studenti.UniNostra','81dc9bdb52d04dc20036','cf10'),
	 ('Mario','Rossi','mario.rossi@studenti.UniNostra','81dc9bdb52d04dc20036','cfu12'),
	 ('Massimo','Santini','massimo.santini@docenti.UniNostra','81dc9bdb52d04dc20036','cf20');
INSERT INTO "UniNostra".utente (nome,cognome,email,"password",cf) VALUES
	 ('Vincenzo','Piuri','Vincenzo.Piuri@docenti.UniNostra','1234','cf2'),
	 ('Francesco','Rossi','francesco.rossi@segreteria.UniNostra','81dc9bdb52d04dc20036','cf2929'),
	 ('Giovanni','Pighizzini','giovanni.pighizzini@docenti.UniNostra','81dc9bdb52d04dc20036','crllcr');

--Trigger

CREATE OR REPLACE FUNCTION "UniNostra".storicoInsegnamentiDocenti()
	RETURNS TRIGGER AS $$
	DECLARE 
    	countDocenze integer;
	BEGIN 	
		 IF NEW.IdDocente = OLD.IdDocente then --non sto modificando la docenza
        	RETURN NEW;
    	ELSE
        	perform * from "UniNostra".ExInsegnamento ei where new.idDocente = ei.idDocente and new.codice = ei.codiceinsegnamento ;
        	if found then 
        		raise exception 'il docente ha già detenuto il corso in passato, non li può essere riasseganto';
    		end if; 
    		
    		insert into "UniNostra".exinsegnamento (codiceInsegnamento, idDocente, nome, cfu, annoInizio, annoFine)
    		values (old.codice,old.idDocente,old.nome,old.cfu,old.annoInizio,cast(date_part('year', CURRENT_DATE) as integer));
    	
        	RETURN NEW;
       END IF ; 
	END;	
	$$ LANGUAGE plpgsql;

	CREATE OR REPLACE TRIGGER storicoInsegnamento BEFORE update on "UniNostra".insegnamento 
	FOR EACH ROW EXECUTE FUNCTION "UniNostra".storicoInsegnamentiDocenti();

	
	CREATE OR REPLACE FUNCTION "UniNostra".countDocentiResponsabili()
	RETURNS TRIGGER AS $$
	DECLARE 
    	countDocenze integer;
	BEGIN 	
		 IF NEW.IdDocente = OLD.IdDocente THEN
        	RETURN NEW;
    	ELSE
        	SELECT count(*) INTO countDocenze 
        	FROM "UniNostra".insegnamento i
        	WHERE i.idDocente = NEW.idDocente;
       
       		IF countDocenze = 3 THEN 
            	RAISE EXCEPTION 'il docente è gia responsabile di 3 corsi !';
        	END IF;
       
        	RETURN NEW;
       END IF ; 
	END;	
	$$ LANGUAGE plpgsql;

	CREATE OR REPLACE TRIGGER countDocenze BEFORE update or INSERT on "UniNostra".insegnamento 
	FOR EACH ROW EXECUTE FUNCTION "UniNostra".countDocentiResponsabili();

create or replace function "UniNostra".attivaDisattivaCdl()
	returns trigger as $$ 
	declare 
		numInsegnamenti integer ;
	begin 
		if old.codiceCorso is null then --stiamo inserendo 
			update "UniNostra".corsodilaurea 
			set isAttivo = true 
			where codice = new.codiceCorso;		
			return new; 
		end if;
		
		select count(*) into numInsegnamenti 
		from "UniNostra".pianostudi p 
		where p.codiceCorso = old.codiceCorso ;
		--RAISE NOTICE 'Value: %', numInsegnamenti;
		
		if (numInsegnamenti-'1') < '1' then
			update "UniNostra".corsodilaurea 
			set isAttivo = false  
			where codice = old.codiceCorso;
	
		end if;
		return old;
	end;
	$$ LANGUAGE plpgsql;

	CREATE OR REPLACE TRIGGER attivaDisattivaCdl BEFORE insert or delete  on "UniNostra".pianostudi  
	FOR EACH ROW EXECUTE FUNCTION "UniNostra".attivaDisattivaCdl();

	create or replace function "UniNostra".elliminaProp()
	returns trigger as $$ 
	declare 
	begin 
		delete from "UniNostra".propedeuticita p where p.codicelaurea = old.codicecorso and (p.esame=old.codiceinsegnamento or p.prop =old.codiceinsegnamento );	
		return new;
	end;
	$$ LANGUAGE plpgsql;


	CREATE OR REPLACE TRIGGER elliminaPiano after delete  on "UniNostra".pianostudi 
	FOR EACH ROW EXECUTE FUNCTION elliminaProp();

create or replace function "UniNostra".controllaProp()
	returns trigger as $$
	declare 
		lv integer;
		c1 integer;
		c2 integer;
		co varchar(10);
	begin 
		perform * from "UniNostra".propedeuticita p where new.prop = p.esame and new.esame = p.prop and new.codicelaurea = p.codicelaurea;
		if found then 
			raise exception 'impossibile inserire la propedeucita % -> % causerebbe un ciclo', new.esame ,new.prop ;
		end if;
	
		with recursive sos (LEVEL, corso1, corso2, codicelaurea) as (
			select 1, p.esame as corso1, p.prop as corso2 , p.codicelaurea  from "UniNostra".propedeuticita p 
		    where p.esame = new.esame and p.codicelaurea = new.codicelaurea
		    union
		    select o.LEVEL + 1, next_p.esame, next_p .prop  , next_p.codicelaurea 
		    from "UniNostra".propedeuticita as next_p, sos as o
		    where o.corso2 = next_p.esame  and o.codicelaurea = next_p.codicelaurea and o.corso2 <> new.esame
		)
		select LEVEL, corso1,corso2, codicelaurea into lv,c1,c2,co from sos order by level desc limit 1;
		--RAISE NOTICE 'Value22: % % %',new, c1, c2;
		--raise notice '%',c2 = new.esame;
		if c2 = new.esame then 
			raise exception 'Non è possibile inserire la propredeuticità, altrimenti causerebbe un ciclo';
		end if;
		return new;
	end;
	$$ language plpgsql;

	CREATE OR REPLACE TRIGGER controllaPropedeuticita after insert on "UniNostra".propedeuticita 
	FOR EACH ROW EXECUTE FUNCTION "UniNostra".controllaProp();


CREATE OR REPLACE FUNCTION "UniNostra".storicoStudente()
	RETURNS TRIGGER AS $$
	DECLARE 
    	iscrizioneApp "UniNostra".iscrizioneesame%rowtype;
    	oldUtente "UniNostra".utente%rowtype;
	BEGIN 
		
		select u.idutente ,u.nome ,u.cognome ,u.email ,u."password" , u.tipo ,u.cf  into oldUtente from "UniNostra".studente s inner join "UniNostra".utente u on s.idutente = u.idutente where s.matricola = old.matricola ;
		
		insert into "UniNostra".exstudente(matricola,nome,cognome,telefono,indirizzoResidenza,dataNascita,annoIscrizione,incorso,stato,datarimozione,"votolaurea",codicecorso,idutente)
		values(old.matricola,oldUtente.nome,oldUtente.cognome,old.telefono,old.indirizzoResidenza,old.dataNascita,old.annoIscrizione,old.incorso,old.stato,CURRENT_DATE,old.votoLaur,old.idCorso,old.idUtente);
		
		for iscrizioneApp in select * from "UniNostra".iscrizioneesame i where i.matricola = old.matricola
			loop 
				insert into "UniNostra".storicovalutazioni (votoesame,stato,idAppello,matricola,isLode)
				values(iscrizioneApp.votoEsame,iscrizioneApp.stato,iscrizioneApp.id,iscrizioneApp.matricola,iscrizioneApp.isLode);
				
			end loop;
			delete from "UniNostra".iscrizioneesame i where i.matricola = old.matricola;
	
        	RETURN OLD;
	END;	
	$$ LANGUAGE plpgsql;

	CREATE OR REPLACE TRIGGER storicoStud before delete on "UniNostra".studente 
	FOR EACH ROW EXECUTE function "UniNostra".storicoStudente();


create or replace function "UniNostra".controllaAppelli()
	returns trigger as $$
	declare 
		anno "UniNostra".pianostudi%rowtype;
		annoNuovo annoEsame;
	begin 
		
		select p.annoerogazione into annoNuovo from "UniNostra".pianostudi p 
		where p.codiceinsegnamento = new.codiceInsegnamento and p.codicecorso = new.cdl;
		
		for anno in select p.codicecorso ,p.codiceinsegnamento ,p.annoerogazione from "UniNostra".appello a inner join "UniNostra".pianostudi p on a.codiceinsegnamento = p.codiceinsegnamento and a.cdl = p.codicecorso where a.dataesame = new.dataesame
			loop 
				if anno.codicecorso = new.cdl and anno.annoerogazione = annoNuovo and anno.codiceinsegnamento <> new.codiceInsegnamento  then 
					raise exception 'Esiste già un appello erogata in data %, per l^ insegnamento % del cdl % erogato nello stesso anno %',new.dataEsame,anno.codiceinsegnamento,anno.codicecorso,anno.annoerogazione;
				end if;
			end loop;
		return new;
	end;
	$$ language plpgsql;

	CREATE OR REPLACE TRIGGER controllaAppelli before insert on "UniNostra".appello 
	FOR EACH ROW EXECUTE FUNCTION "UniNostra".controllaAppelli();


create or replace function "UniNostra".controllaTurni()
	returns trigger as $$
	declare
		cod "UniNostra".appello.cdl%type;
		dataApp "UniNostra".appello.dataesame%type;
	begin 
		select a.dataesame , a.cdl into dataApp,cod from "UniNostra".appello a where a.idappello = new.id;
		
		perform * from "UniNostra".iscrizioneEsame i inner join "UniNostra".appello a on a.idappello = i.id 
		where i.matricola = new.matricola and i.id <> new.id and a.dataesame = dataApp and a.cdl = cod;
		if found then 
			raise exception 'lo studente è già iscritto ad un altro turno nella stessa giornata';
		end if;
		return new;
	end
	$$ language plpgsql;
	
	CREATE OR REPLACE TRIGGER controllaTurniE BEFORE insert on "UniNostra".iscrizioneesame  
	FOR EACH ROW EXECUTE FUNCTION "UniNostra".controllaTurni();

create or replace function "UniNostra".controlloAppello()
	returns trigger as $$ 
	declare 
		tmpAppello "UniNostra".appello%rowtype; 
		cdlS "UniNostra".studente.idCorso%type;
		tmp "UniNostra".propedeuticita%rowtype;
		h1 integer ;
		h2 integer;
	begin 
		call "UniNostra".aggiornaStatoAppello(new.id);
		select * into tmpAppello from "UniNostra".appello a where a.idappello = new.id;
		
		select s.idcorso into cdlS from "UniNostra".studente s where s.matricola = new.matricola;
		if tmpAppello.cdl <> cdlS then 
			raise exception 'lo studente % del cdl % non si può iscrivere all^appello %, in quanto riguarda il cdl %',new.matricola,cdlS,tmpAppello.idAppello,tmpAppello.cdl;	
		end if;
	
		if tmpAppello.statoAppello = 'chiuso' then 
			raise exception 'appello % risulta chiuso',new.id;
		end if;
		
		select extract into h1( hour from tmpAppello.oraInizio) as hour ;
		select extract into h2( hour from now()) as hour;
		if current_date = tmpAppello.dataEsame and h1-h2 <= 1 then 
			raise exception 'lo studente non può iscriversi all^esame, manca meno di un ora all^inizio';
		end if;
		
		for tmp in select * from "UniNostra".propedeuticita p where p.codicelaurea = cdlS and p.esame = tmpAppello.codiceInsegnamento
			loop
				perform * from "UniNostra".iscrizioneesame i inner join "UniNostra".appello a on i.id = a.idappello 
				where i.matricola = new.matricola and a.codiceinsegnamento = tmp.prop and i.stato = 'Accettato';
				if not found then 
					raise exception 'lo studente % non si può iscrivere, in quanto non ha dato l^esame propedeutico %',new.matricola,tmp.prop;
				end if;
			end loop;
		return new;
	end;
	$$ LANGUAGE plpgsql;

	CREATE OR REPLACE TRIGGER controllaIscrizioniAppelli BEFORE insert on "UniNostra".iscrizioneesame  
	FOR EACH ROW EXECUTE FUNCTION "UniNostra".controlloAppello();

	create or replace function "UniNostra".controllaPassatoVoti()
	returns trigger as $$
	declare
		votoE "UniNostra".iscrizioneesame.votoesame%type;
		dataOld "UniNostra".appello.dataesame%type;
		cdlAppello "UniNostra".appello.cdl%type;
		idIns "UniNostra".appello.codiceinsegnamento%type;
		
	begin 
		select a.cdl ,a.codiceinsegnamento into cdlAppello,idIns from "UniNostra".appello a where a.idappello = new.id;
		perform * from "UniNostra".iscrizioneesame i inner join "UniNostra".appello a on i.id = a.idappello 
		where i.matricola = new.matricola and a.codiceinsegnamento = idIns and a.cdl = cdlAppello and i.stato = 'In attesa';
		if found then 
			select i.votoesame ,a.dataesame into votoE,dataOld from "UniNostra".iscrizioneesame i inner join "UniNostra".appello a on i.id = a.idappello 
			where i.matricola = new.matricola and a.cdl = cdlAppello and a.codiceinsegnamento = idIns and i.stato = 'In attesa';
			raise exception 'Lo studente ha un voto pendente per l^insegnamento %, sostenuto in data % con voto %',idIns,dataOld,votoE;
		end if;
	
		perform * from "UniNostra".iscrizioneesame i inner join "UniNostra".appello a on i.id = a.idappello 
		where i.matricola = new.matricola and a.cdl = cdlAppello and a.codiceinsegnamento = idIns and i.stato = 'Accettato';
		if found then 
			select i.votoesame ,a.dataesame into votoE,dataOld from "UniNostra".iscrizioneesame i inner join "UniNostra".appello a on i.id = a.idappello 
			where i.matricola = new.matricola and a.cdl = cdlAppello and a.codiceinsegnamento = idIns and i.stato = 'Accettato';
			raise exception 'lo studente ha già passato l^esame per l^insegnamento % in data % con voto %',idIns,dataOld,votoE;
		end if;
		return new;
	end
	$$ language plpgsql;
	
	CREATE OR REPLACE TRIGGER controllaVecchieIscrizioni BEFORE insert on "UniNostra".iscrizioneesame  
	FOR EACH ROW EXECUTE FUNCTION "UniNostra".controllaPassatoVoti();

	create or replace function "UniNostra".disiscriviStudente()
	returns trigger as $$
	declare
		codIns "UniNostra".appello.codiceinsegnamento %type;
		idApp "UniNostra".iscrizioneesame.id%type;
	begin 
		select a.codiceinsegnamento into codIns from "UniNostra".appello a where a.idappello = new.id;	
	
		if old.stato = 'In attesa' and new.stato = 'Accettato' then 
			for idApp in select i.id from "UniNostra".iscrizioneesame i inner join "UniNostra".appello a on i.id = a.idappello 
			where i.id <> new.id and a.codiceinsegnamento = codIns and i.matricola = new.matricola and (i.stato = 'Iscritto' or i.stato = 'In attesa')
				loop 
					delete from "UniNostra".iscrizioneesame i where i.matricola = new.matricola and i.id = idApp;
				end loop ;
		end if; 
		return new;
	end 
	$$ language plpgsql;
	--
	
	CREATE OR REPLACE TRIGGER elliminaIscrizioniPassate after update on "UniNostra".iscrizioneesame  
	FOR EACH ROW EXECUTE FUNCTION "UniNostra".disiscriviStudente();
