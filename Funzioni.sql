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

select * from "UniNostra".iscrizioneesame i ;
select * from "UniNostra".appello a ;
select * from "UniNostra".propedeuticita p ;
delete from "UniNostra".iscrizioneesame i2 where i2.id = 25;
delete from "UniNostra".iscrizioneesame i2 where i2.id = 23;
delete from "UniNostra".iscrizioneesame i2 where i2.id = 29;
update "UniNostra".iscrizioneesame set stato = 'Rifiutato' where id = 13

--Funzione che permette di far visionare ad un certo utente gli appelli d'esame a cui risulta iscritto. 
--Parametri : idUtente(integer) 
--Eccezioni : l'id utente inserito non appartiene ad uno studente 

CREATE OR REPLACE FUNCTION "UniNostra".iscrizioniAppelli (
	  idU integer
	)
	RETURNS TABLE (
		idappello integer,
		codiceinsegnamento integer,
		nomeIns varchar(50),
		cfu integer,
		cdl varchar(10),
		nome varchar(50),
		cognome varchar(100),
		dataesame date,
		orainizio time,
		aula varchar(20),
		statoappello tipostatoAppello,
		statoStudente tipoStatoVoto  
	)
	LANGUAGE plpgsql
	AS $$
	declare 
		mat integer;
		idApp integer;
		begin
			
			select s.matricola into mat from "UniNostra".studente s where s.idutente = idU;
			if mat is null then 
				raise exception 'l^id inserito non appartiene ad uno studente';
			end if;
			
			for idApp in select i.id from "UniNostra".iscrizioneesame i where i.matricola = mat and i.stato = 'Iscritto'
			loop 
				call "UniNostra".aggiornaStatoAppello(idApp);
			end loop;	
		
		
			RETURN QUERY
				select a.idappello ,a.codiceinsegnamento , i2.nome , i2.cfu , a.cdl , u.nome , u.cognome , a.dataesame , a.orainizio, a.aula , a.statoappello, i.stato 
				from "UniNostra".iscrizioneesame i inner join "UniNostra".appello a on i.id = a.idappello inner join "UniNostra".insegnamento i2 on i2.codice = a.codiceinsegnamento inner join "UniNostra".utente u on u.idutente = i2.iddocente  
				where i.matricola = mat and i.stato = 'Iscritto';
		 END;
	 $$;

	drop function "UniNostra".iscrizioniAppelli;

select * from "UniNostra".studente s 

--Funzione che dato l'id utente di uno studente, restituisce tutti gli esami che ha accettato e superato
--Parametri : idUtente (integer)
--Eccezioni : se l'id utente inserito non appartiene ad uno studente 

	CREATE OR REPLACE FUNCTION "UniNostra".visualizzaEsamiPassati(
	  idU integer
	)
	RETURNS TABLE (
		idappello integer,
		codiceinsegnamento integer,
		nomeIns varchar(50),
		cfu integer,
		cdl varchar(10),
		nome varchar(50),
		cognome varchar(100),
		dataesame date,
		votoE "UniNostra".voto,
		lode bool,
		statoStudente tipoStatoVoto  
		
	)
	LANGUAGE plpgsql
	AS $$
	declare 
		mat integer; 
		begin	
			select s.matricola into mat from "UniNostra".studente s where s.idutente = idU;
			if mat is null then 
				raise exception 'l^id inserito non appartiene ad uno studente';
			end if;
			
			RETURN QUERY
				select a.idappello, a.codiceinsegnamento,i2.nome, i2.cfu,a.cdl ,u.nome ,u.cognome ,a.dataesame, i.votoesame, i.islode , i.stato  
				from "UniNostra".iscrizioneesame i inner join "UniNostra".appello a on a.idappello = i.id inner join "UniNostra".insegnamento i2 on i2.codice = a.codiceinsegnamento inner join "UniNostra".utente u on u.idutente = i2.iddocente 
				where i.matricola = mat and i.stato = 'Accettato'
				order by a.dataesame desc ;
		 END;
	 $$;
	
	--select * from "UniNostra".visualizzaEsamiPassati('11');

