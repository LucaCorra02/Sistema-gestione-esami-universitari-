DROP SCHEMA IF EXISTS "UniNostra" CASCADE;
CREATE schema "UniNostra";
SET search_path TO "UniNostra";


--Creazioen tipo utente, ("Studente","Docente","Segretario")
CREATE TYPE tipoUtente AS ENUM ('Studente', 'Docente', 'Segretario'); --aggiungere ex studente ?

--Tipo durata del corso di laurea, 3 anni per la triennale e 5 per la magistrale
create type tipoCorsoLaurea as enum ('3','5');

--Possibili stati di "accetazione" di un voto di un esame
create type tipoStatoVoto as enum ('Ritirato','Accettato','Rifiutato','Bocciato','Assente','Iscritto','In attesa');

--possibili sati di un exStudente 
create type tipoSatoExStudente as enum ('Laureato','Ritirato');

--possibili voti esame 
CREATE domain "UniNostra".voto as int
CHECK (
    VALUE>=0 AND VALUE<=30
);

--possibili voti laurea
CREATE domain "UniNostra".votoLaurea as int
CHECK (
    VALUE>=60 AND VALUE<=110
);

--tipo anno erogazione di un esame 1 = primo anno, 2 = secondo anno, 3 = terzo anno sia per triennale che per magistrale
create type annoEsame as enum ('1','2','3');

--tipo stato appello, esso potra essere aperto per le iscrizioni, fino ad un ora prima dell'esame oppure chiuso. 
create type tipostatoAppello as enum('chiuso','aperto');

ALTER database "postgres"
SET TIMEZONE TO 'Europe/Berlin';

--Creazione tabella Utenti 
create table "UniNostra".Utente (
	idUtente SERIAL PRIMARY KEY, 
	nome varchar(50) not null check(nome<>''),
	cognome varchar(100) not null check(cognome<>''),
	email varchar(50) not null unique check(email<>'' and email like '%@%'),
	password varchar(32) not null check (password<>''), --droppare lo schema per aggiornare la psw 
	tipo tipoUtente not null,
	cf varchar(16) not null unique check(cf<>'')
);

--Creazione tabella segreteria
create table "UniNostra".Segretario(
	idUtente integer PRIMARY key REFERENCES "UniNostra".Utente(idUtente),
	indirizzoSegreteria varchar(100) not null check (indirizzoSegreteria<>''), 
	nomeDipartimento varchar(50)not null check(nomeDipartimento<>''),
	cellulareInterno varchar(10) not null check(cellulareInterno<>'')
);

--Creazione del Docente 
create table "UniNostra".Docente(
	idUtente integer PRIMARY key REFERENCES "UniNostra".Utente(idUtente),
	indirizzoUfficio varchar(100) not null check (indirizzoUfficio<>''), 
	cellulareInterno varchar(10) not null check(cellulareInterno<>'')
);

--Creazione dell'insegnamento 
create table "UniNostra".Insegnamento(
	codice serial primary key, 
	nome varchar(50) not null check(nome<>''),
	descrizione varchar(200),
	cfu integer not null check(cfu > 0),
	annoInizio integer not null check (annoInizio > 0),
	idDocente integer not null REFERENCES "UniNostra".Docente(idUtente)  ON DELETE cascade
);

--Storico insegnamenti 
create table "UniNostra".ExInsegnamento ( 
	codiceInsegnamento integer, 
	idDocente integer references "UniNostra".Docente(idUtente) on delete cascade, 
	nome varchar(50) not null check(nome<>''),
	cfu integer not null check(cfu > 0),
	annoInizio integer not null check (annoInizio > 0),
	annoFine integer not null default cast(date_part('year', CURRENT_DATE) as integer) check (annoFine > 0),
	primary key(codiceInsegnamento, idDocente)
);

--Creazione delle propredeuticità
create table "UniNostra".Propedeuticita (
	esame integer references "UniNostra".Insegnamento(codice) on delete cascade,
	prop integer references "UniNostra".Insegnamento(codice) on delete cascade,
	codiceLaurea varchar(10) references "UniNostra".CorsoDiLaurea(codice) on delete cascade,
	primary key (esame,prop,codiceLaurea)
);

--Creazione dell'appello
create table "UniNostra".Appello (
	idAppello serial primary key, 
	codiceInsegnamento integer references "UniNostra".Insegnamento(codice) on delete cascade,
	--responsabile integer references "UniNostra".Docente(idUtente)  on delete cascade,
	aula varchar(20) not null check(aula<>''),
	note varchar(200), 
	dataEsame DATE not null, 
	oraInizio TIME not null check (oraInizio >= '07:00:00' and oraInizio <= '18:00:00'),
	oraFine TIME not null check (oraFine <= '20:00:00'),
	statoAppello tipostatoAppello  not null default 'aperto', 
	cdl varchar(10) references "UniNostra".corsodilaurea(codice) on delete cascade --aggiornare ER
);

--Creazione del corso di laurea 
create table "UniNostra".CorsoDiLaurea(
	codice varchar(10) primary key check, 
	nome varchar(50) not null check (nome<>''),
	descrizione varchar(200), 
	isAttivo bool not null default false, 
	--cfuTot integer not null,
	durata tipoCorsoLaurea not null 
);

--Creazione dello studente 
create table "UniNostra".Studente (
	matricola serial primary key, 
	telefono varchar(20) not null check(telefono<>''),
	indirizzoResidenza varchar(100) not null check(indirizzoResidenza<>''),
	dataNascita Date not null, 
	annoIscrizione Date not null default current_date,
	inCorso bool not null default true,
	idUtente integer references "UniNostra".Utente(idUtente) on delete cascade, 
	idCorso varchar references "UniNostra".CorsoDiLaurea(codice) on delete cascade,
	stato tipoSatoExStudente default null,
	votoLaur "UniNostra".votoLaurea default null 
);
drop table "UniNostra".studente 
drop table "UniNostra".iscrizioneesame 

