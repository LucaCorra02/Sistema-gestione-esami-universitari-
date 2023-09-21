<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home Segreteria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../Css/style.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
</head>
  <body>
    <?php
      require("../lib/connect.php");
      require("../navbar.php");
      require("../lib/creaTabella.php");
      if (! (isset($_SESSION["idUtente"]))){
        redirect("../login.php");
      }
      if (!isset($_SESSION["iscrizioni"])){
        redirect("storicoAppelli.php");
      }

      if(isset($_SESSION["modificavoto"])){
        unset($_SESSION["modificavoto"]);
      }

      if(isset($_POST["modificavoto"])){
        $_SESSION["modificavoto"] = $_POST["modificavoto"];
        @redirect("modificavotoaccettato.php");
      }

      $connesione = openConnection();
      $query = "select * from ".UniNostra.".studPartecipanti($1)";   
      $res = pg_prepare($connesione, "", $query);
      $row = pg_execute($connesione, "", array($_SESSION["iscrizioni"]));

      $query = "select * from ".UniNostra.".studPartecipantiStorico($1)";   
      $res2 = pg_prepare($connesione, "", $query);
      $row2 = pg_execute($connesione, "", array($_SESSION["iscrizioni"]));

  

    ?>
    <div class="centroNoBordo">
        <h1>Partecipanti appello id: <?php echo $_SESSION["iscrizioni"];?></h1>
    </div>
    <?php 
      barraRicerca();
    ?>

    <div class = "container centroConScritte" id ="elliminaTabella" >
        <table class="table coloreTabella table-striped" >
            <?php
                $titoli = array("#","Matricola","Nome","Cdl","voto","stato","modifica voto");
                creaIntestazione($titoli);
            ?>
            <tbody class="ricerca">
                <?php   
                    $cont = 1;
                    while ($res = pg_fetch_row($row)) {    
                        $res[1] = $res[1]." ".$res[2];
                        $res[0] = $res[0];
                        if($res[5] =="t"){
                            $res[4] = $res[4]." e lode";
                        }
                        $btn = "<p style='color:red;'>Non ancora accettato</p>";
                        if($res[6] == "Accettato"){
                            $btn = "<form method='post' action='iscrizioniappelli.php'><button type='submit' id = '".$res[0]."' class='btn btn-warning btn-sm' name='modificavoto' value='".$res[0]."+".$_SESSION["iscrizioni"]."'>Modifica</button></form>";
                        }
                        array_push($res,$btn);
                        unset($res[5]);
                        unset($res[2]);
                        creaColonne($res,$cont);
                        $cont++;
                    }

                    $cont = 1;
                    while ($res2 = pg_fetch_row($row2)) {        
                        $res2[1] = $res2[1]." ".$res2[2];
                        $res2[0] = bindec($res2[0]);

                        if($res2[5] =="t"){
                            $res2[4] = $res2[4]." e lode";
                        }
                        $btn = "<p style='color:red;'>Ex studente</p>";
                        array_push($res2,$btn);
                        unset($res2[5]);
                        unset($res2[2]);
                        creaColonne($res2,$cont);
                        $cont++;
                    }
                    endConnection($connesione);  
                ?> 
            </tbody>
        </table>
        <div id="tTable" class="centroErr"></div>
    </div>
    <?php
        require("../footer.php");
        spazioFooter();
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