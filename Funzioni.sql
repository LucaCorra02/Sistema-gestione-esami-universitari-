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

--select * from "UniNostra".profiloStudente('17');
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
		idCdl varchar(10);
		begin	
			select s.matricola, s.idcorso into mat,idCdl from "UniNostra".studente s where s.idutente = idU;
			if mat is null then 
				raise exception 'l^id inserito non appartiene ad uno studente';
			end if;
			
			RETURN QUERY
				select a.idappello, a.codiceinsegnamento,i2.nome, i2.cfu,a.cdl ,u.nome ,u.cognome ,a.dataesame, i.votoesame, i.islode , i.stato  
				from "UniNostra".iscrizioneesame i inner join "UniNostra".appello a on a.idappello = i.id inner join "UniNostra".insegnamento i2 on i2.codice = a.codiceinsegnamento inner join "UniNostra".utente u on u.idutente = i2.iddocente 
				where i.matricola = mat and i.stato = 'Accettato' and exists (
					select *
					from "UniNostra".pianostudi p 
					where p.codicecorso = idCdl and p.codiceinsegnamento = i2.codice 
				)
				order by a.dataesame desc ;
		 END;
	 $$;
	
	select * from "UniNostra".utente u 
	select * from "UniNostra".iscrizioneesame i 
	select * from "UniNostra".visualizzaEsamiPassati('11');

	select * from "UniNostra".propedeuticita p 

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
		
		
		--select * from "UniNostra".utente u 
		--select * from "UniNostra".iscrizioneesame i 
		--update "UniNostra".iscrizioneesame set votoesame = null,stato='Iscritto',islode = null where id = '53'
		--delete from "UniNostra".iscrizioneesame i where matricola = '11'
		--insert into "UniNostra".iscrizioneesame (matricola,id,votoesame,stato,islode)
		--values('11','53',null,'Iscritto',null);
	
--Funzione che dato l'id di un appello ritorna il numero di studenti che lo hanno fatto 
--Parametri : idAppello (integer)
--Eccezioni : l'id dell'appello inserito non esiste 
	
	CREATE OR REPLACE FUNCTION "UniNostra".numPartecipantiApp(
		  idApp integer
		)
		RETURNS TABLE (
			num bigint
		)
		LANGUAGE plpgsql
		AS $$
			begin	
				
				perform * from "UniNostra".appello a where a.idappello = idApp;
				if not found then 
					raise exception 'l^appello inserito non esiste';
				end if;
				
				RETURN QUERY
					select count(i.id)
					from "UniNostra".iscrizioneesame i
					where i.id = idApp;
			 END;
		 $$;	
		
	CREATE OR REPLACE FUNCTION "UniNostra".numPartecipantiStorico(
		  idApp integer
		)
		RETURNS TABLE (
			num bigint
		)
		LANGUAGE plpgsql
		AS $$
			begin	
				
				perform * from "UniNostra".appello a where a.idappello = idApp;
				if not found then 
					raise exception 'l^appello inserito non esiste';
				end if;
				
				RETURN QUERY
					select count(s.id)
					from "UniNostra".storicovalutazioni s 
					where s.idappello = idApp;
			 END;
		 $$;	
	
--Funzione che dato l'id di un appello ritorna gli studenti partecipanti studenti
--Paremetri : idAppello (integer)
--Eccezioni : -
	
	CREATE OR REPLACE FUNCTION "UniNostra".studPartecipanti(
		  idApp integer
		)
		RETURNS TABLE (
			matricola integer,
			nome varchar(50),
			cognome varchar(100),
			cdl varchar(10),
			votoE "UniNostra".voto,
			isLode bool,
			stato tipoStatoVoto
		)
		LANGUAGE plpgsql
		AS $$
			begin	
				RETURN QUERY
					select i.matricola,u.nome ,u.cognome,s.idcorso,i.votoesame ,i.islode, i.stato 
					from "UniNostra".iscrizioneesame i inner join "UniNostra".studente s on s.matricola = i.matricola inner join "UniNostra".utente u on u.idutente = s.idutente 
					where i.id = idApp;
			 END;
		 $$;	
	
--Funzione che dato l'id di un appello ritorna gli studenti partecipanti nello storico
--Paremetri : idAppello (integer)
--Eccezioni : -	
	
	CREATE OR REPLACE FUNCTION "UniNostra".studPartecipantiStorico(
		  idApp integer
		)
		RETURNS TABLE (
			matricola integer,
			nome varchar(50),
			cognome varchar(100),
			cdl varchar(10),
			votoE "UniNostra".voto,
			isLode bool,
			stato tipoStatoVoto
		)
		LANGUAGE plpgsql
		AS $$
			begin
				RETURN QUERY
					select e.matricola ,e.nome ,e.cognome, e.codicecorso , s.votoesame , s.islode , s.stato 
					from "UniNostra".storicovalutazioni s inner join "UniNostra".exstudente e on s.matricola = e.matricola
					where s.idappello = idApp;
			 END;
		 $$;	
		
	--select * from "UniNostra".storicovalutazioni s 
	--select * from "UniNostra".studPartecipantiStorico('41');
	--select * from "UniNostra".studPartecipanti('41');
		