--Creazione dell'iscrizione con relativo esito dell'esame 
create table "UniNostra".IscrizioneEsame (
	matricola integer references "UniNostra".Studente(matricola) on delete cascade, 
	id integer references "UniNostra".Appello(idAppello) on delete cascade, 
	votoEsame "UniNostra".voto default null,
	stato tipoStatoVoto default 'Iscritto',
	isLode bool default null,
	primary key(matricola, id)
);

--Creazione storico studenti 
create table "UniNostra".ExStudente(
	matricola integer primary key, 
	nome varchar(100) not null, 
	cognome varchar(100) not null, 
	telefono varchar(20) not null check(telefono<>''),
	indirizzoResidenza varchar(100) not null check(indirizzoResidenza<>''),
	dataNascita Date not null, 
	annoIscrizione Date not null, 
	inCorso bool default null,
	stato tipoSatoExStudente default null, 
	dataRimozione date not null default current_date, 
	votoLaurea "UniNostra".votoLaurea default null,
	codiceCorso varchar(10) references "UniNostra".CorsoDiLaurea(codice) on delete cascade
);

--stroico valutazioni 
create table "UniNostra".StoricoValutazioni (
	id serial primary key, 
	votoEsame "UniNostra".voto default null, 
	stato tipoStatoVoto default null,
	idAppello integer references "UniNostra".Appello(idAppello) on delete cascade, 
	matricola integer references "UniNostra".ExStudente(matricola) on delete cascade
);

--piani studi 
create table "UniNostra".PianoStudi(
	codiceCorso varchar(10) references "UniNostra".CorsoDiLaurea(codice) on delete cascade, 
	codiceInsegnamento integer references "UniNostra".Insegnamento(codice) on delete cascade, 
	annoErogazione annoEsame default null,
	primary key(codiceCorso,codiceInsegnamento)
);

--aggiungere viste per carriera esame superati e carriera esami studenti e media esami


--FUNZIONI 
--NB = i parametri notati con * possono anche essere nulli. 

--Aggiunta di un corso di laurea 
--Parametri : Codice del Corso (varchar), Nome del corso (varchar), Descrizione(varchar) *, Durata (magistrale o triennale)
--Eccezioni : Se la durata non è 3 o 5. 
	
	CREATE OR REPLACE PROCEDURE "UniNostra".inserisciCorsoLaurea(
    	codice varchar(10), nome varchar(50), descrizione varchar(200), durata tipoCorsoLaurea
	)
	AS $$
	BEGIN     
    	INSERT INTO "UniNostra".CorsoDiLaurea(codice, nome, descrizione, durata)
        VALUES (codice,nome,descrizione,durata);
	END;
	$$ LANGUAGE plpgsql ;
	
	--call "UniNostra".inserisciCorsoLaurea('FX102','Informatica','Corso di lauerea Magistrale in informatica','5');

--Funzione che permette di attivare e disattivare un corso di laurea, sara utilizzata internamente. 
--Parametri : id del corso di laurea (varchar), stato (boolean)
--Eccezioni : Se il corso di laurea inserito come parametro non esiste
--			 

	create or replace procedure "UniNostra".aggiornaStatoCorso(
		idCorsoLaurea varchar(10), stato bool
	)
	as $$
	begin 
		PERFORM * from "UniNostra".CorsoDiLaurea cdl where idCorsoLaurea = cdl.codice ;
	    	IF not FOUND THEN 
	    		raise exception 'Corso di laurea inserito non esistente';
	    	end if;
	    	
	    	UPDATE "UniNostra".corsodilaurea 
			SET isattivo  = stato 
			WHERE codice  = idCorsoLaurea;
	end;
	$$language plpgsql;
	
	--call "UniNostra".aggiornaStatoCorso('FX101',true);

--Aggiunta di un insegnamento
--Parametri : Nome dell'insegnamento (varchar), Descrizione(varchar) *, Cfu(integer), IdDocente(integer) 
--Eccezioni : Se il docente inserito non esite.
--			  Se il docente tiene già il corso che si vuole inserire. 
	
	create or replace procedure "UniNostra".inserisciInsegnamento ( 
		nomeInsegnamento varchar(50), Descrizione varchar(200), CfuEsame integer, Docente integer
	)
	as $$ 
	begin 
		PERFORM * FROM "UniNostra".Insegnamento as i WHERE Lower(replace(i.nome, ' ', '')) = Lower(replace(nomeInsegnamento , ' ', '')) and i.cfu = CfuEsame and i.idDocente = Docente ;
    	IF FOUND THEN 
        	RAISE EXCEPTION 'il docente è già responsabile dell insegnamento ';
    	END IF;
		insert into "UniNostra".Insegnamento(nome, descrizione, cfu, annoInizio,idDocente)
		values (nomeInsegnamento,Descrizione,CfuEsame, cast(date_part('year', CURRENT_DATE) as integer), Docente);	
	end;
	$$ language plpgsql;
	
	--call "UniNostra".inserisciInsegnamento('Programmazione 3','non esiste','12','4');

--Aggiornamento responsabile di un corso 
--Prametri : id dell'insegnamento che si vuole aggiornare (integer), id del nuovo docente (integer)
--Eccezioni : Se l'insegnamento inserito non esiste 
--			  Se il nuovo responsabile tiene già 3 insegnamenti
	
	create or replace procedure "UniNostra".updateDocenza ( 
		idInsegnamento integer, Docente integer
	)
	as $$ 
	begin 
		PERFORM * FROM "UniNostra".Insegnamento as i where i.codice = idInsegnamento  ;
    	IF not FOUND THEN 
        	RAISE EXCEPTION 'l insegnamento inserito non esiste';
    	END IF;
    
		update "UniNostra".Insegnamento 
		set idDocente = Docente 
		where codice = idInsegnamento;	
	end;
	$$ language plpgsql;

	--call "UniNostra".updateDocenza('7','1');

