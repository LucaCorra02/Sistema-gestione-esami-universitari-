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
        redirect("visualizzaStudenti.php");
      }
      if (isset( $_SESSION["arr"])){
        unset($_SESSION["arr"]);
      }
      if (isset($_SESSION["dis"])){
        unset($_SESSION["dis"]);
      }
    ?>
    
    <?php
      if(isset($_SESSION["addStud"])){
        unset($_SESSION["addStud"]);
      }

      if(isset($_POST["addStud"])){
        $_SESSION["addStud"] = $_POST["addStud"];
        @redirect("aggiungiStudente.php");
      }

      if(isset($_SESSION["disiscrivi"])){
        unset($_SESSION["disiscrivi"]);
      }

      if(isset($_POST["disiscrivi"])){
        $_SESSION["disiscrivi"] = $_POST["disiscrivi"];
        @redirect("disiscriviStudente.php");
      }

      if(isset($_SESSION["modificaStud"])){
        unset($_SESSION["modificaStud"]);
      }

      if(isset($_POST["modificaStud"])){
        $_SESSION["modificaStud"] = $_POST["modificaStud"];
        @redirect("aggiornainfostudente.php");
      }

      if(isset($_SESSION["visCarriera"])){
        unset($_SESSION["visCarriera"]);
      }

      if(isset($_POST["visCarriera"])){
        $_SESSION["visCarriera"] = $_POST["visCarriera"];
        @redirect("viscarrierastudenti.php");
      }

      $arr = explode("+",$_SESSION["cdl"]);
      $idCdl = $arr[0];
      $statoCdl = $arr[1];

      $connesione = openConnection();
      $query = "select * from ".UniNostra.".visualizzaStudentiCdl($1)";
      $res = pg_prepare($connesione, "", $query);
      $row = pg_execute($connesione, "", array($idCdl));
      
    ?>
    <div class = "centroNoBordo">
        <h1>Studenti per il cdl : <?php echo $idCdl;?></h1>
    </div>
    <div class='input-group centroNoBordoSotto' id="elliminaRicerca"> 
            <label class='form-label' for='filtra' style='margin-right:8px;font-size:20px;'>Filtra</label>
            <input id='filtra' type='text' class='form-control' placeholder='Ricerca..' style='margin-right:2%;'>
            <label class='form-label' for='addStud' style='margin-right:8px;font-size:18px;'>Aggiungi Studente: </label>
            <?php
              if ($statoCdl == "t"){
                echo "<form method='post' action='studentiCdl.php'><button type='submit' class='btn btn-primary' name='addStud' id ='addStud' value='".$idCdl."'>Aggiungi</button></form>";
              }
            ?>
    </div>
    <div class = "container centroConScritte" id = "elliminaTabella">
      <table class="table coloreTabella table-striped">
        <?php
           $titoli = array("#","Matricola","Nome","Telefono","Data di Nascita","Residenza","Data Iscrizione","Stato","Modifica","Carriera","Disiscrivi");
           creaIntestazione($titoli);
        ?>
        <tbody class="ricerca">
          <?php
              $cont = 1;
              while ($res = pg_fetch_row($row)) {
                $res[1] = $res[1]." ".$res[2];
                $res[4] = parseData($res[4]);
                $res[6] = parseData($res[6]);
                $stato = "In Corso";
                if ($res[7] == "f"){
                    $stato = "Fuori Corso";
                }
                $res[7] = $stato;
                unset($res[2]);

                $button = "<form method='post' action='studentiCdl.php'><button type='submit' class='btn btn-warning btn-sm' name='modificaStud' value='".$res[0]."'>Modifica</button></form>";
                array_push($res,$button);
                $button = "<form method='post' action='studentiCdl.php'><button type='submit' class='btn btn-primary btn-sm' name='visCarriera' value='".$res[0]."'>Carriera</button></form>";
                array_push($res,$button);
                $button = "<form method='post' action='studentiCdl.php'><button type='submit' class='btn btn-danger btn-sm' name='disiscrivi' value='".$res[0]."'>Disiscrivi</button></form>";
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
        if ($cont==1){
            elliminaTabella("elliminaTabella");
            echo "<div class='alert alert-warning centroS' role='alert'>";
            if ($statoCdl =="t"){
                echo  "<h4>Non ci sono studenti iscritti.</h4>";
            }else{
                elliminaTabella("elliminaRicerca");
                echo "<h4> Non possono iscriversi nuovi studenti, in quanto il cdl risulta chiuso <a href='visualizzaStudenti.php' class='alert-link'>Altri corsi</a></h4>";
            }
            echo "</div>";
        }   
      require("../footer.php");
      spazioFooter();
    ?>
   
  </body>
</html>