--Funzione che dato l'id utente di uno studente, restituisce tutta la sua carriera
--Parametri : idUtente (integer)
--Eccezioni : se l'id utente inserito non appartiene ad uno studente 

	CREATE OR REPLACE FUNCTION "UniNostra".visualizzaCarriera(
		  idU integer
		)
		RETURNS TABLE (
			idappello integer,
			codiceinsegnamento integer,
			nomeIns varchar(50),
			cfu integer,
			cdl varchar(10),
			nome varchar(50),
			cognome varchar(100),
			dataesame date,
			votoE "UniNostra".voto,
			lode bool,
			statoStudente tipoStatoVoto  
			
		)
		LANGUAGE plpgsql
		AS $$
		declare 
			mat integer; 
			begin	
				select s.matricola into mat from "UniNostra".studente s where s.idutente = idU;
				if mat is null then 
					raise exception 'l^id inserito non appartiene ad uno studente';
				end if;
				
				RETURN QUERY
					select a.idappello, a.codiceinsegnamento,i2.nome, i2.cfu,a.cdl ,u.nome ,u.cognome ,a.dataesame, i.votoesame, i.islode , i.stato  
					from "UniNostra".iscrizioneesame i inner join "UniNostra".appello a on a.idappello = i.id inner join "UniNostra".insegnamento i2 on i2.codice = a.codiceinsegnamento inner join "UniNostra".utente u on u.idutente = i2.iddocente 
					where i.matricola = mat and i.stato <> 'Iscritto' and i.stato <> 'In attesa'
					order by a.dataesame DESC;
			 END;
		 $$;
		
		
		--select * from "UniNostra".visualizzaCarriera('14')

--Funzione che dato l'id di un utente studente mostra tutti i voti che può rifiutare o accettare. 
--Parametri : idUtente(integer)
--Eccezioni : se l'id inserito non appertiene ad uno studente 
		
	CREATE OR REPLACE FUNCTION "UniNostra".accettaVoti(
			  idU integer
			)
			RETURNS TABLE (
				idappello integer,
				codiceinsegnamento integer,
				nomeIns varchar(50),
				cfu integer,
				cdl varchar(10),
				nome varchar(50),
				cognome varchar(100),
				dataesame date,
				votoE "UniNostra".voto,
				lode bool,
				statoStudente tipoStatoVoto  
				
			)
			LANGUAGE plpgsql
			AS $$
			declare 
				mat integer; 
				begin	
					
					select s.matricola into mat from "UniNostra".studente s where s.idutente = idU;
					if mat is null then 
						raise exception 'l^id inserito non appartiene ad uno studete';
					end if;
					
					RETURN QUERY
						select a.idappello ,a.codiceinsegnamento,i2.nome, i2.cfu,a.cdl ,u.nome ,u.cognome ,a.dataesame, i.votoesame, i.islode , i.stato  
						from "UniNostra".iscrizioneesame i inner join "UniNostra".appello a on a.idappello = i.id inner join "UniNostra".insegnamento i2 on i2.codice = a.codiceinsegnamento inner join "UniNostra".utente u on u.idutente = i2.iddocente 
						where i.matricola = mat and i.stato = 'In attesa'
						order by a.dataesame asc ;
				 END;
			 $$;
			
		--select * from "UniNostra".iscrizioneesame i 
		--update "UniNostra".iscrizioneesame i2 set stato = 'In attesa' where id = '38';
		--select * from "UniNostra".accettaVoti('14');
		--call "UniNostra".registraVotoEsame('4','1','38',30,true);
		