--Aggiungi un nuovo utente, procedura interna utilizzata per aggiungere studenti, segretari o docenti. 
--Parametri : Nome(Varchar), Cognome(varchar), Password(varchar), Email(varchar), tipo (tipoUtente), cf(varchar)

	create or replace procedure "UniNostra".aggiungiUtente (
		nomeU varchar(50), cognomeU varchar(50), emailU varchar(50),pssw varchar(32), tipoU tipoUtente, cfU varchar(16)
	)
	as $$ 
	declare 
		ps text;
	begin 
		ps := md5(pssw)::varchar(20);
		insert into "UniNostra".utente (nome,cognome,email,password,tipo,cf)
		values (nomeU,cognomeU,emailU,ps,tipoU,cfU);
	end;
	$$language plpgsql;

	--call "UniNostra".aggiungiUtente('nome','cognome','email@gmail.com','1234','Studente','cf10')
	
--Modifica delle credenziali di accesso di un utente. L'utente viene riconosciuto attraverso l'email e la password, 
--i campi che non vogliono essere modificati andranno lasciati a null. 
--Parametri : email (varcahr), password(varchar), newEmail *(varchar), newPassword *(varchar). 
--Eccezioni : se l'email non esiste nel db
--            la password associata all'email inserita non esiste

	create or replace procedure "UniNostra".aggiornaCredenzialiUtente ( 
		emailU varchar(50), pssw varchar(32), newEmail varchar(50), newPssw varchar(32)
	)
	as $$
	declare 
		upEmail varchar(50);
		upPsw varchar(32);
	begin 
		perform * from "UniNostra".utente u where u.email = emailU ; 
		if not found then 
			raise exception 'Email non esistente';
		end if; 
		
		perform * from "UniNostra".utente u where u.email = emailU and u.password = pssw; 
		if not found then 
			raise exception 'accesso non autorizzato';
		end if;
	
		if newEmail is not NULL then 
			upEmail = newEmail;
		else 
			upEmail = emailU;
		end if;
	
		if newPssw is not null then 
			upPsw = md5(newPssw)::varchar(20);
		else 
			upPsw = pssw;
		end if; 
		
		update "UniNostra".utente
		set password = upPsw , email = emailU
		where password = pssw and email = emailU;
	end;
	$$language plpgsql;

	--select * from "UniNostra".utente u 
	--call "UniNostra".aggiornaCredenzialiUtente('giacomo.comitani@studenti.UniNostra','1234','aggiornaCredenzialiUtente.@studenti.UniNostra','1234');

--Aggiunta di un docente 
--Parametri : Nome(varchar), Cognome(varchar),Password(varchar),Email(varchar),tipo(tipoUtente),cf(varchar),indirizzoUfficio (varchar), cellulareInterno(varchar)

	create or replace procedure "UniNostra".aggiungiDocente(
		nomeU varchar(50), cognomeU varchar(50), emailU varchar(50),pssw varchar(32), tipoU tipoUtente, cfU varchar(16),
		indirizzoU varchar(100), cellulareU varchar(10)
	)
	as $$
	declare 
		idU integer;
	begin 
		call "UniNostra".aggiungiUtente (nomeU,cognomeU,emailU,pssw,tipoU,cfU);
		
		select u.idUtente into idU 
		from "UniNostra".utente u 
		where u.email = emailU and u.cf = cfU;
	
		insert into "UniNostra".docente (idUtente, indirizzoUfficio, cellulareInterno)
		values (idU, indirizzoU,cellulareU);
	
	end;
	$$language plpgsql;

	--call "UniNostra".aggiungiDocente ('Violetta','Lonati','ViolettaLonati@docenti.UniNostra','1234','Docente','cf6','via celoria 18','3331888772');

--inserimento di uno studente nella base di dati, saranno richieste le informazioni dello studente e il corso di laurea a cui si vuole iscrivere. procedura utilizzata internamente dalla segreteria 
--Parametri : nome (varchar), cognome(varchar), email(varchar),password(varchar), cf(varchar) ,Telefono (varchar), indirizzo residenza (varchar), dataNascita (date), 
--			   annoIscrizione (integer), idCorso (varchar)
--Eccezioni : Se il corso di laurea non è attivo lo studente non può iscriversi. 
--			  Se il corso di laurea inserito non esiste. 
--			  Se il corso di laurea non è attivo.
--			  Se lo studente risulta già iscritto ad un corso di laurea 

	create or replace procedure "UniNostra".aggiungiStudente(
		telefonoU varchar(20), residenzaU varchar(100), dataNascitaU date, idCorsoU varchar(10)
	)
	as $$
	declare 
		idU integer; 
	begin
		
		perform * from "UniNostra".corsodilaurea c where c.codice = idCorsoU; 
		if not found then 
			raise exception 'il corso inserito non esiste';
		end if;
	
		perform * from "UniNostra".corsodilaurea c where c.codice = idCorsoU and c.isattivo = false ;
		if found then 
			raise exception 'lo studente non può iscriversi al corso di laurea in quanto è inattivo (senza insegnamenti)';
		end if;
	
		perform * from "UniNostra".Utente u where u.email = emailU or u.cf = cfU; 
		if found then 
			raise exception 'Lo studente risulta già iscritto ad un corso di laurea';
		end if; 
		
		call "UniNostra".aggiungiUtente (nomeU,cognomeU,emailU,passwordU,'Studente',cfU);
		select u.idutente into idU from "UniNostra".utente u where u.email = emailU;
		
		insert into "UniNostra".studente (telefono,indirizzoresidenza,datanascita,idutente,idcorso)
		values(telefonoU,residenzaU,datanascitaU,idU,idCorsoU);
		
	end;
	$$language plpgsql;

	--call "UniNostra".aggiungiStudente('Giacomo','Comitani','giacomo.comitani@studenti.UniNostra','1234','cf5','3930582002','vimodrone mi','2002-11-03','FX102');

