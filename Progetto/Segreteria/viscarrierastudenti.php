<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home Segreteria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../Css/style.css" />
</head>
    <body>
        
        <?php
        require("../lib/connect.php");
        require("../navbar.php");
        if (! (isset($_SESSION["idUtente"]))){
            redirect("../login.php");
        }
        if(!isset($_SESSION["visCarriera"])){
            redirect("studentiCdl.php");
        }
        require("../lib/creaTabella.php");
        

        $mat = $_SESSION["visCarriera"];

        $connesione = openConnection();
        $query = "select * from ".UniNostra.".idAssociatoMatricola($1)";
        $res = pg_prepare($connesione, "", $query);
        $row = pg_fetch_assoc(pg_execute($connesione, "", array($mat))); 
        $idU = $row["idassociatomatricola"];

        $connesione = openConnection();
        $query = "select * from ".UniNostra.".visualizzaEsamiPassati($1)";
        $res = pg_prepare($connesione, "", $query);
        $row = pg_execute($connesione, "", array($idU));

        $connesione = openConnection();
        $query = "select * from ".UniNostra.".visualizzaCarriera($1)";
        $res2 = pg_prepare($connesione, "", $query);
        $row2 = pg_execute($connesione, "", array($idU));
        
        endConnection($connesione);
        ?>

        <div class = "centroNoBordo"><h1>Esami Passati</h1></div>
            <div class = "container centroConScritte" id ="elliminaTabella" >
                <table class="table coloreTabella table-striped" >
                    <?php
                        $titoli = array("#","Codice Insegnamento","Nome Insegnamento","Cfu","Cdl","Docente","Data","Voto","Stato Studente");
                        creaIntestazione($titoli);
                    ?>
                    <tbody>
                        <?php
                            $cont = 1;
                            $sommaPesata= 0;
                            $sommaCfu = 0;
                            while ($res = pg_fetch_row($row)) {
                                //tolgo id appello 
                                $res = array_slice($res,1);
                                $res[6] = parseData($res[6]);
                                //unisco nome e cognome 
                                $res[4] = $res[4]." ".$res[5];
                                unset($res[5]);

                                $sommaPesata+= intval($res[7])*intval($res[2]);
                                $sommaCfu += intval($res[2]);
                                
                                //controllo se la lode è true, la aggiugno al campo voto
                                if ($res[8] == "t") {
                                    $res[7] = $res[7]." e lode";
                                } 
                                unset($res[8]);
                                creaColonne($res,$cont);
                                $cont++;
                            }
                        ?>
                    </tbody>
                </table>
                <div class="alert alert-primary centroNoBordo" role="alert" style="width: 20%; background-color:#0D6EFD;">
                    <h4 style="color:white;">
                        <?php 
                            if ($sommaCfu!=0){
                                echo "Media: ".number_format(floatval(($sommaPesata/$sommaCfu)), 2, '.', '');
                            }
                        ?>
                    </h4>
                </div>
            </div>
            <div class = "centroNoBordo" id="id2"><h1>Carriera Studente</h1></div>
            <div class = "container centroConScritte" id ="elliminaTabella2" >
                <table class="table coloreTabella table-striped" >
                    <?php
                        $titoli = array("#","Codice Insegnamento","Nome Insegnamento","Cfu","Cdl","Docente","Data","Voto","Stato");
                        creaIntestazione($titoli);
                    ?>
                    <tbody>
                        <?php
                            $cont2 = 1;
                            while ($res2 = pg_fetch_row($row2)) {
                                //tolgo id appello 
                                $res2 = array_slice($res2,1);
                                
                                $res2[6] = parseData($res2[6]);
                                //unisco nome e cognome 
                                $res2[4] = $res2[4]." ".$res2[5];
                                unset($res2[5]);

                                //controllo se la lode è true, la aggiugno al campo voto
                                if ($res2[8] == "t") {
                                    $res2[7] = $res2[7]." e lode";
                                } 
                                unset($res2[8]);
                                creaColonne($res2,$cont2);
                                $cont2++;
                            }
                        ?>
                    </tbody>
                </table>
            </div>
    
            <?php
            if ($cont==1){
                elliminaTabella("elliminaTabella");
                echo "<div class='alert alert-warning centroConScritte erroreGiallo' role='alert'><h4>Nessun voto accettato. Media: 0.00</h4>
                </div>";
            }

            if ($cont2==1){
                elliminaTabella("id2");
                echo "<div class = 'centroNoBordo' id='id2'><h1>Carriera Studente</h1></div>";
                elliminaTabella("elliminaTabella2");
                echo "<div class='alert alert-warning centroConScritte erroreGiallo' role='alert'><h4>Nessun Esame in Carriera.</h4>
                </div>";
            }
                require("../footer.php");
                if ($cont==1 && $cont2==1){
                    spazioFooter();
                }
            ?>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
            <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
    </body>
</html>