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

select idU,tipoU from "UniNostra".login('violettaLonati@docenti.UniNostra','1234');