--funzione per il cambio di corso di laurea di uno studente to do

--funzione per l'inserimento di un segretario, può essere utilizzata solamenete da altri segretari
--Parametri : nomeU varchar(50), cognomeU varchar(100), emailU varchar(50), passwordU varchar(32), cfU varchar(16),
--			  indirizzoSegreteria varcha(100), nomeDipartimento varchar(50), cellulareInterno varchar(10)
--Eccezioni : Segretario già registrato 
	
	create or replace procedure "UniNostra".inserisciSegretario( 
		 nomeU varchar(50), cognomeU varchar(100), emailU varchar(50), passwordU varchar(32), cfU varchar(16),
		 indirizzo varchar(100), nomeDip varchar(50), cellulare varchar(10)
	)
	as $$
	declare 
		idU integer; 
	begin
		perform * from "UniNostra".utente u where u.email = emailU or u.cf = cfU;
		if found then 
			raise exception 'segretario già registrato nel sistema';
		end if; 
		
		call "UniNostra".aggiungiUtente(nomeU,cognomeU,emailU,passwordU,'Segretario',cfU);
		select u.idutente into idU from "UniNostra".utente u where u.email = emailU;
		
		insert into "UniNostra".segretario (idUtente,indirizzosegreteria ,nomedipartimento ,cellulareinterno)
		values(idU,indirizzo,nomeDip,cellulare); 
		
	end;
	$$language plpgsql;

	--call "UniNostra".inserisciSegretario('Luigi','Pepe','LuigiPepe@segreteria.UniNostra','1234','cf4','via celoria 18','informatica','383838');

--funzione per inserimento insegnamenti nei corsi di laurea, li insegnamenti sono condivisi tra corsi di laurea
--Parametri : Codice corso di laurea (varchar), id dell'insegnamento (integer), annoErogazione (annoEsame)
--Eccezioni : Se il codice del corso di laurea non esiste 
--			  Se il codice dell'insegnamento non esiste 
--		      Se l'anno del insegnamento non è valido 

	create or replace procedure "UniNostra".inserisciPianoStudi( 
		codiceLaurea varchar(10), codiceIn integer, annoEr annoEsame 
	)
	as $$ 
	declare 
		tipoLaurea "UniNostra".corsodilaurea.durata%type; 
	begin
		perform * from "UniNostra".corsodilaurea c where c.codice = codiceLaurea;
		if not found then
			raise exception 'Il corso di laurea inserito non esiste';
		end if; 
		
		perform * from "UniNostra".insegnamento i where i.codice = codiceIn;
		if not found then
			raise exception 'insegnamento inserito inesistente';
		end if;
	
		select c.durata  into tipoLaurea from "UniNostra".corsodilaurea c where c.codice = codiceLaurea;
		if tipoLaurea = '5' and annoEr = '3' then 
			raise exception 'anno di erogazione non esistente per il cdl inserito';
		end if;
	
		insert into "UniNostra".pianostudi (codiceCorso,codiceinsegnamento,annoerogazione)
		values(codiceLaurea, codiceIn, annoEr); 
		
	end; 
	$$language plpgsql; 
	
	--call "UniNostra".inserisciPianoStudi('FX102','6','2')


--Funzione per inserimento propredeuticità tra insegnamenti. Viene inserito per prima l'insegnamento a cui ci si vuole riferire e successivamente l'insegnamento propredeutico per il superamento del primo.
--la propredeuticità vale per il corso di laurea specificato, in cui devono far parte entrambi gli insegnamenti.
--Parametri : Codice Insegnamento 1 (integer), Codice insegnamento 2 propredeutico (integer), codiceCDL (varchar)
--Eccezzioni : Insegnamento 1 non esistente, insegnamento 2 non esistente. 
--			   Se Codice1 = Codice2, gli insegnamenti devono essere diversi.
--			   Gli insegnamenti devono appartenere allo stesso corso di laurea.			   

	create or replace procedure "UniNostra".inserisciPropedeuticita (
		codice1 integer, codice2 integer, codiceCdl varchar(10)
	)
	as $$
	begin 
		if codice1 = codice2 then 
			raise exception 'Un insegnamento non può essere propedeutico a se stesso';
		end if; 
		
		perform * from "UniNostra".pianostudi p inner join "UniNostra".corsodilaurea c on p.codicecorso = c.codice 
		where p.codiceinsegnamento = codice1 and c.codice = codiceCdl;
		if not found then 
			raise exception 'il corso con codice % non appartiene al corso di laurea inserito : %',codice1,codiceCdl;
		end if;
	
		perform * from "UniNostra".pianostudi p inner join "UniNostra".corsodilaurea c on p.codicecorso = c.codice 
		where p.codiceinsegnamento = codice2 and c.codice = codiceCdl;
		if not found then 
			raise exception 'il corso con codice % non appartiene al corso di laurea inserito : %',codice2,codiceCdl;
		end if;
	
		insert into "UniNostra".propedeuticita (esame,prop,codiceLaurea)
		values (codice1,codice2,codiceCdl);
	
	end;
	$$language plpgsql;