--Funzione che dato l'id di un docente ritorna gli insegnamenti che ha tenuto in precedenza
--Parametri : idDocente (integer)
--Eccezioni : l'id inserito non appartiene ad un docente 
		
	CREATE OR REPLACE FUNCTION "UniNostra".storicoInsegnamenti(
	  idDoc integer
	)
	RETURNS TABLE (
		idIns integer,
		nomeIns varchar(100),
		cfu integer,
		annoInizio integer,
		annoFine integer,
		nome varchar(50),
		cognome varchar(100)
	)
	LANGUAGE plpgsql
	AS $$
		begin
			
			perform * from "UniNostra".docente d where d.idutente = idDoc; 
			if not found then 
				raise exception 'l^id inserito non appartiene ad un docente';
			end if;
			
			RETURN QUERY
				select e.codiceinsegnamento ,e.nome, e.cfu ,e.annoinizio ,e.annofine , u.nome ,u.cognome 
				from "UniNostra".exinsegnamento e inner join "UniNostra".utente u on e.iddocente = u.idutente 
				where e.iddocente = idDoc;
		 END;
	 $$;	
	
	--select u.nome , u.cognome , i.codice  from "UniNostra".insegnamento i inner join "UniNostra".utente u on i.iddocente = u.idutente 
	--select * from "UniNostra".storicoInsegnamenti('4');
	
	select * from "UniNostra".iscrizioneesame i 
	select * from "UniNostra".utente u 
	
--FUNZIONI SEGRETARIO
	
--Funzione che dato l'id di un segretario, ritorna alcune informazioni riguardanti il suo profilo
--Parametri : idSegretario 
--Eccezioni : l'id inserito non appartiene ad un segretario 
	
		
	CREATE OR REPLACE FUNCTION "UniNostra".profiloSegretario(
	  idSeg integer
	)
	RETURNS TABLE (
		nome varchar(50),
		cognome varchar(100),
		cf varchar(16),
		indirizzo varchar(100),
		nomeDip varchar(50),
		cellulareInterno varchar(50)
	)
	LANGUAGE plpgsql
	AS $$
		begin
			perform * from "UniNostra".segretario s where s.idutente = idSeg;
			if not found then
				raise exception 'l^id inserito non appartiene ad un segretario';
			end if;
			
			RETURN QUERY
				select u.nome, u.cognome,u.cf,s.indirizzosegreteria , s.nomedipartimento , s.cellulareinterno 
				from "UniNostra".segretario s inner join "UniNostra".utente u on s.idutente = u.idutente 
				where s.idutente = idSeg;
		 END;
	 $$;

	select * from "UniNostra".profiloSegretario('13');
	
--Funzione che ritorna tutti i corsi di laurea presenti nel db 
--Parametri : idUtente (integer)
--Eccezzioni : id utente non esistente 

	CREATE OR REPLACE FUNCTION "UniNostra".visualizzaTuttiCorsi(
	  idU integer
	)
	RETURNS TABLE (
		codice varchar(10),
		nome varchar(50),
		descrizione varchar(200),
		durata tipoCorsoLaurea,
		isAttivo bool
	)
	LANGUAGE plpgsql
	AS $$
	declare 
		begin
			perform * from "UniNostra".utente u where u.idutente = idU;
			if not found then 
				raise exception 'utente non autorizzato';
			end if;
		
			RETURN QUERY
				select  c.codice ,c.nome ,c.descrizione , c.durata ,c.isattivo  
				from "UniNostra".corsodilaurea c ;
		 END;
	 $$;

	select * from "UniNostra".visualizzaTuttiCorsi('13');