--Funzione che dato l'id utente di uno studente ritorna tutte le sue carriere passate 
--Parametri : idUtente (integer)
--Eccezioni : se l'id inserito non è di uno studente 
			
	CREATE OR REPLACE FUNCTION "UniNostra".visualizzaExCarriere(
			  idU integer
			)
			RETURNS TABLE (
				matricola integer,
				nome varchar(50),
				cognome varchar(100),
				stato tipoSatoExStudente,
				votoLaur "UniNostra".votoLaurea ,
				cdl varchar(10),
				dataiscrizione date,
				datarimozione date,
				inCorso bool				
			)
			LANGUAGE plpgsql
			AS $$
			declare 
				begin	
					perform * from "UniNostra".utente u where u.idutente = idU and u.tipo = 'Studente';
					if not found then 
						raise exception 'l^id inserito non appartiene ad uno studente';
					end if;
				
					RETURN QUERY
						select e.matricola , u.nome , u.cognome, e.stato , e."votolaurea" , e.codicecorso , e.annoiscrizione ,e.datarimozione, e.incorso
						from "UniNostra".utente u inner join "UniNostra".exstudente e on u.idutente = e.idutente 
						where u.idutente = idU
						order by e.datarimozione asc ;
				 END;
			 $$;
	
--Funzione che data la matricola di un ex-studente, ritorna la sua carriera passata. 
--Parametri : matricola (integer)
--Eccezioni : la matricola inserita non appartiene ad un ex-studente 		
	
	CREATE OR REPLACE FUNCTION "UniNostra".visualizzaExVotiValidi(
		  mat integer
		)
		RETURNS TABLE (
			codiceinsegnamento integer,
			nomeIns varchar(50),
			cfu integer,
			cdl varchar(10),
			nome varchar(50),
			cognome varchar(100),
			dataesame date,
			votoE "UniNostra".voto,
			lode bool,
			statoStudente tipoStatoVoto  			
		)
		LANGUAGE plpgsql
		AS $$
		declare 
			begin	
				
				perform * from "UniNostra".exstudente e where e.matricola = mat;
				if not found then 
					raise exception 'l^id inserito non appartiene ad un ex studente';
				end if;
			
				RETURN QUERY
					select a.codiceinsegnamento, i.nome , i.cfu , a.cdl , u.nome , u.cognome , a.dataesame , s.votoesame , s.islode , s.stato 
					from "UniNostra".storicovalutazioni s inner join "UniNostra".appello a on a.idappello = s.idappello  inner join "UniNostra".insegnamento i on i.codice = a.codiceinsegnamento inner join "UniNostra".utente u on u.idutente = i.iddocente 
					where s.matricola = mat and s.stato = 'Accettato'
					order by a.dataesame ;
			 END;
		 $$;
	
--Funzione che data la matricola di un ex-studente, ritorna la sua carriera passata completa. 
--Parametri : matricola (integer)
--Eccezioni : la matricola inserita non appartiene ad un ex-studente 
	
	CREATE OR REPLACE FUNCTION "UniNostra".visualizzaExVoti(
		  mat integer
		)
		RETURNS TABLE (
			codiceinsegnamento integer,
			nomeIns varchar(50),
			cfu integer,
			cdl varchar(10),
			nome varchar(50),
			cognome varchar(100),
			dataesame date,
			votoE "UniNostra".voto,
			lode bool,
			statoStudente tipoStatoVoto  			
		)
		LANGUAGE plpgsql
		AS $$
		declare 
			begin	
				
				perform * from "UniNostra".exstudente e where e.matricola = mat;
				if not found then 
					raise exception 'l^id inserito non appartiene ad un ex studente';
				end if;
			
				RETURN QUERY
					select a.codiceinsegnamento, i.nome , i.cfu , a.cdl , u.nome , u.cognome , a.dataesame , s.votoesame , s.islode , s.stato 
					from "UniNostra".storicovalutazioni s inner join "UniNostra".appello a on a.idappello = s.idappello  inner join "UniNostra".insegnamento i on i.codice = a.codiceinsegnamento inner join "UniNostra".utente u on u.idutente = i.iddocente 
					where s.matricola = mat
					order by a.dataesame ;
			 END;
		 $$;
			
	select * from "UniNostra".storicovalutazioni s 
	select * from "UniNostra".exstudente e 
	select * from "UniNostra".visualizzaExVoti('2');