--Funzione per i docenti responsabili degli insegnamenti, permette di inserire un appello in una certa data per gli insegnamenti che detiene specifiacando il corso di laurea.
--l'appello deve essere programmato almeno un giorno prima
--Parametri : idInsegnamento (integer), idDocente (integer), aula (varchar), note (varchar), dataEsame (date), oraInizio (time), oraFine (time) 
--Eccezioni : Se il docente non è responsabile dell'insegnamento.
--			  Se il codice di laure inserito non esiste
--            Se l'ora di fine non viene dopo l'ora di inizio.
--			  l'esame deve durare almeno 15 minuti 
--            Se esiste già un appello dello stesso insegnamento e dello stesso cdl che si sovrappone.
--			  Se l'aula è già occupata a quel ora

	create or replace procedure "UniNostra".inserimentoAppello(
		idIns integer , idDoc integer, al varchar(20), nt varchar(200), dataE date , oraIn time, oraFi time, codicel varchar(10)
	)
	as $$
	declare 
		oldAppello "UniNostra".appello%rowtype;
		ini time ;
		fin time ;
		aulaE text;
		aule text[];
		ris bool; 
	begin
		perform * from "UniNostra".Insegnamento i where i.codice = idIns and i.iddocente = idDoc;
		if not found then 
			raise exception 'il docente con codice % non è responsabile dell insegnamento %',idDoc, idIns;
		end if; 
	
		perform * from "UniNostra".corsodilaurea c where c.codice = codicel; 
		if not found then 
			raise exception 'non esiste nessuan laurea con codice %',codicel;
		end if; 
	
		perform * from "UniNostra".pianostudi p where p.codiceinsegnamento = idIns and p.codicecorso = codicel;
		if not found then 
			raise exception 'il cdl % non prevede l insegnamento con codice %',codicel,idIns;
		end if;
	
		if oraIn > oraFi then 
			raise exception 'ora di fine % maggiore del ora di inzio %',oraFi,oraIn;
		end if;
	
		if dataE <= current_date then
			raise exception 'l esame non può essere inserito, data passata o troppo poco preavviso';
		end if;
	
		if oraFi - oraIn < '00:15:00' then 
			raise exception 'l esame deve durare almeno 15 minuti';
		end if;
	
		FOR oldAppello in select * from "UniNostra".appello a where a.codiceinsegnamento = idIns and a.dataesame = dataE and a.cdl = codicel
    		loop
	    		--raise exception '%',oldAppello;
	    		ini = oldAppello.oraInizio;
				fin = oldAppello.oraFine;
				if (  (oraIn <= ini and oraFi >= fin)
			   		  or (oraIn >= ini and oraFi <= fin)
			   		  or (oraIn < ini and oraFi > ini)
		       		  or (oraIn < fin and oraFi > fin)
				) then 
					raise exception 'Esiste già un appello per l insegnamento %  del cdl % in data % ,con ora inizo % e ora fine %, si sovrapporrebbero',idIns,codicel,oldAppello.dataEsame,ini,fin;
				end if; 
		    	
		 	END LOOP;	
		 
		for oldAppello in select * from "UniNostra".appello a where a.dataesame = dataE
			loop 
				ini = oldAppello.oraInizio;
				fin = oldAppello.oraFine; 
				if ((oraIn <= ini and oraFi >= fin)
			   		  or (oraIn >= ini and oraFi <= fin)
			   		  or (oraIn < ini and oraFi > ini)
		       		  or (oraIn < fin and oraFi > fin)
				)then
					select into aule string_to_array(oldAppello.aula, '+');
					foreach aulaE in array aule
						loop
							perform * from "UniNostra".appello a where a.idappello = oldAppello.idappello and position(aulaE in al) > 0; 
							if found then 
								raise exception 'aula % già occupata nel orario % - % ', aulaE, ini, fin;
							end if;
						end loop;
				end if;    
			end loop;
			
		insert into "UniNostra".appello (codiceInsegnamento,aula,note,dataesame,orainizio,orafine, statoappello,cdl)
		values(idIns,al,nt,dataE,oraIn,oraFi,default,codicel);
	end;
	$$language plpgsql;
	
	--call "UniNostra".inserimentoAppello('6','4','gamma+lambda','bho','2023/09/09','13:10:00','15:15:00','FX101');
	--call "UniNostra".inserimentoAppello('10','1','gamma','bho','2023/09/10','10:50:00','15:15:00','FX102');

--Funzione che peremtte di aggiornare lo stato di una appello, uno studente si può iscrivere ad un appello solo se esso è aperto, ovvero fino a un ora prima dell'ora di inizio dell'esame. 
--update stato appello da chiamare ogni volta che lo studente refresha la pagine delle iscrizioni
--Parametri  : idAppello (integer)
--Eccezzioni : se non esiste nessun appello con l'id inserito

	create or replace procedure "UniNostra".aggiornaStatoAppello(
		idApp integer
	)
	as $$
	declare 
		oldAppello "UniNostra".appello%rowtype; 
		h1 integer; 
		h2 integer; 
	begin 
		perform * from "UniNostra".Appello ap where ap.idappello = idApp;
		if not found then 
			raise exception 'Nessun appello trovato con id %', idApp;
		end if; 
	
		select * into oldAppello from "UniNostra".appello a where a.idappello = idApp;
		select extract into h1( hour from oldAppello.oraInizio) as hour ; 
		select extract into h2( hour from now()) as hour;
	
		if ( CURRENT_DATE > oldAppello.dataEsame ) or (CURRENT_DATE = oldAppello.dataEsame and h1-h2 <= '1') then 	
			update "UniNostra".appello
			set statoappello = 'chiuso'
			where idappello = idApp;
		end if; 
	end;
	$$ language plpgsql;

	--call "UniNostra".aggiornaStatoAppello('57');

