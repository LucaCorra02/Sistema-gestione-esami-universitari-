<?php

    if(isset($_POST["select"])){

        require("../lib/connect.php");
        require("../lib/creaTabella.php");
        $idCdl = $_POST["select"];
    
        $connesione = openConnection();
        $query = "select * from ".UniNostra.".studentiprontilaurea($1)";   
        $res = pg_prepare($connesione, "", $query);
        $row = pg_execute($connesione, "", array($idCdl));

        $cont = 1;
        $query = "select * from ".UniNostra.".mediaStudente($1)";   
        while ($res = pg_fetch_row($row)) {
            
            $res2 = pg_prepare($connesione, "", $query);
            $media = pg_fetch_assoc(pg_execute($connesione, "", array($res[0])));
            
            $res[1] = $res[1]." ".$res[2];
            $res[5] = parseData($res[5]);
            $stato = "InCorso";
            if($res[6]=="f"){
                $stato = "Fuori Corso";
            }
            $res[6] = $stato;
            $button = "<form method='post' action='laureastudenti.php'><button type='submit' class='btn btn-warning btn-sm' name='laurea' value='".$res[0]."'>Laurea</button></form>";
            array_push($res,$media["mediastudente"]);
            array_push($res,$button);
            unset($res[2]);
            creaColonne($res,$cont);
            $cont++; 
        }
        endConnection($connesione);
    }



?>