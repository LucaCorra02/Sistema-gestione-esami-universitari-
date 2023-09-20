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

      if(isset( $_SESSION["modificadocente"])){
        unset( $_SESSION["modificadocente"]);
      }

      if(isset($_POST["modificadocente"])){
        $_SESSION["modificadocente"] = $_POST["modificadocente"];
        @redirect("modificadocente.php");
      }

      if(isset($_SESSION["arr"])){
        unset($_SESSION["arr"]);
      }

      if(isset( $_SESSION["adddoc"])){
        unset( $_SESSION["adddoc"]);
      }

      if(isset($_POST["adddoc"])){
        $_SESSION["adddoc"] = $_POST["adddoc"];
        @redirect("aggiungidocente.php");
      }


    ?>
    
    <?php
      $connesione = openConnection();
      $query = "select * from ".UniNostra.".tuttiidocenti($1)";
      $res = pg_prepare($connesione, "", $query);
      $row = pg_execute($connesione, "", array($_SESSION["idUtente"]));
      
    ?>
    <div class = "centroNoBordo">
        <h1>Docenti</h1>
    </div>
    <div class='input-group centroNoBordoSotto'> 
            <label class='form-label' for='filtra' style='margin-right:8px;font-size:20px;'>Filtra</label>
            <input id='filtra' type='text' class='form-control' placeholder='Ricerca..' style='margin-right:2%;'>
            <label class='form-label' for='adddoc' style='margin-right:8px;font-size:18px;'>Aggiungi Docente: </label>
            <?php
              echo "<form method='post' action='visualizzadocenti.php'><button type='submit' class='btn btn-primary' name='adddoc' id ='adddoc'>Aggiungi</button></form>";
            ?>
        </div>
    <div class = "container centroConScritte" id = "elliminaTabella">
      <table class="table coloreTabella table-striped">
        <?php
           $titoli = array("#","Nome","Indirizzo Ufficio","Cellulare interno","Numero Docenze","Docenze","Ex Docenze","Modifica");
           creaIntestazione($titoli);
        ?>
        <tbody class="ricerca">
          <?php
              $cont = 1;
              $query = "select * from ".UniNostra.".profiloDocente($1)";
              $query2 = "select * from ".UniNostra.".numexdocenze($1)";
              while ($res = pg_fetch_row($row)) {
                $res2 = pg_prepare($connesione, "", $query);
                $row2 = pg_fetch_assoc(pg_execute($connesione, "", array($res[0])));
                $res3 = pg_prepare($connesione, "", $query2);
                $row3 = pg_fetch_assoc(pg_execute($connesione, "", array($res[0])));
            
                $res[1] = $res[1]." ".$res[2];
            
                array_push($res,$row2["numdocenze"]);
                if ($row2["docenze"]==""){
                    $row2["docenze"] = "-";
                }
                array_push($res,$row2["docenze"]);
                
                $btn= "<form method='post' action='exinsegnamenti.php'><button type='submit' class='btn btn-primary btn-sm' name='exdocenze' value='".$res[0]."'>Visualizza</button></form>";
                if ($row3["numexdocenze"] ==0){
                    $btn = "<p style='color:red;'>Nessun ex insegnamento</p>";
                }
                array_push($res,$btn);
                $btn= "<form method='post' action='visualizzadocenti.php'><button type='submit' class='btn btn-warning btn-sm' name='modificadocente' value='".$res[0]."'>Modifica</button></form>";
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
      //;
    ?>
   
  </body>
</html>