--Funzione per l'iscrizione di uno studente ad un esame. L'utente può iscriversi solo se l'appello non è chiuso (data passata) e fino ad un ora prima dell'esame.
--un utente inoltre, può iscriversi solo agli esami previsti dal suo cdl. 
--Parametri : idAppello(integer), matricola(integer)
--Eccezioni : nessun studente con matricola inserita
--			  nessun appello con id inserito 		  

	create or replace procedure "UniNostra".inserisciIscrizioneEsame(
		mat integer, idAppelloEsame integer 
	)
	as $$
	declare
		 cdl "UniNostra".studente.idcorso%type; 
	begin 
		perform * from "UniNostra".studente s where s.matricola = mat; 
		if not found then 
			raise exception 'non esiste nessun studente con matricola %.',mat;
		end if;
		
		perform * from "UniNostra".appello a where a.idappello = idAppelloEsame;
		if not found then 
			raise exception 'non esiste nessun appello con id %',idAppelloEsame;
		end if; 

		insert into "UniNostra".iscrizioneesame(matricola,id,votoesame,stato,islode)
		values (mat,idAppelloEsame,default,default,default);			
	end;
	$$ language plpgsql;
	
	--call "UniNostra".inserisciIscrizioneEsame('1','14');

--Funzione che permette ad uno studente di accettare o rifiutare l'esito di un appello per un certo insegnamento.
--l'accetazione viene richiesta all'utente tramite una variabile booleana se è false l'esito è accettato altrimenti l'esito è rifiutato.
--Parametri : idAppello (integer), matricola(integer), accettato (bool)
--Eccezioni : lo studente non è iscritto all'appello d'esame
--			  la sua iscrizione deve essere inAttesa e l'appello deve essere chiuso

	create or replace procedure "UniNostra".accettaVoto(
		idApp integer, mat integer, isRifiutato bool
	)
	as $$ 
	declare 
		status "UniNostra".appello.statoappello%type;
		iscrizione "UniNostra".iscrizioneesame%rowtype;
	begin 
		call "UniNostra".aggiornaStatoAppello(idApp);
		perform * from "UniNostra".IscrizioneEsame ie where ie.id = idApp and ie.matricola = mat;
		if not found then 
			raise exception 'lo studente non è iscritto all^appello %',idApp;
		end if;
		
		select * into iscrizione from "UniNostra".IscrizioneEsame ie where ie.id = idApp and ie.matricola = mat;
		select a.statoappello into status from "UniNostra".appello a where a.idappello = idApp ;
		if status <> 'chiuso' or iscrizione.stato <> 'In attesa' then 
			raise exception 'lo studente non può accettare o rifiutare il voto';
		end if;
		
		if isRifiutato = true  then 
			update "UniNostra".iscrizioneesame i
			set stato = 'Rifiutato'
			where i.id = idApp and i.matricola = mat; 
		else 
			update "UniNostra".iscrizioneesame i
			set stato = 'Accettato'
			where i.id = idApp and i.matricola = mat; 
		end if;
	end;
	$$language plpgsql;

	--call "UniNostra".accettaVoto(11,1,true)

--lo stato dello studente deve essere iscritto. 

--funzione che permette ad uno studente di disiscriversi da un appello a cui risulta iscritto
--Parametri : matricola(integer), idAppello(integer)
--Eccezioni : se lo studente non è iscritto all'appello inserito 
--            se l'appello è chiuso o l'utente si disiscrive a meno di un ora dall'inizio. 

	create or replace procedure "UniNostra".elliminaIscrizione(
		mat integer, idApp integer 
	)
	as $$ 
	declare
		oraApp "UniNostra".appello.orainizio %type;
		dataApp "UniNostra".appello.dataesame%type;
		statoApp "UniNostra".appello.statoappello%type; 
		h1 integer; 
		h2 integer; 
	begin 
		call "UniNostra".aggiornaStatoAppello(idApp);
		perform * from "UniNostra".iscrizioneEsame i where i.matricola = mat and i.id = idApp and i.stato = 'Iscritto';
		if not found then 
			raise exception 'lo studente % non risulta iscritto all^appello %',mat,idApp; 
		end if; 

		select a.statoappello , a.orainizio, a.dataesame into statoApp,oraApp,dataApp from "UniNostra".iscrizioneesame i inner join "UniNostra".appello a on i.id = a.idappello 
		where i.matricola = mat and i.id = idApp ;
	
		select extract into h1( hour from oraApp) as hour ; 
		select extract into h2( hour from now()) as hour;
		if statoApp <> 'aperto' then 
			raise exception 'lo studente % non si può discrivere dall^appello %, in quanto è chiuso',mat,idApp;
		end if;
	
		if cast(now() as date) = dataApp and h1 - h2 <= 1 then 
			raise exception 'lo studente % non si può disiscrivere dall^appello % in quanto manca meno di un ora',mat,idApp;
		end if; 
		
		delete from "UniNostra".iscrizioneesame i where i.matricola = mat and i.id = idApp; 
	end
	$$ language plpgsql;

	--call "UniNostra".elliminaIscrizione('1','14')