--funzione che permmete di indirizzare nelle corrette pagine uno studente oppure un ex-studente
--Parametri : idutente (integer)

	CREATE OR REPLACE FUNCTION "UniNostra".isStudente(
		  idU integer
		)
		RETURNS boolean 
		LANGUAGE plpgsql
		AS $$
		declare 
			begin		
				perform * from "UniNostra".exstudente e 
				where e.idutente = idU and not exists (
					select *
					from "UniNostra".studente s 
					where s.idutente = idU
				
				);
				if found then 
					return false ; 
				else 
					return true;
				end if;
			 END;
		 $$;

		select * from "UniNostra".isStudente('14');

--Funzione che visualizza il profilo di un ex studente 
--Parmateri : idUtnete (integer)
--Eccezioni : l'id inserito non è di un ex studente
	
	CREATE OR REPLACE FUNCTION "UniNostra".visualizzaProfiloEx(
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
		    cognome varchar(100),
		    stato tipoSatoExStudente,
		    votoLaur "UniNostra".votoLaurea
		)
		LANGUAGE plpgsql
		AS $$
		declare 
			begin	
				
				perform * from "UniNostra".utente u inner join "UniNostra".exstudente e on e.idutente = u.idutente ;
				if not found then 
					raise exception 'L^id inserito non appartiene ad un ex studente';
				end if;
				
				RETURN QUERY
					select e.matricola , e.telefono , e.indirizzoresidenza , e.annoiscrizione , e.incorso , e.codicecorso , e.nome , e.cognome, e.stato , e."votolaurea" 
					from "UniNostra".exstudente e 
					where e.idutente = idU 
					order by e.datarimozione desc  limit 1;
			 END;
		 $$;
		
	--select * from "UniNostra".visualizzaProfiloEx('14')

--Funzione che dato l'id di uno studente permette di visualizzare gli insegnamenti del suo cdl 
--Parametri : idUtente(integer)
--Eccezioni : se l'id inserito non è di uno studente 
		
	CREATE OR REPLACE FUNCTION "UniNostra".visualizzaCdl(
		  idU integer
		)
		RETURNS TABLE (
			codiceIns integer,
			nomeIns varchar(50),
			cfu integer,
			codiceCdl varchar(10),
			annoErogazione  annoEsame,
			descrizione varchar(200),
			nome varchar(50),
			cognome varchar(100)
			
		)
		LANGUAGE plpgsql
		AS $$
		declare 
			mat integer;
			idCdl varchar(10);
			begin	
				
				select s.matricola,s.idcorso into mat,idCdl from "UniNostra".studente s where s.idutente = idU;
				if mat is null then 
					raise exception 'l^id inserito non appartiene ad uno studente';
				end if;
				
				RETURN QUERY
					select i.codice , i.nome , i.cfu, p.codicecorso,p.annoerogazione ,i.descrizione , u.nome , u.cognome 
					from "UniNostra".pianostudi p inner join "UniNostra".insegnamento i on i.codice = p.codiceinsegnamento inner join "UniNostra".utente u on u.idutente = i.iddocente  
					where p.codicecorso = idCdl 
					order by p.annoerogazione ;
			 END;
		 $$;

	--select * from "UniNostra".visualizzaCdl('11')
		
--Funzione che dato l'id di uno studente, restituisce informazioni sul suo corso di laurea
--Parametri : idUtente(integer)
--Eccezioni : se l'id inserito non è di uno stidente 

	CREATE OR REPLACE FUNCTION "UniNostra".visualizzaInfoCdl(
		  idU integer
		)
		RETURNS TABLE (
			codiceCdl varchar(10),
			nomeCdl varchar(50),
			descrizione varchar(200),
			isAttivo bool,
			durata tipoCorsoLaurea 
		)
		LANGUAGE plpgsql
		AS $$
		declare 
			mat integer;
			idCdl varchar(10);
			begin	
				
				select s.matricola,s.idcorso into mat,idCdl from "UniNostra".studente s where s.idutente = idU;
				if mat is null then 
					raise exception 'l^id inserito non appartiene ad uno studente';
				end if;
				
				RETURN QUERY
					select c.codice , c.nome , c.descrizione , c.isattivo , c.durata 
					from "UniNostra".corsodilaurea c 
					where c.codice = idCdl;
			 END;
		 $$;

	select * from "UniNostra".visualizzaInfoCdl('11');

