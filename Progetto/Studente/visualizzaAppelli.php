<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home Studente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../Css/style.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
</head>
  <body>
    <?php
      require("../lib/connect.php");
      require("../navbar.php");
      if (! (isset($_SESSION["idUtente"]))){
        redirect("../login.php");
      }
      require("../lib/creaTabella.php");
    ?>

    <button id="errorebtn"></button>
    <button id="tuttook"></button>
    <div class="alert-box success centroErr" id ="divsuccesso">
      <?php
        if (isset($_SESSION["ok"])){
          echo parseError($_SESSION["ok"]);
        }
      ?>
    </div>
    <div class="alert-box failure centroErr" id="diverrore">
      <?php
        if (isset($_SESSION["error"])){
          echo parseError($_SESSION["error"]);
        }
      ?>
    </div>

    <script>
      function errore(){
          document.getElementById("errorebtn").click();
      }

      function ok(){
        console.log("sssss");
        document.getElementById("tuttook").click();
      }
    </script>
    <script>
    
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
        echo '<script type="text/javascript">errore();</script>';
        unset($_SESSION["error"]);
      }
      if (isset($_SESSION["ok"])){
        unset($_SESSION["ok"]);
        echo '<script type="text/javascript">ok();</script>';
      }

      $connesione = openConnection();
      $query = "select * from ".UniNostra.".iscrizioniAppelli($1)";
      $res = pg_prepare($connesione, "", $query);
      $row = pg_execute($connesione, "", array($_SESSION["idUtente"]));

      if(isset($_POST['disiscriviti'])) {

        $query = "select s.matricola from ".UniNostra.".utente u inner join ".UniNostra.".studente s on s.idutente = u.idutente where u.idutente =".$_SESSION["idUtente"].";";
        $row = pg_fetch_assoc(pg_query($connesione, $query));

        $query = "call ".UniNostra.".elliminaIscrizione($1,$2)";   
        $res = pg_prepare($connesione, "", $query);
        $res = pg_execute($connesione, "", array($row["matricola"],$_POST['disiscriviti']));
        
        if (!$res){
          $_SESSION["error"] = pg_last_error();
        }else{
          $_SESSION["ok"] = "Disiscrizione all'appello ".$_POST['disiscriviti'].", avvenuta con successo!";
        }
        unset($_POST['disiscriviti']);
        redirect("visualizzaAppelli.php");
      }
      endConnection($connesione);
    ?>

    <div class = "container centroS" id ="elliminaTabella" >
      <table class="table coloreTabella table-striped" >
        <?php
           $titoli = array("#","Codice Insegnamento","Nome Insegnamento","Cfu","Cdl","Docente","Data","Ora Inizio","Aula","Stato Appello","Stato Studente","Disicriviti");
           creaIntestazione($titoli);
        ?>
        <tbody>
          <?php
              $cont = 1;
              while ($res = pg_fetch_row($row)) {
                //aggiungo bottone
                $button = "<form method='post'><button type='submit' id = '".$res[0]."' class='btn btn-primary' name='disiscriviti' value='".$res[0]."'>Disiscriviti</button></form>";
                $res = array_slice($res,1);

                $res[6] = parseData($res[6]);
                //unisco nome e cognome 
                $res[4] = $res[4]." ".$res[5];
                unset($res[5]);
                
                if ($res[9] != "chiuso"){
                  array_push($res,$button);
                }else{
                  array_push($res,"<p style='color:red'>Impossibile disicriversi</p>");
                }
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
        echo "<div class='alert alert-warning centroS' role='alert'><h4>Non sei iscritto a nessun appello. <a href='iscrizioneappelli.php' class='alert-link'>Iscriviti agli appelli</a></h4>
        </div>";
      }
      require("../footer.php");
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
   
  </body>
</html>