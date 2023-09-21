<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home Docente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../Css/style.css" />
</head>
  <body>
    <?php
        require("../lib/connect.php");
        require("../navbar.php");
        require("../lib/creaTabella.php");
        if (! (isset($_SESSION["idUtente"]))){
          redirect("../login.php");
        }

        $connesione = openConnection();
        $query = "select * from ".UniNostra.".appelliApertiDoc($1)";   
        $res = pg_prepare($connesione, "", $query);
        $row = pg_execute($connesione, "", array($_SESSION["idUtente"]));
    ?>
    <div class="centroNoBordo">
        <h1>Appelli Aperti</h1>
    </div>
    <?php
        barraRicerca();
    ?>
    <div class = "container tabellaGrande" id ="elliminaTabella" >
        <table class="table coloreTabella table-striped" >
            <?php
                $titoli = array("#","Codice Insegnamento","Nome Insegnamento","Cfu","Cdl","Anno Erogazione","Data Esame","Ora Inizio","Ora Fine","Aula","note","stato","iscritti","lista iscritti");
                creaIntestazione($titoli);
            ?>
            <tbody class="ricerca">
                <?php   
                    $cont = 1;
                    $query = "call ".UniNostra.".aggiornaStatoAppello($1)";  
                    $queryNum = "select * from ".UniNostra.".numIscrittiA($1)";  
                    while ($res = pg_fetch_row($row)) {
                        $res2 = pg_prepare($connesione, "", $query);
                        $row2 = pg_execute($connesione, "", array($res[0]));
                        
                        $res3 = pg_prepare($connesione, "", $queryNum);
                        $num = pg_fetch_assoc(pg_execute($connesione, "", array($res[0])));
                
                        
                        if($res[11] == "aperto"){
                            $res[6] = parseData($res[6]);
                            array_push($res,$num["numiscritti"]);
                            $btn = "<form method='post' action='iscrizioni.php'><button type='submit' id = '".$res[0]."' class='btn btn-primary' name='iscrizioni' value='".$res[0]."'>Iscrizioni</button></form>";
                            if ($num["numiscritti"] ==0){
                                $btn = "<button type='submit' id = '".$res[0]."' class='btn btn-primary' name='iscrizioni' value='".$res[0]."' disable>Iscrizioni</button>";
                            }
                            array_push($res,$btn);
                            $res = array_slice($res,1);
                            creaColonne($res,$cont);
                        }
                        $cont++;
                    }
                    endConnection($connesione);  
                ?> 
            </tbody>
        </table>
        <div id="tTable" class="centroErr"></div>
    </div>
    <?php
        if ($cont==1){
            elliminaTabella("elliminaTabella");
            echo "<div class='alert alert-warning centroS' role='alert'><h4>Nessun appello aperto. <a href='creaAppelli.php' class='alert-link'>Crea Appelli</a></h4>
            </div>";
          }
        require("../footer.php");
        if($cont < 4){
          spazioFooter();
        }
    ?>
     <script>
      $(document).ready(function () {
        (function ($) {
          $('#filtra').keyup(function () {
            var rex = new RegExp($(this).val(), 'i');
            $('#tTable').hide();
            $('.ricerca tr').hide();
            var ris = $('.ricerca tr').filter(function () {
              return rex.test($(this).text());
            })
            if (ris.length==0){
              $('#tTable').show();
              $("#tTable").html('<h4 class="noRis">No record found</h4>');
            }else{
              ris.show();
            }
        })}(jQuery));
      });      
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
  </body>
</html>