--Funzione che dato l'id di un insegnamento e del cdl a cui fa riferimento restituisce tutte le propredeuticità per quel corso 
--Parametri : idCdl (varchar(10), idIns (integer)
--Eccezioni : se il cdl non esiste
--            se l'insegnamento non è in quel cdl 

	CREATE OR REPLACE FUNCTION "UniNostra".visualizzaProp(
		  idCdl varchar(10), idIns integer
		)
		returns text 
		LANGUAGE plpgsql
		AS $$
		declare 
			prop text;
			ins integer;
			nomeIns varchar(50);
			begin	
				
				perform * from "UniNostra".corsodilaurea c where c.codice = idCdl;
				if not found then 
					raise exception 'l^insegnamento non esite';
				end if;
			
				perform * from "UniNostra".pianostudi p where p.codicecorso = idCdl and p.codiceinsegnamento = idIns;
				if not found then 
					raise exception 'l^insegnamento con codice %, non esite nel cdl %',idIns,idCdl;
				end if;
			
				for ins in select p.prop from "UniNostra".propedeuticita p where p.esame = idIns and p.codicelaurea = idCdl
					loop 
						select i.nome into nomeIns from "UniNostra".insegnamento i where i.codice = ins ;
						if prop is null then 
							prop := nomeIns;
						else 
							prop := CONCAT (prop,', ', nomeIns);
						end if;
					end loop;
				return prop; 
			 END;
		 $$;

	select * from "UniNostra".visualizzaProp('FX101','6');
	select * from "UniNostra".propedeuticita p 

--Funzione che dato il codiced di un corso di laurea, restituisce tuti i suoi insegnamenti 
--Parametri : idCdl (varchar(10)
--Eccezioni : l'id del cordo di laurea inserito non è valido 
	
	CREATE OR REPLACE FUNCTION "UniNostra".visualizzaTuttiCdl(
		  idCdl varchar(10)
		)
		RETURNS TABLE (
			codiceIns integer,
			nomeIns varchar(50),
			cfu integer,
			codiceCdl varchar(10),
			annoErogazione  annoEsame,
			descrizione varchar(200),
			nome varchar(50),
			cognome varchar(100)
		)
		LANGUAGE plpgsql
		AS $$
		declare 
			begin	
				
				perform * from "UniNostra".corsodilaurea c where c.codice = idCdl;
				if not found then 
					raise exception 'il cdl inserito %, non esiste',idCdl;
				end if;
			
				perform * from "UniNostra".corsodilaurea c where c.codice = idCdl and c.isattivo = false ;
				if found then 
					raise exception 'il cdl selezionato % non contiene insegnamenti, è inattivo',idCdl;
				end if;
				
				RETURN QUERY
					select i.codice, i.nome  , i.cfu, p.codicecorso , p.annoerogazione , i.descrizione , u.nome , u.cognome 
					from "UniNostra".pianostudi p inner join "UniNostra".insegnamento i on p.codiceinsegnamento = i.codice inner join "UniNostra".utente u on u.idutente = i.iddocente 
					where p.codicecorso = idCdl
					order by p.annoerogazione ;
			 END;
		 $$;

	--select * from "UniNostra".visualizzaTuttiCdl('FX101');
	--select * from "UniNostra".corsodilaurea c ;

--Funzione che restituisce tutti gli id dei corsi di laurea presenti 
--Parametri : nessuno 

	CREATE OR REPLACE FUNCTION "UniNostra".visualizzaidCdl()
	RETURNS TABLE (
		codiceCdl varchar(10)
	)
	LANGUAGE plpgsql
	AS $$
		begin	
			RETURN QUERY
				select c.codice 
				from "UniNostra".corsodilaurea c  
				order by c.codice ;
		 END;
	 $$;

	--select * from "UniNostra".visualizzaidCdl();
	select * from "UniNostra".utente u 
	
	
--FUNZIONI PER I DOCENTI 
	
--updatare le password dei docenti con l'hash
select * from "UniNostra".docente d 
select * from "UniNostra".utente u 
select * from "UniNostra".insegnamento i 
update "UniNostra".utente set "password" = md5('1234')::varchar(20) where idutente = '1'



--Funzione che dato l'id di un docente permette di visualizzarne le informazioni 
--Parametri : idUtente (integer)
--Eccezioni : se l'id insertio non appartien ad un utente

	CREATE OR REPLACE FUNCTION "UniNostra".profiloDocente(
		idDoc integer 
	)
	RETURNS TABLE (
		nome varchar(50),
		cognome varchar(100),
		indUfficio varchar(100),
		telefono varchar(10),
		numDocenze integer,
		docenze text
	)
	LANGUAGE plpgsql
	AS $$
	declare
		docenze text;
		nomeIns text;
		countIns integer;
		begin	
			
			perform * from "UniNostra".docente d where d.idutente = idDoc;
			if not found then 
				raise exception 'l^id inserito non appartiene ad un docente';
			end if;
		
			countIns :=0;
			for nomeIns in select i.nome from "UniNostra".insegnamento i where i.iddocente  = idDoc
				loop 
					if countIns = 0 then 
						docenze := nomeIns;
					else 
						docenze := CONCAT (docenze ,', ', nomeIns);
					end if;
					countIns := countIns + 1;
				end loop;
		
		
			RETURN QUERY
				select u.nome , u.cognome ,d.indirizzoufficio, d.cellulareinterno, countIns, docenze
				from "UniNostra".docente d inner join "UniNostra".utente u on d.idutente = u.idutente 
				where d.idutente = idDoc;
		 END;
	 $$;
	--select * from "UniNostra".profiloDocente('24')
	
--Funzione che dato l'id di un docente ritorna i cdl in cui opera
--Parametri idUtente(integer)
--Eccezioni : se l'id inserito non appartiene ad un docente
	
	CREATE OR REPLACE FUNCTION "UniNostra".cdlDocente(
		idDoc integer 
	)
	RETURNS TABLE (
		cdl varchar(10)
	)
	LANGUAGE plpgsql
	AS $$
		begin	
			
			perform * from "UniNostra".docente d where d.idutente = idDoc;
			if not found then 
				raise exception 'l^id inserito non appartiene ad un docente';
			end if;
		
			RETURN QUERY
				select distinct p.codicecorso 
				from "UniNostra".insegnamento i inner join "UniNostra".pianostudi p on i.codice = p.codiceinsegnamento 
				where i.iddocente = idDoc;
		 END;
	 $$;
	
	--select * from "UniNostra".cdlDocente('24');
	
--Funzione che dato il codiced di un corso di laurea, restituisce tuti i suoi insegnamenti tenuti dal docente selezionato
--Parametri : idCdl (varchar(10)
--Eccezioni : l'id del cordo di laurea inserito non è valido 
	
	CREATE OR REPLACE FUNCTION "UniNostra".visualizzaCdlDoc(
		  idCdl varchar(10), idDoc integer
		)
		RETURNS TABLE (
			codiceIns integer,
			nomeIns varchar(50),
			cfu integer,
			codiceCdl varchar(10),
			annoErogazione  annoEsame,
			descrizione varchar(200),
			nome varchar(50),
			cognome varchar(100)
		)
		LANGUAGE plpgsql
		AS $$
		declare 
			begin	
				
				perform * from "UniNostra".corsodilaurea c where c.codice = idCdl;
				if not found then 
					raise exception 'il cdl inserito %, non esiste',idCdl;
				end if;
			
				perform * from "UniNostra".corsodilaurea c where c.codice = idCdl and c.isattivo = false ;
				if found then 
					raise exception 'il cdl selezionato % non contiene insegnamenti, è inattivo',idCdl;
				end if;
				
				RETURN QUERY
					select i.codice, i.nome  , i.cfu, p.codicecorso , p.annoerogazione , i.descrizione , u.nome , u.cognome 
					from "UniNostra".pianostudi p inner join "UniNostra".insegnamento i on p.codiceinsegnamento = i.codice inner join "UniNostra".utente u on u.idutente = i.iddocente 
					where p.codicecorso = idCdl and i.iddocente = idDoc
					order by p.annoerogazione ;
			 END;
		 $$;
		
	--select * from "UniNostra".visualizzaCdlDoc('FX101','4');

--Dato l'id di un docente ritorna il numero dei suoi insegnameni 
--Parametri : idDocente (integer)
--Eccezioni : se l'id inserito non è di un docente 

	CREATE OR REPLACE FUNCTION "UniNostra".numInsegnamenti(
		  idDoc integer
		)
		RETURNS TABLE (
			numIns bigint
		)
		LANGUAGE plpgsql
		AS $$
			begin	
				
				perform * from "UniNostra".docente d where d.idutente = idDoc;
				if not found then 
					raise exception 'l^id inserito non appartiene ad un docente';
				end if;
				
				RETURN QUERY
					select count(distinct p.codiceinsegnamento) 
					from "UniNostra".insegnamento i inner join "UniNostra".pianostudi p on i.codice = p.codiceinsegnamento 
					where i.iddocente = idDoc;
			 END;
		 $$;
		
	--select * from "UniNostra".numInsegnamenti('24');
		
--Funzione che dato l'id di un docente, restituisce gli id degli insegnameni che detiene 
--Parametro : idUtente (integer)
--Eccezioni : l'id utente inserito non è di un docente 		

		CREATE OR REPLACE FUNCTION "UniNostra".idCorsiDoc(
		  idDoc integer
		)
		RETURNS TABLE (
			idIns integer,
			nome varchar(50)
		)
		LANGUAGE plpgsql
		AS $$
			begin	
				
				perform * from "UniNostra".docente d where d.idutente = idDoc;
				if not found then 
					raise exception 'l^id inserito non appartiene ad un docente';
				end if;
				
				RETURN QUERY
					select distinct p.codiceinsegnamento, i.nome
					from "UniNostra".insegnamento i inner join "UniNostra".pianostudi p on i.codice = p.codiceinsegnamento 
					where i.iddocente = idDoc;
			 END;
		 $$;
		
		
		--select * from "UniNostra".idCorsiDoc('1')
		
--Funzione che dato l'id di un docente restituisce gli appelli aperti
--Parametri : idDocente (integer)
--Eccezioni : se l'id inserito non appartiene ad un docente
		
	CREATE OR REPLACE FUNCTION "UniNostra".appelliApertiDoc(
		  idDoc integer
		)
		RETURNS TABLE (
			idApp integer,
			idIns integer,
			nomeIns varchar(50),
			cfu integer,
			cdl varchar(10),
			annoErogazione annoEsame,
			dataEsame date,
			oraInizio time,
			oraFine time,
			aula varchar(50),
			note varchar(200),
			stato tipostatoAppello
		)
		LANGUAGE plpgsql
		AS $$
			begin	
				
				perform * from "UniNostra".docente d where d.idutente = idDoc;
				if not found then 
					raise exception 'l^id inserito non appartiene ad un docente';
				end if;
				
				RETURN QUERY
					select distinct a.idappello ,a.codiceinsegnamento,i2.nome , i2.cfu ,a.cdl,p.annoerogazione ,a.dataesame , a.orainizio ,a.orafine ,a.aula ,a.note , a.statoappello 
					from "UniNostra".appello a inner join "UniNostra".insegnamento i2 on a.codiceinsegnamento = i2.codice inner join "UniNostra".pianostudi p on p.codiceinsegnamento = i2.codice and a.cdl = p.codicecorso  
					where i2.iddocente = idDoc and a.statoappello = 'aperto'
					order by a.dataesame desc;
			 END;
		 $$;
	
	--select * from "UniNostra".appelliApertiDoc('1');
		
--Funzione che dato l'id di un appello ritorna il numero di iscritti
--Parametri : idAppello (integer)
--Eccezioni : se l'id della appello non esite 
	
	CREATE OR REPLACE FUNCTION "UniNostra".numIscrittiA(
		  idApp integer
		)
		RETURNS TABLE (
			numIscritti bigint
		)
		LANGUAGE plpgsql
		AS $$
			begin	
				
				perform * from "UniNostra".appello a where a.idappello = idApp;
				if not found then 
					raise exception 'l^appello non esiste';
				end if;
				
				RETURN QUERY
					select count(i.matricola)
					from "UniNostra".iscrizioneesame i 
					where i.id = idApp and i.stato = 'Iscritto';
			 END;
		 $$;
		
	--select * from "UniNostra".numIscrittiA('58')
	select * from "UniNostra".appello a where a.statoappello = 'aperto'
		
--Funzione che dato l'id di un appello ritorna tutti gli studenti iscritti
--Paremtri : idApp (integer)
--Eccezioni : se l'id dell'appello non esiste 
	
	
	CREATE OR REPLACE FUNCTION "UniNostra".iscrittiAppello(
		  idApp integer
		)
		RETURNS TABLE (
			matricola integer,
			nome varchar(50),
			cognome varchar(100),
			stato tipoStatoVoto,
			cdl varchar(10)
		)
		LANGUAGE plpgsql
		AS $$
			begin	
				perform * from "UniNostra".appello a where a.idappello = idApp;
				if not found then 
					raise exception 'l^appello non esiste';
				end if;
				
				RETURN QUERY
					select i.matricola , u.nome , u.cognome , i.stato , s.idcorso 
					from "UniNostra".iscrizioneesame i inner join "UniNostra".studente s on i.matricola = s.matricola inner join "UniNostra".utente u on u.idutente = s.idutente
					where i.id = idApp and i.stato = 'Iscritto'
					order by i.matricola ;
			 END;
		 $$;
		
--Funzione che permette di visualizzare gli appelli chiusi di un docente 
--Parametri : idDodcente (integer)
--Eccezioni : se l'id inserito non appartiene al docente 
		
	CREATE OR REPLACE FUNCTION "UniNostra".appelliChiusiDoc(
		  idDoc integer
		)
		RETURNS TABLE (
			idApp integer,
			idIns integer,
			nomeIns varchar(50),
			cfu integer,
			cdl varchar(10),
			annoErogazione annoEsame,
			dataEsame date,
			oraInizio time,
			oraFine time,
			aula varchar(50),
			note varchar(200),
			stato tipostatoAppello
		)
		LANGUAGE plpgsql
		AS $$
			begin	
				
				perform * from "UniNostra".docente d where d.idutente = idDoc;
				if not found then 
					raise exception 'l^id inserito non appartiene ad un docente';
				end if;
				
				RETURN QUERY
					select distinct a.idappello ,a.codiceinsegnamento,i2.nome , i2.cfu ,a.cdl,p.annoerogazione ,a.dataesame , a.orainizio ,a.orafine ,a.aula ,a.note , a.statoappello 
					from "UniNostra".appello a inner join "UniNostra".insegnamento i2 on a.codiceinsegnamento = i2.codice inner join "UniNostra".pianostudi p on p.codiceinsegnamento = i2.codice and a.cdl = p.codicecorso  
					where i2.iddocente = idDoc and a.statoappello = 'chiuso'
					order by a.dataesame asc;
			 END;
		 $$;
		
		select * from "UniNostra".appelliChiusiDoc('1');
		
		select * from "UniNostra".iscrizioneesame i 
		
		select * from "UniNostra".appello a inner join "UniNostra".insegnamento i on a.codiceinsegnamento = i.codice where a.idappello = '85';
	
		select * from "UniNostra".propedeuticita p 
		--53, 38, 11
		update "UniNostra".iscrizioneesame set stato = 'Iscritto', votoesame = null ,islode  = null where id = '53'

		
		
		
		
		
		
		
		
		
	
		
		