--Funzione che permette ad un docente di registrare il risultato di un esame 
--Parametri : idDocente(integer), matricola(integer),idAppello(integer), voto* (votoesame), lode (bool)
--Eccezioni : Docente non responsabile dell'appello inserito.
--			  L'appello deve essere chiuso.
--			  lo studente deve essere iscritto all'appello.
--			  La lode non può essere true se il voto non è 30.
--			  lo studente ha già un voto registrato 

	create or replace procedure "UniNostra".registraVotoEsame(
		idDoc integer, mat integer, idApp integer, votoE "UniNostra".voto, lode bool 
	)
	as $$
	declare 
		idIns "UniNostra".insegnamento.codice%type;
		corsoL "UniNostra".appello.cdl%type;
	begin 
		perform * from "UniNostra".docente d where d.idutente = idDoc;
		if not found then 
			raise exception 'il docente con id %, non esiste all^interno della base di dati',idDoc;
		end if; 
		perform * from "UniNostra".studente s where s.matricola = mat;
		if not found then 
			raise exception 'lo studente % , non esiste all^interno della base di dati',mat;
		end if;
	
		perform * from "UniNostra".appello a inner join "UniNostra".insegnamento i on a.codiceinsegnamento = i.codice  
		where a.idappello = idApp and i.iddocente = idDoc; 
		if not found then 
			raise exception 'il docente % non è responsabile dell^apello %',idDoc,idApp;
		end if;
	
		call "UniNostra".aggiornaStatoAppello(idApp);
		perform * from "UniNostra".appello a where a.idappello = idApp and a.statoappello = 'chiuso';
		if not found then 
			raise exception 'il docente % non può inserire voti, in quanto l^appello % non è ancora chiuso',idDoc,idApp;
		end if;
		
		perform * from "UniNostra".iscrizioneesame i where i.id = idApp and i.matricola = mat and i.stato = 'Iscritto' ;
		if not found then
			raise exception 'lo studente % non risulta iscritto all^appello %',mat,idApp;
		end if;
		
		--controllare se lo studente ha già un voto accettato per quel inseganemtno
		select a.codiceinsegnamento,a.cdl  into idIns,corsoL from "UniNostra".appello a where a.idappello = idApp;
		perform * from "UniNostra".iscrizioneesame i inner join "UniNostra".appello a on i.id = a.idappello 
		where i.matricola = mat and a.codiceinsegnamento = idIns and  a.cdl = corsoL and i.stato = 'Accettato';
		if found then
			delete from "UniNostra".iscrizioneesame i where i.matricola = mat and i.id = idApp;
			raise notice 'lo studente % ha già un esito registrato per l^insegnamento %',mat,idIns;
		end if;
		
		if votoE is null then 
			update "UniNostra".iscrizioneesame i 
			set stato = 'Ritirato', isLode = false
			where i.matricola = mat and i.id = idApp;
		else 
			if votoE < 18 then 
				update "UniNostra".iscrizioneesame i 
				set stato = 'Bocciato', votoesame = votoE,isLode = false 
				where i.matricola = mat and i.id = idApp;
			else
				if lode and votoE <> 30 then 
					raise exception 'non può essere asseganta la lode se il voto non è 30';
				end if;
				update "UniNostra".iscrizioneesame i 
				set stato = 'In attesa', isLode = lode, votoesame = votoE 
				where i.matricola = mat and i.id = idApp;	
			end if;
		end if;
	end 
	$$ language plpgsql;
	
	--call "UniNostra".registraVotoEsame('1','1','15',30,true);

--Funzione che permette di aggiornare la variabile inCorso di uno studente 
--Parametri : Matricola(integer)
--Eccezioni : la matricola inserita non esiste 

	create or replace procedure "UniNostra".aggiornaInCorso(
		mat integer
	)
	as $$
	declare 
		dur "UniNostra".corsodilaurea.durata%type;
		iscrizione "UniNostra".studente.annoiscrizione%type;
		somma integer;
	begin
		perform * from "UniNostra".studente s where s.matricola = mat;
		if not found then 
			raise exception 'la matricola inserita non esiste';
		end if;
		
		select s.annoiscrizione, c.durata  into iscrizione,dur from "UniNostra".studente s inner join "UniNostra".corsodilaurea c on c.codice = s.idcorso 
		where s.matricola = mat;
		if dur = '3' then
			somma := 3;
		else 
			somma := 2;
		end if;
		if 	DATE_PART('year', AGE(current_date,iscrizione)) >= somma+1 then 
			update "UniNostra".studente
			set incorso = false 
			where matricola = mat;
		end if;
	end 
	$$ language plpgsql;

	--call "UniNostra".aggiornaInCorso('2');
	--select * from "UniNostra".studente s  
	--update "UniNostra".studente s set incorso  = true  where s.matricola = '1';

--Funzione che peremtte ad un segretario di registrare la laurea o il ritiro di uno studente. 
--Parametri : Matricola (integer), status (tipoSatoExStudente), votoLaurea "UniNostra".votoLaurea
--Eccezioni : La matricola inserita non esiste 
--			  Lo studente inserito non ha ancora passato tutti gli esami previsti dal suo cdl 
	
	create or replace procedure "UniNostra".registraLaurea(
		mat integer , status tipoSatoExStudente, votoL "UniNostra".votoLaurea
	)
	as $$
	declare 
		cdlS "UniNostra".appello.cdl%type; 
		idIns "UniNostra".insegnamento.codice%type;
	begin 
		perform * from "UniNostra".studente s where s.matricola = mat;
		if not found then 
			raise exception 'lo studente % non esiste all^interno della base di dati',mat;
		end if;
	
		if status = 'Laureato' and votoL is null then 
			raise exception 'inserire il voto di laurea';
		end if;
		
		if status = 'Ritirato' then 
			votoL := null; 
		end if;
	
		if status is not null then
			if status = 'Laureato' then 
				select s.idcorso into cdlS from "UniNostra".studente s where s.matricola = mat; 
				for idIns in select p.codiceinsegnamento from "UniNostra".pianostudi p where p.codicecorso = cdlS
					loop 
						perform * from "UniNostra".iscrizioneesame i inner join "UniNostra".appello a on i.id = a.idappello  
						where i.matricola = mat and i.stato = 'Accettato' and a.cdl = cdlS and a.codiceinsegnamento = idIns;
						if not found then
							raise exception 'lo studente non ha passato l^esame con id % previsto dal cdl %',idIns,cdlS;
						end if;
					end loop;
			end if;
			update "UniNostra".studente s set stato = status, votolaur = votoL where s.matricola = mat;
			delete from "UniNostra".studente s where s.matricola = mat;
		end if;
	end; 
	$$ language plpgsql;
	
	--call "UniNostra".registraLaurea(1,'Laureato',90)
	