--Funzione che dato l'id di un cdl, restituisce il numero di studenti iscritti ad esso 
--Parametri : idCdl (varchar(10)
--Eccezione : se id cdl non esiste 

	CREATE OR REPLACE FUNCTION "UniNostra".numIscrizioniCdl(
	  idCdl varchar(10)
	)
	returns bigint
	LANGUAGE plpgsql
	AS $$
	declare 
		n bigint;
		begin
			perform * from "UniNostra".corsodilaurea c where c.codice = idCdl;
			if not found then 
				raise exception 'il cdl %, non esiste',idCdl;
			end if;
		
			select count(s.idcorso) into n
			from "UniNostra".studente s 
			where s.idcorso = idCdl
			group by s.idcorso; 
		
			if n is null then
				return 0;
			else 
				return n;
			end if;
		 END;
	 $$;

	
	--select * from "UniNostra".numIscrizioniCdl('FX102');

--Funzione che dato l'id di un cdl, ritorna tutti gli insegnamenti che possono essere aggiunti a quel cdl (non ancora presenti)
--Parametri : idCdl (varchar(10))
--Eccezioni : il cdl inserito non esiste 
	
	CREATE OR REPLACE FUNCTION "UniNostra".insegnamentiDisponibili(
	  idCdl varchar(10)
	)
	returns TABLE(
		codice integer,
		nomeIns varchar(50),
		descrizione varchar(200),
		cfu integer,
		nome varchar(50),
		cognome varchar(100)
	)
	LANGUAGE plpgsql
	AS $$
	declare 
		begin 
		 
			perform * from "UniNostra".corsodilaurea c where c.codice = idCdl;
			if not found then 
				raise exception 'Nessun corso di laurea con codice %',idCdl;
			end if;
		
			return QUERY
				select i.codice , i.nome , i.descrizione ,i.cfu , u.nome ,u.cognome 
				from "UniNostra".insegnamento i inner join "UniNostra".utente u on i.iddocente = u.idutente 
				where not exists (
					select *
					from "UniNostra".pianostudi p 
					where p.codiceinsegnamento = i.codice and p.codicecorso = idCdl
				);
		END;
	 $$;
	--select * from "UniNostra".insegnamentiDisponibili('')

--Funzione che dato il codice di un corso di laurea ritorna se è attivo o meno 
--Parametri : idCdl (varchar(10)
--Eccezioni : il corso di laura inserito non esiste 
	
	CREATE OR REPLACE FUNCTION "UniNostra".cdlIsAttivo(
	  idCdl varchar(10)
	)
	returns bool
	LANGUAGE plpgsql
	AS $$
	declare 
		idC varchar(10);
		attivo bool;
		begin 
			select c.codice ,c.isattivo into idC, attivo from "UniNostra".corsodilaurea c where c.codice = idCdl;
			if idC is null then 
				raise exception 'il corso inserito %, non esiste',idCdl;
			end if;
			
			if attivo = true then 
				return true;
			else 
				return false;
			end if;
		END;
	 $$;
	--select * from "UniNostra".cdlIsAttivo('MD102')
	
	
--Funzione che dato l'id di un insegnamento ne ritorna le sue informazioni 
--Parametri : idInse (integer)
--Eccezioni : l'insegnamento non esiste 
	
	CREATE OR REPLACE FUNCTION "UniNostra".infoIns(
	  idIns integer
	)
	returns TABLE(
		descrizione varchar(200),
		cfu integer,
		nome varchar(50),
		cognome varchar(100),
		idDoc integer,
		codice integer,
		nomeIns varchar(50)
	)
	LANGUAGE plpgsql
	AS $$
	declare 
		begin 
			perform * from "UniNostra".insegnamento i where i.codice = idIns;
			if not found then 
				raise exception 'nessun insegnamento con id %',idIns;
			end if;
		
			return QUERY
				select i.descrizione ,i.cfu ,u.nome ,u.cognome ,u.idutente , i.codice , i.nome 
				from "UniNostra".insegnamento i inner join "UniNostra".utente u on i.iddocente = u.idutente  
				where i.codice = idIns ;
		END;
	 $$;
	--select * from "UniNostra".infoIns('10');

--Funzione che dato l'id di un cdl ritorna gli anni possibii per gli esami 
--Parametri : idCdl(varchar 10)

	CREATE OR REPLACE FUNCTION "UniNostra".anniPossibili(
	  idCdl varchar(10)
	)
	RETURNS text[]
	LANGUAGE plpgsql
	AS $$
	declare 
		tipo tipoCorsoLaurea;
		begin 
			select c.durata into tipo from "UniNostra".corsodilaurea c where c.codice = idCdl;
			if tipo = '3' then 
				return array['1','2','3'];
			else
				return array['1','2'];
			end if;
		END;
	 $$;
	
	select * from "UniNostra".anniPossibili('FX102');
	
--Funzione che dato l'id di un utente i docenti a cui puo essere assegnato un insegnamento <3
--Parametri : idUtente(integer)
--Eccezioni : se non esiste nessun utente con tale id

	CREATE OR REPLACE FUNCTION "UniNostra".docentiDisponibili(
	  idU integer
	)
	RETURNS table(
		numInsegnamenti bigint,
		idDoc integer,
		nome varchar(50),
		cognome varchar(100)
	)
	LANGUAGE plpgsql
	AS $$
	declare 
		begin 
			
			perform * from "UniNostra".utente u where u.idutente = idU;
			if not found then 
				raise exception 'non esiste nessun utente con id %',idU;
			end if;
			
			return QUERY
				select count(i.codice), i.iddocente , u.nome ,u.cognome  
				from "UniNostra".insegnamento i inner join "UniNostra".utente u on u.idutente = i.iddocente 
				group by i.iddocente , u.nome ,u.cognome 
				having count(i.codice) < 3; 
		END;
	 $$;
	--select * from "UniNostra".docentiDisponibili('1');
	
	select * from "UniNostra".utente u where u.tipo = 'Docente';
	--24 violetta lonati
	call "UniNostra".inserisciInsegnamento('Medicina Generale','si studiano cose','12','24');
	call "UniNostra".inserisciInsegnamento('Fisica 1','fisica','12','24');
	call "UniNostra".inserisciInsegnamento('Fisica 2','fisica 2','6','24');
	call "UniNostra".inserisciInsegnamento('Fisica 3','fisica 3 difficile','9','24');
	

--Funzione che dato l'id di un insegnamento e il cdl di cui fa parte, restituisce tutti gli insegnamenti di quel corso per inserire le propedeuticità
--Parametri : idCdl Varchar(10), idInsegnamento (integer)
--Eccezioni : nessun corso con il codice specificato come parametro 
--			  Nessun insegnamento con il codice specificato come parametro 

	CREATE OR REPLACE FUNCTION "UniNostra".propDisponibili(
	  idCdl varchar(10), idIns integer
	)
	RETURNS table(
		idInsegnamento integer,
		nomeIns varchar(50),
		annoErogazione annoEsame 
	)
	LANGUAGE plpgsql
	AS $$
	declare 
		begin 
			perform * from "UniNostra".corsodilaurea c where c.codice = idCdl;
			if not found then 
				raise exception 'nessun corso di laurea con id %',idCdl;
			end if;
		
			perform * from "UniNostra".insegnamento i where i.codice = idIns;
			if not found then 
				raise exception 'Nessun insegnamento con id %',idIns;
			end if;
			
			return QUERY
				select i.codice , i.nome , p.annoerogazione 
				from "UniNostra".pianostudi p inner join "UniNostra".insegnamento i on p.codiceinsegnamento = i.codice 
				where p.codicecorso = idCdl and p.codiceinsegnamento <> idIns;
		END;
	 $$;
	
	--select * from "UniNostra".propDisponibili('MD101','10')
	select * from "UniNostra".propedeuticita p 
	

	-- 4 -> 6 tolgo
	-- 6 -> 10  tolgo
	-- 4 -> 10
	select * from "UniNostra".propedeuticita p where p.codicelaurea = 'FX101'
	call "UniNostra".rimuoviPianoStudi('6','FX101');
	
select * from "UniNostra".corsodilaurea c where c.codice,c.nome,c.descrizione,c.durata 
select * from "UniNostra".corsodilaurea c2 ;
delete from "UniNostra".corsodilaurea c3 where c3.isattivo = false;

--Funzione che dato l'id di un utente ( per verificare che sia un accesso autorizzato), ritorna tutti gli insegnamenti presenti.
--Parametri : idUtente (integer)
--Eccezioni : se l'utente inserito non è autorizzato 

	CREATE OR REPLACE FUNCTION "UniNostra".visualizzaTuttiInsegnamenti(
	  idU integer
	)
	RETURNS table(
		idIns integer,
		nomeIns varchar(50),
		descrizione varchar(200),
		cfu integer,
		nomeDoc varchar(50),
		cognomeDoc varchar(50)
	)
	LANGUAGE plpgsql
	AS $$
	declare 
		begin 
			perform * from "UniNostra".utente u where u.idutente = idU;
			if not found then 
				raise exception 'utente non autorizzato';
			end if;
			
			return QUERY
				select i.codice ,i.nome ,i.descrizione ,i.cfu, u.nome ,u.cognome 
				from "UniNostra".insegnamento i  inner join "UniNostra".utente u  on i.iddocente = u.idutente 
				order by i.codice ;
		END;
	 $$;
	
	--select * from "UniNostra".visualizzaTuttiInsegnamenti('122');

--Funzione che dato l'id di un insegnamento ritorna i cdl di cui fa parte 
--Parametri : idInsegnamento integer
--Eccezioni : id non esistente
	
	CREATE OR REPLACE FUNCTION "UniNostra".idCdlPerInsegnamento(
	  idIns integer
	)
	RETURNS text
	LANGUAGE plpgsql
	AS $$
	declare 
		pianoStudiRiga "UniNostra".pianostudi%rowtype;
		idCdl text;
		begin 
			perform * from "UniNostra".insegnamento i where i.codice = idIns;
			if not found then 
				raise exception 'nessun insegnamento con id %',idIns;
			end if;
			
			for pianoStudiRiga in select * from "UniNostra".pianostudi p where p.codiceinsegnamento = idIns 
				loop 
					idCdl := concat(idCdl,' ',pianoStudiRiga.codicecorso); 
				end loop;
			return idCdl;
		END;
	 $$;
	
	--select * from "UniNostra".idCdlPerInsegnamento('101');
	
--Funzione che dato l'id di un cdl ritorna tutti gli studenti iscritti 
--Parametri : idCdl 
--Eccezioni : non esiste nessun corso di laurea con il codice inserito 
	
	CREATE OR REPLACE FUNCTION "UniNostra".visualizzaStudentiCdl(
	  idCdl varchar(10)
	)
	RETURNS table(
		matricola integer,
		nome varchar(50),
		cognome varchar(100),
		telefono varchar(20),
		datanascita date,
		indirizzo varchar(100),
		iscrizione date,
		incorso bool
	)
	LANGUAGE plpgsql
	AS $$
	declare 
		begin 
			
			perform * from "UniNostra".corsodilaurea c where c.codice = idCdl;
			if not found then 
				raise exception 'Nessun cdl con codice %',idCdl;
			end if;
			
			return QUERY
				select s.matricola,u.nome ,u.cognome ,s.telefono ,s.datanascita,s.indirizzoresidenza ,s.annoiscrizione,s.incorso 
				from "UniNostra".studente s inner join "UniNostra".utente u on u.idutente = s.idutente
				where s.idcorso = idCdl;
		END;
	 $$;
	--select * from "UniNostra".visualizzaStudentiCdl('FX102');
	
	
--Funzione che data la matricola di uno studente ritorna le sue informazioni 
--Parametri : matricola studente 
--Eccezioni : la matricola inserita non esisre
	
	CREATE OR REPLACE FUNCTION "UniNostra".visualizzaInfoDisiscrizione(
	  mat integer
	)
	RETURNS table(
		matricola integer,
		nome varchar(50),
		cognome varchar(100),
		email varchar(50),
		cfu varchar(16),
		telefono varchar(20),
		indirizzo varchar(100),
		datanascita date,
		idCorso varchar(10)
	)
	LANGUAGE plpgsql
	AS $$
	declare 
		begin 
			
			perform * from "UniNostra".studente s where s.matricola = mat;
			if not found then 
				raise exception 'nessun studente con matricola %',mat;
			end if;
			
			return QUERY
				select s.matricola,u.nome ,u.cognome,u.email ,u.cf ,s.telefono,s.indirizzoresidenza,s.datanascita,s.idcorso 
				from "UniNostra".studente s inner join "UniNostra".utente u on u.idutente = s.idutente
				where s.matricola = mat;
		END;
	 $$;
	
	--select * from "UniNostra".visualizzaInfoDisiscrizione('10')
	
--Funzione che dato l'id di una matricola restituisce l'id utente associato
--Parametri : matricola (integer)
--Eccezioni : la matricola inserita non esiste
	
	CREATE OR REPLACE FUNCTION "UniNostra".idAssociatoMatricola(
	  mat integer
	)
	RETURNS integer 
	LANGUAGE plpgsql
	AS $$
	declare 
		idU integer;
		begin 
			
			perform * from "UniNostra".studente s where s.matricola = mat;
			if not found then 
				raise exception 'nessun studente con matricola %',mat;
			end if;
			
			select s.idutente into idU from "UniNostra".studente s where s.matricola = mat;
			return idU;
		END;
	 $$;
	
	select * from "UniNostra".idAssociatoMatricola('1');
	
--Funzione che resituisce tutti gli appelli dato l'id di un utente
--Parametri : idUtente integer
--Eccezioni : utente non autorizzato 

	CREATE OR REPLACE FUNCTION "UniNostra".appelliDatoUtente(
	  idU integer
	)
	RETURNS TABLE(
		idappello integer,
		codiceinsegnamento integer,
		nomeIns varchar(50),
		cfu integer,
		nome varchar(50),
		cognome varchar(100),
		dataesame date,
		orainizio time,
		orafine time,
		aula varchar(20),
		cdl varchar(10),
		statoappello tipostatoAppello
	)
	LANGUAGE plpgsql
	AS $$
		begin 
			perform * from "UniNostra".utente u where u.idutente = idU;
			if not found then 
				raise exception 'nessun utente con id %',idU;
			end if;
			
			return QUERY
				select a.idappello ,a.codiceinsegnamento,i.nome ,i.cfu ,u.nome ,u.cognome ,a.dataesame  ,a.orainizio ,a.orafine ,a.aula ,a.cdl ,a.statoappello 
				from "UniNostra".appello a inner join "UniNostra".insegnamento i on a.codiceinsegnamento = i.codice inner join "UniNostra".utente u on u.idutente = i.iddocente 
				order by a.cdl, a.codiceinsegnamento,a.dataesame desc  ;
		END;
	 $$;
		
	select * from "UniNostra".appelliDatoUtente('1')

--Funzione che dato la matricola e l'appello di un esame a cui ha partecipato ritorna le sue informazioni 
--Parametri : matricola intger, idAppello integer 
--Eccezioni : la matricola non ha partecipato all'appello inserito 
	
	CREATE OR REPLACE FUNCTION "UniNostra".esitoStud(
	 	mat integer,idApp integer
	)
	RETURNS TABLE(
		
		nome varchar(50),
		cognome varchar(100),
		matricola integer,
		nomeIns varchar(50),
		cfu integer,
		cdl varchar(10),
		votoEsame "UniNostra".voto
	)
	LANGUAGE plpgsql
	AS $$
	declare
		esito "UniNostra".iscrizioneesame%rowtype;
		begin 
			
			select * into esito from "UniNostra".iscrizioneesame i where i.matricola = mat and i.id = idApp;
			if esito.matricola is null then 
				raise exception 'lo studente con matricola % non è iscritto all^appello %',mat,idApp;
			end if;
			
			return QUERY
				select u.nome ,u.cognome ,i.matricola ,i2.nome , i2.cfu , a.cdl, i.votoesame 
				from "UniNostra".iscrizioneesame i inner join "UniNostra".appello a on a.idappello = i.id inner join "UniNostra".insegnamento i2 on i2.codice = a.codiceinsegnamento inner join "UniNostra".studente s on s.matricola = i.matricola inner join "UniNostra".utente u on u.idutente = s.idutente
				where i.matricola = mat and i.id = idApp;
		END;
	 $$;
		
	--select * from "UniNostra".esitoStud('1','23')
	
--Procedura che data la matricola e l'id ad un appello a cui è iscritta permette di cambiare il voto 
--Parametri : matricola (integer), idAppello (integer), votoEsame ("UniNostra".voto)
--Eccezioni : matricola non iscritta all'appello 
--			  matricola iscritta ma con stato != Accettato
--            voto < 18
	
	CREATE OR REPLACE procedure  "UniNostra".cambiavotoaccettato(
	 	mat integer,idApp integer,votoE "UniNostra".voto, lode bool
	)
	LANGUAGE plpgsql
	AS $$
	declare
		esito "UniNostra".iscrizioneesame%rowtype;
		begin 
			
			select * into esito from "UniNostra".iscrizioneesame i where i.matricola = mat and i.id = idApp;
			if esito.matricola is null then 
				raise exception 'lo studente con matricola % non è iscritto all^appello %',mat,idApp;
			end if;
			
			if esito.stato != 'Accettato' then 
				raise exception 'stato non valido';
			end if;
		
			if votoE < 18 then 
				raise exception 'voto non valido';
			end if;
		
			update "UniNostra".iscrizioneesame i 
			set votoesame = votoE , islode = lode
			where i.matricola = mat and i.id = idApp;
		END;
	 $$;
	

--funzione che ritorna gli studenti che si possono laureare 
--Parametri : idUtente 
--Eccezioni : utente non autorizzato 
	
	CREATE OR REPLACE FUNCTION "UniNostra".studentiprontilaurea(
	 	idCdl varchar(10)
	)
	RETURNS TABLE(
		matricola integer,
		nome varchar(50),
		cognome varchar(100),
		indrizzo varchar(100),
		dataNascita date,
		annoiscrizione date,
		inCorso bool,
		nomeCorso varchar(50),
		idCors varchar(10)
	)
	LANGUAGE plpgsql
	AS $$
	declare
		begin 
			perform * from "UniNostra".corsodilaurea c where c.codice = idCdl;
			if not found then 
				raise exception 'nessun cdl con id %',idCdl;
			end if;
		
			return QUERY
				select s.matricola ,u.nome , u.cognome ,s.indirizzoresidenza , s.datanascita , s.annoiscrizione , s.incorso, c.nome ,s.idcorso 
				from "UniNostra".studente s inner join "UniNostra".utente u on s.idutente = u.idutente inner join "UniNostra".corsodilaurea c on c.codice = s.idcorso 
				where s.idcorso = idCdl and not exists ( 
					select * 
					from "UniNostra".pianostudi p 
					where p.codicecorso = s.idcorso and not exists (
						select * 
						from "UniNostra".iscrizioneesame i inner join "UniNostra".appello a on a.idappello = i.id 
						where i.matricola = s.matricola and a.codiceinsegnamento = p.codiceinsegnamento and a.cdl = s.idcorso and i.stato = 'Accettato'
					)
				);				
		END;
	 $$;
	
	select * from "UniNostra".studentiprontilaurea('MD101');
	
--Funzione che data la matricola di uno studente ritorna la sua media
--parametri : matricola (integer)
--Eccezioni : studente non esistente
	
	CREATE OR REPLACE FUNCTION "UniNostra".mediaStudente(
	 	mat integer
	)
	RETURNS integer
	LANGUAGE plpgsql
	AS $$
	declare
		stud "UniNostra".studente%rowtype;
		votoE "UniNostra".voto;
		cf integer;
		num integer;
		media integer; 
	
		begin 
			select * into stud from "UniNostra".studente s where s.matricola = mat ;
			if stud.matricola is null then 
				raise exception 'nessun studente con id %',mat;
			end if;
		
			media := 0;
			num := 0;
			for votoE,cf in select i.votoesame, i2.cfu from "UniNostra".iscrizioneesame i inner join "UniNostra".appello a on i.id = a.idappello inner join "UniNostra".insegnamento i2 on i2.codice = a.codiceinsegnamento  where i.matricola = mat and a.cdl = stud.idcorso and i.stato = 'Accettato'
				loop 
					media := media + (votoE * cf);
					num := num + cf;
				end loop;
				
			if num = 0 then 
				return 0;
			end if;
			media := media / num;
			return media;
		END;
	 $$;

	select * from "UniNostra".mediaStudente('17');

--Funzione che ritorna tutti gli ex-studenti
--Parametri : idSegretario (integer)
--Eccezioni : utente non autorizzato 

	CREATE OR REPLACE FUNCTION "UniNostra".tuttigliexstudenti(
	 	idU integer
	)
	RETURNS TABLE(
		matricola integer,
		nome varchar(50),
		cognome varchar(100),
		telefono varchar(20),
		indririzzo varchar(100),
		datanascita date,
		annoiscrizione date,
		annorimozione date,
		incorso bool,
		stato tipoSatoExStudente,
		votol "UniNostra".votolaurea,
		idcdl varchar(10),
		nomeins varchar(50)
	)
	LANGUAGE plpgsql
	AS $$
	declare
		begin 
			
			perform * from "UniNostra".segretario u where u.idutente  = idU;
			if not found then 
				raise exception 'utente non autorizzato';
			end if;
			
			return query 
				select e.matricola ,e.nome ,e.cognome, e.telefono ,e.indirizzoresidenza ,e.datanascita ,e.annoiscrizione ,e.datarimozione,e.incorso ,e.stato,e."votolaurea" ,e.codicecorso ,c.nome  
				from "UniNostra".exstudente e inner join "UniNostra".corsodilaurea c on c.codice = e.codicecorso
				order by e.codicecorso , e.stato ,e.datarimozione asc;
		END;
	 $$;

	--select * from "UniNostra".tuttigliexstudenti('13');

--Funzione che dato l'id di un segretario, restituisce tutti i docenti presenti nel database 
--Parametri : idSegretario (integer)
--Eccezioni : utente non autorizzato 
	
	CREATE OR REPLACE FUNCTION "UniNostra".tuttiidocenti(
	 	idU integer
	)
	RETURNS TABLE(
		idUtente integer,
		nome varchar(50),
		cognome varchar(100),
		indirizzoufficio varchar(100),
		cellulareinterno varchar(20)
	)
	LANGUAGE plpgsql
	AS $$
		begin 
				
			perform * from "UniNostra".segretario s where s.idutente = idU;
			if not found then 
				raise exception 'utente non autorizzato';
			end if;

			return QUERY 
				select u.idutente, u.nome , u.cognome , d.indirizzoufficio , d.cellulareinterno 
				from "UniNostra".docente d inner join "UniNostra".utente u on d.idutente = u.idutente;
		END;
	 $$;
	
	select * from "UniNostra".tuttiidocenti('13');
	
--Funzione che dato l'id di un docente ritorna il numero di exdocenze
--Parametri : idDocente (integer)
--Eccezioni : l'id inserito non appartiene ad un docente 	
	
	CREATE OR REPLACE FUNCTION "UniNostra".numexdocenze(
	 	idDoc integer
	)
	returns bigint
	LANGUAGE plpgsql
	AS $$
		declare 
			num bigint;
		begin 

			perform * from "UniNostra".docente d where d.idutente = idDoc;
			if not found then 
				raise exception 'id non di un docente';
			end if;

			num:=0;
			select count(e.iddocente)  into num
			from "UniNostra".docente d inner join "UniNostra".exinsegnamento e on e.iddocente = d.idutente 
			where d.idutente = idDoc;
			
			return num;
		END;
	 $$;
	select * from "UniNostra".exinsegnamento e 
	
	--select * from "UniNostra".numexdocenze('47')
	--1 --24 --4

--Procedura che permette di aggiornare le informazioni di un docente 
--Parametri: idDocente (integer), indirizzo varchar(100), cellulare varchar(10)
--Eccezioni : non esiste nessun docente con id inserito 

	CREATE OR replace procedure "UniNostra".updateinfodocente(
	 	idDoc integer, indirizzo varchar(100), cellulare varchar(10)
	)
	LANGUAGE plpgsql
	AS $$
		declare 
			doc "UniNostra".docente%rowtype;
		begin 

			select * into doc from "UniNostra".docente d where d.idutente = idDoc ;
			if doc.idutente is null then 
				raise exception 'id non di un docente';
			end if;

			if doc.cellulareinterno = cellulare then 
				cellulare := doc.cellulareinterno;
			end if;
			
			if doc.indirizzoufficio = indirizzo then 
				indirizzo := doc.indirizzoufficio;
			end if;
			
			update "UniNostra".docente d set cellulareinterno = cellulare, indirizzoufficio = indirizzo where d.idutente = idDoc;
		END;
	 $$;
	
	call "UniNostra".updateinfodocente('1','sos','393020220')
	select * from "UniNostra".docente d 
	
--Funzione che dato l'id di un segretario, ritorna tutti i segretari presenti nella base di dati 
--Parametri : idSegretario (integer)
--Eccezioni : Utente non autorizzato, l'id inserito non è di un segretario. 
	
	CREATE OR REPLACE FUNCTION "UniNostra".tuttiisegretari(
	 	idSegretario integer
	)
	RETURNS TABLE(
		idS integer,
		nome varchar(50),
		cognome varchar(100),
		email varchar(50),
		indirizzo varchar(100),
		nomedip varchar(50),
		cellulare varchar(10)
	)
	LANGUAGE plpgsql
	AS $$
		begin 
				
			perform * from "UniNostra".segretario s where s.idutente = idSegretario;
			if not found then 
				raise exception 'Utente non autorizzato';
			end if;

			return QUERY 
				select u.idutente ,u.nome ,u.cognome ,u.email ,s.indirizzosegreteria ,s.nomedipartimento ,s.cellulareinterno
				from "UniNostra".segretario s inner join "UniNostra".utente u on s.idutente = u.idutente ;
		END;
	 $$;
	
	select * from "UniNostra".tuttiisegretari('13');
	
--Procedura che permette di aggiornare le informazioni di un segretario
--Parametri: idSegreatario (integer), nome diartimento varchar(50) ,indirizzo varchar(100), cellulare varchar(10)
--Eccezioni : non esiste nessun docente con id inserito 

	CREATE OR replace procedure "UniNostra".updateinfosegretario(
	 	idS integer, nomeDip varchar(50) , indirizzo varchar(100), cellulare varchar(10)
	)
	LANGUAGE plpgsql
	AS $$
		declare 
			seg "UniNostra".segretario%rowtype;
		begin 

			select * into seg from "UniNostra".segretario s where s.idutente = idS;
			if seg.idutente is null then 
				raise exception 'nessun segretario con id %',idS;
			end if;

			if seg.indirizzosegreteria = indirizzo then 
				indirizzo := seg.indirizzosegreteria ;
			end if;
			
			if seg.nomedipartimento = nomeDip then 
				nomeDip := seg.nomedipartimento;
			end if;
			
			if seg.cellulareinterno = cellulare then 
				cellulare := seg.cellulareinterno;
			end if;
			
			update "UniNostra".segretario s set nomedipartimento = nomeDip, indirizzosegreteria = indirizzo, cellulareinterno = cellulare where s.idutente = idS; 
		END;
	 $$;
	
	
--Funzione che permette di visualizzare tutti gli utenti tranne i segretari 
--Parametri : idSegretario 
--Utente non autorizzato 
	
	CREATE OR REPLACE FUNCTION "UniNostra".tuttigliutenti(
	 	idSegretario integer
	)
	RETURNS TABLE(
		idS integer,
		nome varchar(50),
		cognome varchar(100),
		cf varchar(16),
		email varchar(50),
		psw varchar(32),
		tipo tipoUtente
	)
	LANGUAGE plpgsql
	AS $$
		begin 
				
			perform * from "UniNostra".segretario s where s.idutente = idSegretario;
			if not found then 
				raise exception 'Utente non autorizzato';
			end if;

			return QUERY 
				select u.idutente ,u.nome , u.cognome ,u.cf ,u.email ,u."password" , u.tipo 
				from "UniNostra".utente u 
				where u.tipo <> 'Segretario';
		END;
	 $$;
	
	select * from  "UniNostra".tuttigliutenti('13');

--funzione che dato l'id di un utente ritorna alcune sue informazioni 
--Parametri : idUtente 
--Eccezioni : utente inesistente

	CREATE OR REPLACE FUNCTION "UniNostra".infoutente(
	 	idU integer
	)
	RETURNS TABLE(
		nome varchar(50),
		cognome varchar(100),
		email varchar(50)
	)
	LANGUAGE plpgsql
	AS $$
		begin 
				
			perform * from "UniNostra".utente u where u.idutente = idU;
			if not found then 
				raise exception 'Utente inesistente';
			end if;

			return QUERY 
				select u.nome ,u.cognome ,u.email  
				from "UniNostra".utente u
				where u.idutente = idU;
		END;
	 $$;
	

	
	
--Procedura che dato l'id di un utente e la nuova password modifica le sue credenziali d'accesso
--Parametri : idSegretario (integer), idUtente(integer), nuovaPsw varchar(32)
--Eccezioni : utente non autorizzato 
--			  utente non esistente 
--            l'utente a cui si vuole cambiare password è un segretario

	CREATE OR REPLACE procedure "UniNostra".recuperopsw(
	 	idSegretario integer, idU integer, nuovaPsw varchar(20)
	)
	LANGUAGE plpgsql
	AS $$
		begin 
				
			perform * from "UniNostra".segretario s where s.idutente = idSegretario;
			if not found then 
				raise exception 'accesso non autorizzato';
			end if;
		
			perform * from "UniNostra".utente u where u.idutente = idU;
			if not found then 
				raise exception 'utente non esistente';
			end if;
		
			perform * from "UniNostra".segretario s where s.idutente = idU;
			if found then 
				raise exception 'l^utente è un segretario';
			end if;

			update "UniNostra".utente u set "password" = md5(nuovaPsw)::varchar(20) where u.idutente = idU;
		END;
	 $$;

	select * from "UniNostra".utente u 
	
	
	
	
	
	
	
	