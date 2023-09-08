-- Funzione che permette di effettuare il login per un certo utente 
-- Parametri : email (text), psw(text)
-- Eccezioni : se l'email o la password risultano incorretti
CREATE OR REPLACE FUNCTION "UniNostra".login (
  emailNew TEXT,
  passwordNew TEXT
)
RETURNS TABLE (
	idU integer,
    tipoU tipoUtente
)
LANGUAGE plpgsql
AS $$
	begin
		RETURN QUERY
			select u.idutente , u.tipo 
	    	FROM "UniNostra".utente u
	    	where lower(u.email) = lower(emailNew) and u."password" = md5(passwordNew)::varchar(20);
	 END;
 $$;

--select idU,tipoU from "UniNostra".login('violettaLonati@docenti.UniNostra','1234');

--Funzione che dato l'id di uno studente ritorna alcune sue informazioni 
--Parametri : idUtente (integer)
--Eccezioni : se l'id inserito non esiste o non è uno studente
CREATE OR REPLACE FUNCTION "UniNostra".profiloStudente (
  idU integer
)
RETURNS TABLE (
	matricola integer,
    telefono varchar(20),
    indirizzoresidenza varchar(100),
    annoiscrizione date,
    incorso bool,
    idcorso varchar(10),
    nome varchar(50),
    cognome varchar(100)
)
LANGUAGE plpgsql
AS $$
	begin	
		
		RETURN QUERY
			select s.matricola ,s.telefono ,s.indirizzoresidenza ,s.annoiscrizione ,s.incorso ,s.idcorso,u.nome ,u.cognome 
			from "UniNostra".utente u inner join "UniNostra".studente s on u.idutente = s.idutente 
			where u.idutente = idU;
	 END;
 $$;

--select * from "UniNostra".profiloStudente('11');
--Funzione che dato un id di uno studente, mostra gli appelli aperti per il suo cdl
--Parametri : idUtente(integer)
--Eccezioni : l'id inserito non appartiene ad uno studente o non esiste 
--			  Eccezioni sollevate dal controllo degli appelli

CREATE OR REPLACE FUNCTION "UniNostra".appelliAperti (
  idU integer
)
RETURNS TABLE (
	idappello integer,
	codiceinsegnamento integer,
	nomeIns varchar(50),
	cfu integer,
	nome varchar(50),
	cognome varchar(100),
	dataesame date,
	orainizio time,
	aula varchar(20),
	cdl varchar(10),
	statoappello tipostatoAppello
)
LANGUAGE plpgsql
AS $$
declare 
	idApp "UniNostra".appello.idappello%type;
	co "UniNostra".corsodilaurea.codice%type;
	mat integer;

begin	
	select s.idcorso,s.matricola  into co,mat from "UniNostra".studente s where s.idutente = idU;
	if co is null then 
		raise exception 'l^id inserito non è di uno studente';
	end if;
	
	for idApp in select a.idappello from "UniNostra".appello a where a.cdl = co and a.statoappello = 'aperto'
		loop 
			call "UniNostra".aggiornaStatoAppello(idApp);
		end loop;	
		
	RETURN QUERY
			select a.idappello,a.codiceinsegnamento,i.nome,i.cfu,u.nome,u.cognome,a.dataesame,a.orainizio,a.aula,a.cdl,a.statoappello  
			from "UniNostra".appello a inner join "UniNostra".insegnamento i on a.codiceinsegnamento = i.codice  inner join "UniNostra".utente u on u.idutente = i.iddocente 
			where a.cdl = co and a.statoappello = 'aperto' and not exists (
				select *
				from "UniNostra".iscrizioneesame i2
				where i2.id = a.idappello and i2.matricola = mat
			);
END;
$$;

--select * from "UniNostra".appelliAperti('11');