--riattivare trigger

--fixare il fatto che la chiusura degli appelli non si aggiorna 

--trigger all'eliminazione dello studente

--aggiungere ad ex studente riferimento ad utente

--TRIGGER 

--Trigger che permette di disattivare un corso di laurea se non presenta insegnamenti o attivarlo se ne presenta almeno uno. 
--ACTION : insert or delete sulla tabella PianoStudi

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

--Trigger che permette di asseganre un insegnamento ad un docente solo se è responsabile di meno di 3 corsi. 
--ACTION : Insert o update sulla tabella degli insegnamenti 
--Eccezioni : se il docente è già resposabile di 3 corsi 

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

--Trigger che al cambio di docenza vada ad aggiungere nello storico del docente l'insegnamento che tenva. 
--Action : update della tabella insegnamenti 
--Eccezioni : se il docente ha tenuto il corso in passato non li può essere riasseganto

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

	update "UniNostra".insegnamento 


--Trigger che permette di evitare inserimenti di propedeucità che causerebbero un ciclo, ad esempio : 
--	A -> B, B -> C, C -> A
--Action : inserimento di dati nella tabella delle propedeucità
--Eccezioni : inserimeno propedeucità non consentita

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

	--call "UniNostra".inserisciPropedeuticita('4','10','FX102');

--Trigger per verificare che non esistino già appelli di esami previsti nello stesso anno accademico di un cdl, previsti nella stessa data. 
--Action : inserimento di un appello nella tabella degli appelli. 
--Eccezione : se esiste già un altro appello nella stessa data per un corso previsto nello stesso anno accademico di un certo cdl. 

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
	
--Trigger per il controllo delle iscrizioni ad un appello da parte degli studenti. 
--Uno studente si può iscrivere ad un appello fino ad un ora prima dell'esame ed a solo esami del suo cdl
--Action : inserimento su iscrizioneEsame
--Eccezioni : L'appello risulta chiuso 
--			  iscrizione un ora prima dell'inizio dell'esame
--			  lo studente si sta iscrivendo ad un appello di un insegnamento non del suo cdl 
--			  lo studente no ha i requisiti di prepodeuticità dell'esame 

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

	select * from "UniNostra".appello a 
	--drop trigger controllaIscrizioniAppelli on "UniNostra".iscrizioneesame;
--Trigger che controlla che uno studente all'atto dell'iscrizione ad un certo appello non abbia già un voto registrato come Accettato per quel insegnamento.  
--Action : insert sulla tabella iscrizioneEsami 
--Eccezioni : se lo studente possiede un esito pendente (non ancora accettato o rifiutato) non si potrà iscrivere ad un nuovo appello. 
--			  se lo studente ha già eccettato un voto per lo stesso insegnamento

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


--Trigger che controlla che uno studente si possa iscrivere solo ad un turno di un esame durante la stessa giornata
--Action : inserimento nella tabella iscrizioni esami 
--Eccezioni : se lo studente risulta già isctitto ad un altro turno dello stesso esame durante la giornata

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

--Trigger, quando uno studente accetta un voto di un certo esame, viene automaticamente disiscritto da tutti gli altri appelli di quel esame a cui si era iscritto. 
--Action : update di iscrizione studente

	create or replace function "UniNostra".disiscriviStudente()
	returns trigger as $$
	declare
		codIns "UniNostra".appello.codiceinsegnamento %type;
		idApp "UniNostra".iscrizioneesame.id%type;
	begin 
		select a.codiceinsegnamento into codIns from "UniNostra".appello a where a.idappello = new.id;

		if old.stato = 'In attesa' and new.stato = 'Accettato' then 
			for idApp in select i.id from "UniNostra".iscrizioneesame i inner join "UniNostra".appello a on i.id = a.idappello 
			where i.id <> new.id and a.codiceinsegnamento = codIns and i.matricola = new.matricola and i.stato = 'Iscritto' or i.stato = 'In attesa'
				loop 
					delete from "UniNostra".iscrizioneesame i where i.matricola = new.matricola and i.id = idApp;
				end loop ;
		end if; 
		return new;
	end 
	$$ language plpgsql;
	
	CREATE OR REPLACE TRIGGER elliminaIscrizioniPassate after update on "UniNostra".iscrizioneesame  
	FOR EACH ROW EXECUTE FUNCTION "UniNostra".disiscriviStudente();



	select * from "UniNostra".iscrizioneesame i 
	select * from "UniNostra".appello a 
	
	update "UniNostra".iscrizioneesame i set votoesame = 20 , stato = 'In attesa' where i.id =12
	
	call "UniNostra".accettaVoto('12','1',false)
	
	
	select * from "UniNostra".pianostudi p 
	select * from "UniNostra".propedeuticita p 
	
	
	call "UniNostra".inserimentoAppello('6','4','bertone','bho','2023/09/11','10:00:00','13:00:00','FX101');
	insert into "UniNostra".appello (codiceinsegnamento,aula,note,dataesame,orainizio,orafine,statoappello,cdl)
	values('6','omega','bho','2023/09/09','18:00:00','19:50:00','aperto','FX101');

	call "UniNostra".inserisciIscrizioneEsame('1','21');

	select * from "UniNostra".iscrizioneesame i
	update "UniNostra".iscrizioneesame i set stato = 'In attesa',votoesame = '30' where i.id = '21'
	--delete from "UniNostra".iscrizioneesame i2 where i2.id = 12
	
	update "UniNostra".appello a set orainizio = '16:00:00', orafine = '18:00:00' where a.idappello = 14
	
	insert into "UniNostra".iscrizioneesame (matricola,id,votoesame,stato,islode)
	values('1','21','27','In attesa',false);
