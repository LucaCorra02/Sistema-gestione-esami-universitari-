-- verifica il login di un utente data email e password
-- restituisce id e tipo dell'utente in caso di login corretto
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

CREATE OR REPLACE FUNCTION "UniNostra".profiloStudente (
  idU integer
)
RETURNS TABLE (
	matricola integer,
    telefono varchar(20),
    indirizzoresidenza varchar(100),
    annoiscrizione date,
    incorso bool,
    idcorso varchar(10)
)
LANGUAGE plpgsql
AS $$
	begin	
		
		RETURN QUERY
			select s.matricola ,s.telefono ,s.indirizzoresidenza ,s.annoiscrizione ,s.incorso ,s.idcorso 
			from "UniNostra".utente u inner join "UniNostra".studente s on u.idutente = s.idutente 
			where u.idutente = idU;
	 END;
 $$;

 --select * from "UniNostra".profiloStudente('11');




