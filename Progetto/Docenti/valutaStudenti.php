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

      if (isset($_SESSION["voto"])){
        unset($_SESSION["voto"]);
      }

      if (isset($_POST["voto"])){
        $_SESSION["voto"] = $_POST["voto"];
        redirect("moduloValutazioni.php");
      }

      if (isset( $_SESSION["continua"])){
        $_POST["iscrizioni"] = $_SESSION["continua"];
        unset($_SESSION["continua"]);
      }

      if(!isset($_POST["iscrizioni"])){
        //redirect("valuta.php");
      }

        $connesione = openConnection();
        $query = "select * from ".UniNostra.".iscrittiAppello($1)";
        $res = pg_prepare($connesione, "", $query);
        $row = pg_execute($connesione, "", array($_POST["iscrizioni"]));
    ?>
    <div class="centroNoBordo">
        <h1>Iscrizioni appello id: <?php echo $_POST["iscrizioni"]?></h1>
    </div>
    <?php
      barraRicerca();
    ?>
    <div class = "container centroConScritte" id ="elliminaTabella" >
        <table class="table coloreTabella table-striped" >
            <?php
                $titoli = array("Progressivo","Matricola","Nome","stato","cdl","valuta");
                creaIntestazione($titoli);
            ?>
            <tbody class="ricerca">
                <?php   
                    $cont = 1; 
                    while ($res = pg_fetch_row($row)) {        
                        $btn = "<form method='post' action='valutaStudenti.php'><button type='submit' id = '".$res[0]."' class='btn btn-primary' name='voto' value='".$res[0]."+".$_POST["iscrizioni"]."'>Valuta</button></form>";
                        array_push($res,$btn);
                        $res[1] = $res[1]." ".$res[2];
                        unset($res[2]);
                        $res[0] = bindec($res[0]);
                        creaColonne($res,$cont);
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
            echo "<div class='alert alert-warning centroS' role='alert'><h4>Non hai valutazioni da assegnare. <a href='valuta.php' class='alert-link'>Visualizza Appelli</a></h4>
            </div>";
          }
       
        require("../footer.php");
        if($cont<4){
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