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

      if(isset($_SESSION["arr"])){
        unset($_SESSION["arr"]);
      }

      if(isset($_SESSION["modificasegretario"])){
        unset($_SESSION["modificasegretario"]);
      }

      if (isset($_POST["modificasegretario"])){
        $_SESSION["modificasegretario"] = $_POST["modificasegretario"];
        redirect("modificasegretario.php");
      }

      if( isset($_SESSION["addseg"])){
        unset( $_SESSION["addseg"]);
      }

      if(isset($_POST["addseg"])){
        $_SESSION["addseg"] = $_POST["addseg"];
        redirect("aggiungisegretario.php");
      }

    ?>
    
    <?php
      $connesione = openConnection();
      $query = "select * from ".UniNostra.".tuttiisegretari($1)";
      $res = pg_prepare($connesione, "", $query);
      $row = pg_execute($connesione, "", array($_SESSION["idUtente"]));
      
    ?>
    <div class = "centroNoBordo">
        <h1>Segretari</h1>
    </div>
    <div class='input-group centroNoBordoSotto'> 
            <label class='form-label' for='filtra' style='margin-right:8px;font-size:20px;'>Filtra</label>
            <input id='filtra' type='text' class='form-control' placeholder='Ricerca..' style='margin-right:2%;'>
            <label class='form-label' for='adddoc' style='margin-right:8px;font-size:18px;'>Aggiungi Segretario: </label>
            <?php
              echo "<form method='post' action='visualizzasegretari.php'><button type='submit' class='btn btn-primary btn-sm' name='addseg' id ='addseg'>Aggiungi</button></form>";
            ?>
        </div>
    <div class = "container centroConScritte" id = "elliminaTabella">
      <table class="table coloreTabella table-striped">
        <?php
           $titoli = array("#","Nome","Email","Indirizzo","Dipartimento","Cellulare","Modifica");
           creaIntestazione($titoli);
        ?>
        <tbody class="ricerca">
          <?php
              $cont = 1;
              while ($res = pg_fetch_row($row)) {
                $res[1] = $res[1]." ".$res[2];
                
                $btn= "<form method='post' action='visualizzasegretari.php'><button type='submit' class='btn btn-primary btn-sm' name='modificasegretario' value='".$res[0]."'>Modifica</button></form>";
                array_push($res,$btn);
                unset($res[2]);
                unset($res[0]);
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
      if($cont <= 5){
        spazioFooter();
      }
    ?>
   
  </body>
</html>