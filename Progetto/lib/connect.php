<?php 
    session_start();
    const UniNostra = '"'."UniNostra".'"';
    require("../config.php");

   
    function openConnection() {
        $dbConnect = pg_connect("host=".host." port=".port." dbname=".dbname." user=".username." password=".password."");
        return  $dbConnect;
    }
    
    function endConnection($dbConnect){
        pg_close($dbConnect);
    }

    function redirect($path){
        header("location:".$path);
    }

    function parseData($date){
        $dates = explode("-", $date);
        return $dates[count($dates)-1]."-".$dates[count($dates)-2]."-".$dates[0];
    }

    function parseDataReverse($date){
        $dates = explode("-", $date);
        return $dates[2]."/".$dates[1]."/".$dates[0];
    }

    function parseError($error){
        $arr = explode('CONTEXT', $error );
        $new = $arr[0];
        $new = str_replace("^", "'", $new);
        return $new;
    }

    function creaMenuTendina($nomeFunzione,$idU){
        $dbConnect = openConnection();
        $query = "select * from ".UniNostra.".".$nomeFunzione."($1)";
        $res = pg_prepare($dbConnect, "", $query);
        $row = pg_execute($dbConnect, "", array($idU));
        endConnection($dbConnect);
        
        $f = 1;
        while ($res = pg_fetch_row($row)) {
            if ($f ==1){
                echo "<option selected value=".$res[0].">".$res[0]."</option>";
            }else{
                echo "<option value=".$res[0].">".$res[0]."</option>";
            }
            $f++;
        }
    }

    function spazioFooter(){
        echo "<script> document.getElementById('foot').style.position = 'fixed';</script>";
    }

?>
