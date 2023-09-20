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
    ?>
    
    <?php
      if (isset($_SESSION["cdl"])){
        unset($_SESSION["cdl"]);
      }

      if (isset($_POST["cdl"])){
        $_SESSION["cdl"] = $_POST["cdl"];
        @redirect("studentiCdl.php");
      }

      $connesione = openConnection();
      $query = "select * from ".UniNostra.".visualizzaTuttiCorsi($1)";
      $res = pg_prepare($connesione, "", $query);
      $row = pg_execute($connesione, "", array($_SESSION["idUtente"]));
      
    ?>
    <div class = "centroNoBordo">
        <h1>Corsi di laurea</h1>
    </div>
    <?php
        barraRicerca();
    ?>
    <div class = "container centroConScritte" id = "elliminaTabella">
      <table class="table coloreTabella table-striped">
        <?php
           $titoli = array("#","Codice Cdl","Nome","Descrizione","Durata","Attivo","Numero Iscritti","Piano Studi");
           creaIntestazione($titoli);
        ?>
        <tbody class="ricerca">
          <?php
              $cont = 1;
              $query = "select * from ".UniNostra.".numIscrizioniCdl($1)";
              while ($res = pg_fetch_row($row)) {
                $res2 = pg_prepare($connesione, "", $query);
                $row2 = pg_fetch_assoc(pg_execute($connesione, "", array($res[0])));
                $num = $row2["numiscrizionicdl"];

                $button = "<form method='post' action='visualizzaStudenti.php'><button type='submit' class='btn btn-primary' name='cdl' value='".$res[0]."+".$res[4]."'>Visualizza</button></form>";
                if ($res[3] =="5"){
                    $res[3] = "Magistrale";
                }else{
                    $res[3] = "Triennale";
                }
                if ($res[4] =="t"){
                    $res[4] = "Attivo";
                }else{
                    $res[4] = "Non attivo";
                    $button = "<form method='post' action='visualizzaStudenti.php'><button type='submit' class='btn btn-danger' name='cdl' value='".$res[0]."+".$res[4]."'>Visualizza</button></form>";
                }
                array_push($res,$num);
                array_push($res,$button);
                creaColonne($res,$cont);
                $cont++;
              }  
              endConnection($connesione); 
          ?>
        </tbody>
      </table>
      <div id="tTable" class="centroErr"></div>
    </div>
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
    <?php
      require("../footer.php");
      spazioFooter();
    ?>
  </body>
</html>