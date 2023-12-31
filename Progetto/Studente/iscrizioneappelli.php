<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home Docente</title>
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
    <button id="errorebtn"></button>
    <button id="tuttook"></button>
    <div class="alert-box success centroErr" id ="divsuccesso">Iscrizione avvenuta con successo! </div>
    <div class="alert-box failure centroErr" id="diverrore">
      <?php
        if (isset($_SESSION["error"])){
          echo parseError($_SESSION["error"]);
        }
      ?>
    </div>

    <script>
      function prova(){
          document.getElementById("errorebtn").click();
      }

      function ok(){
        console.log("sssss");
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
        echo '<script type="text/javascript">prova();</script>';
        unset($_SESSION["error"]);
      }
      if (isset($_SESSION["ok"])){
        unset($_SESSION["ok"]);
        echo '<script type="text/javascript">ok();</script>';
      }
    
      $connesione = openConnection();
      $query = "select * from ".UniNostra.".appelliAperti($1)";
      $res = pg_prepare($connesione, "", $query);
      $row = pg_execute($connesione, "", array($_SESSION["idUtente"]));

      if(isset($_POST['iscriviti'])) {
        $query = "select s.matricola from ".UniNostra.".utente u inner join ".UniNostra.".studente s on s.idutente = u.idutente where u.idutente =".$_SESSION["idUtente"].";";
        $row = pg_fetch_assoc(pg_query($connesione, $query));
  
        $query = "call ".UniNostra.".inserisciIscrizioneEsame($1,$2)";
        
        $res = pg_prepare($connesione, "", $query);
        $res = pg_execute($connesione, "", array($row["matricola"],$_POST['iscriviti']));
        if (!$res){
          $_SESSION["error"] = pg_last_error();
        }else{
          $_SESSION["ok"] = "Iscrizione avvenuta con successo";
        }
        unset($_POST['iscriviti']);
        redirect("iscrizioneappelli.php");
      }
      //unset($_SESSION["ok"]);
      endConnection($connesione);
    ?>

    <div class = "container centroS" id = "elliminaTabella">
      <table class="table coloreTabella table-striped">
        <?php
           $titoli = array("#","Codice Insegnamento","Nome Insegnamento","Cfu","Docente","Data","Ora Inizio","Aula","Cdl","Stato","Iscriviti");
           creaIntestazione($titoli);
        ?>
        <tbody>
          <?php
              $cont = 1;
              while ($res = pg_fetch_row($row)) {
                //aggiungo bottone
                $nome = "iscriviti";
                $button = "<form method='post'><button type='submit' class='btn btn-primary' name='iscriviti' value='".$res[0]."'>Iscriviti</button></form>";
                $res = array_slice($res,1);

                $res[5]= parseData($res[5]);
                //unisco nome e cognome 
                $res[3] = $res[3]." ".$res[4];
                unset($res[4]);
                array_push($res,$button);

                creaColonne($res,$cont);
                $cont++;
              }   
          ?>
        </tbody>
      </table>
    </div>
    <?php
      if ($cont==1){
        elliminaTabella("elliminaTabella");
        echo "<div class='alert alert-warning centroS' role='alert'><h4>Non ci sono appelli aperti per il tuo cdl. <a href='visualizzaAppelli.php' class='alert-link'>Visualizza iscrizioni</a></h4>
        </div>";
      }
      require("../footer.php");
      if($cont < 5){
        spazioFooter();
      }
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
   
  </body>
</html>