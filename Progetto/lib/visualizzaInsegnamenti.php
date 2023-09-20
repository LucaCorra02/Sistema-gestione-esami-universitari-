<?php

    require("connect.php");
    require("creaTabella.php");

    if (isset($_POST['select'])) {

        if (!isset($_POST['idDoc'])){
            $connesione = openConnection();
            $query = "select * from ".UniNostra.".visualizzaTuttiCdl($1)";   
            $res = pg_prepare($connesione, "", $query);
            $row = pg_execute($connesione, "", array($_POST['select']));
        }else{
            $connesione = openConnection();
            $query = "select * from ".UniNostra.".visualizzaCdlDoc($1,$2)";   
            $res = pg_prepare($connesione, "", $query);
            $row = pg_execute($connesione, "", array($_POST['select'],$_POST['idDoc']));
        }
        
        if (!$row) {
            echo parseError(pg_last_error());
        }else{
            $cont = 1;
            $query = "select * from ".UniNostra.".visualizzaProp($1,$2)";
            while ($res = pg_fetch_row($row)) {
                $res[6] = $res[6]." ".$res[7];
                unset($res[7]);

                $res2 = pg_prepare($connesione, "", $query);
                $resProp = pg_fetch_assoc(pg_execute($connesione, "", array($res[3],$res[0])));
                $pro = "Nessuna";
                if ($resProp["visualizzaprop"] !="" ){
                    $pro = $resProp["visualizzaprop"];
                }
                array_push($res, $pro);
                // print_r($res2);
                creaColonne($res,$cont);
                $cont++; 
            } 
        }   
        endConnection($connesione); 
    }

?>