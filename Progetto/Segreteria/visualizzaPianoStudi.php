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
      if (!isset($_SESSION["cdl"])){
        redirect("visualizzaCdl.php");
      }
    ?>
    <button id="errorebtn"></button>
    <button id="tuttook"></button>
    <div class="alert-box success centroErr" id ="divsuccesso">
        <?php 
            if (isset($_SESSION["ok"])){
                echo $_SESSION["ok"];
            }
        ?> 
    </div>
    <div class="alert-box failure centroErr" id="diverrore">
      <?php
        if (isset($_SESSION["error"])){
            echo $_SESSION["error"];
        }
      ?>
    </div>

    <script>
      function err(){
          document.getElementById("errorebtn").click();
      }

      function ok(){
        document.getElementById("tuttook").click();
      }
    
      $("#errorebtn").on("click", function() {
        $("#diverrore").fadeIn(800).delay( 5500 ).fadeOut( 800) ;
      });
      
      $("#tuttook").on("click", function() {
        $("#divsuccesso").fadeIn(800 ).delay( 2500 ).fadeOut( 800) ;
      });

    </script>

    <?php
      if (isset($_SESSION["error"])){
        unset($_SESSION["ok"]);
        echo '<script type="text/javascript">err();</script>';
        unset($_SESSION["error"]);
      }
      
      if (isset($_SESSION["ok"])){
        unset($_SESSION["ok"]);
        echo '<script type="text/javascript">ok();</script>';
        header("refresh:2;url=visualizzaPianoStudi.php");
      }

      /*
      if (isset($_SESSION["modfica"])){
        unset($_SESSION["modifica"]);
      }*/

      if(isset( $_SESSION["prop"])){
        unset( $_SESSION["prop"]);
      }

      if (isset($_POST["rimuovi"])){
        $arr = explode("+",$_POST["rimuovi"]);
        $connesione = openConnection();
        $query = "call ".UniNostra.".rimuoviPianoStudi($1,$2)";
        $res = pg_prepare($connesione, "", $query);
        $row = pg_execute($connesione, "", array($arr[0],$arr[1]));  

        if(!$row){
          $_SESSION["error"] = parseError(pg_last_error());
        }else{
          $_SESSION["ok"] = "Esame cancellato con successo";
        }
        @redirect("visualizzaPianoStudi.php");
      }

      if (isset($_POST["modifica"])){
        $_SESSION["modifica"] = $_POST["modifica"];
        @redirect("aggiornaInsegnamento.php");
      }

      if (isset($_POST["prop"])){
        $_SESSION["prop"] = $_POST["prop"];
        @redirect("propedeuticita.php");
      }

    ?>
    
    <?php
      $connesione = openConnection();
      $query = "select * from ".UniNostra.".cdlIsAttivo($1)";
      $res = pg_prepare($connesione, "", $query);
      $arr = pg_fetch_assoc(pg_execute($connesione, "", array($_SESSION["cdl"]))); 
      $attivo = $arr["cdlisattivo"];

      $cdl = $_SESSION["cdl"];  
      if ($attivo == "t"){
        $connesione = openConnection();
        $query = "select * from ".UniNostra.".visualizzaTuttiCdl($1)";
        $res = pg_prepare($connesione, "", $query);
        $row = pg_execute($connesione, "", array($_SESSION["cdl"]));  
           
    ?>
    <div class = "centroNoBordo">
        <h1>Piano Studi Cdl : <?php echo $cdl; ?></h1>
    </div>
    
        <div class='input-group centroNoBordoSotto'> 
            <label class='form-label' for='filtra' style='margin-right:8px;font-size:20px;'>Filtra</label>
            <input id='filtra' type='text' class='form-control' placeholder='Ricerca..' style='margin-right:2%;'>
            <label class='form-label' for='addIns' style='margin-right:8px;font-size:18px;'>Aggiungi Insegnamento al piano di studi: </label>
            <?php
              echo "<form method='post' action='aggiungiPianoStudi.php'><button type='submit' class='btn btn-primary' name='aggiungi' value='".$cdl."' id ='addIns'>Aggiungi</button></form>";
            ?>
        </div>
    
    <div class = "container tabellaGrand" id = "elliminaTabella">
      <table class="table coloreTabella table-striped">
        <?php
           $titoli = array("#","Codice Insegnamento","Nome Insegnamento","Cfu","Codice Cdl","Anno Erogazione","Descrizione","Docente","Propedeuticità","Rimuovi dal manifesto","Modifica Propredeuticità");
           creaIntestazione($titoli);
        ?>
        <tbody class="ricerca">
          <?php
              $cont = 1;
              $query = "select * from ".UniNostra.".visualizzaProp($1,$2)";
              while ($res = pg_fetch_row($row)) {
                $row2 = pg_prepare($connesione, "", $query);
                $res2 = pg_fetch_assoc(pg_execute($connesione, "", array($cdl,$res[0])));
                $prop = $res2["visualizzaprop"];

                $res[6] = $res[6]." ".$res[7];
                unset($res[7]);
                if ($prop == ""){
                    array_push($res,"Nessuna");
                }else{
                    array_push($res,$prop);
                }
                $propedeuticita = "<form method='post' action='visualizzaPianoStudi.php'><button type='submit' class='btn btn-primary btn-sm' name='prop' value='".$res[0]."+".$cdl."'>Modifica</button></form>";
                //$modifica = "<form method='post' action='visualizzaPianoStudi.php'><button type='submit' class='btn btn-warning btn-sm' name='modifica' value='".$res[0]."'>Modifica</button></form>";
                $rimuovi = "<form method='post' action='visualizzaPianoStudi.php'><button type='submit' class='btn btn-danger btn-sm' name='rimuovi' value='".$res[0]."+".$cdl."'>Rimuovi</button></form>";
                array_push($res,$rimuovi,$propedeuticita);
                creaColonne($res,$cont);
                $cont++;
              }
              
              endConnection($connesione); 
            }
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
    <?php 
       if ($attivo == "f" ){
        elliminaTabella("elliminaTabella");
        echo "<div class='alert alert-warning centroS' role='alert'><h4>Non ci sono insegnamenti nel piano di studi.<form method='post' action='aggiungiPianoStudi.php'><button type='submit' class='btn btn-warning' name='aggiungi' value='".$cdl."' id ='addIns'>Aggiungi</button></form></h4>
        </div>";
      }
      require("../footer.php");
      spazioFooter();
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
   
  </body>
</html>