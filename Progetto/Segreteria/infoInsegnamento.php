<?php
    if(isset($_POST["ins"])){
        require("../lib/connect.php");
        $dbConnect = openConnection();
        $query = "select * from ".UniNostra.".infoIns($1)";
        $res = pg_prepare($dbConnect, "", $query);
        $row = pg_fetch_assoc(pg_execute($dbConnect, "", array($_POST["ins"])));
        endConnection($dbConnect);

        echo json_encode($row);
    }
?>