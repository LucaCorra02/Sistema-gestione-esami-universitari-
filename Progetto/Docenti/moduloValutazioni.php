<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home Segreteria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../Css/style.css" />
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
</head>
  <body>
    <?php
      require("../lib/connect.php");
      require("../navbar.php");
      if (! (isset($_SESSION["idUtente"]))){
        redirect("../login.php");
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
      /*if (isset($_POST["voto"])){
        echo $_POST["voto"];
      }
      */

      if (isset($_SESSION["error"])){
        unset($_SESSION["ok"]);
        echo '<script type="text/javascript">err();</script>';
        unset($_SESSION["error"]);
      }
      if (isset($_SESSION["ok"])){
        unset($_SESSION["ok"]);
        echo '<script type="text/javascript">ok();</script>';
        $arr = explode('+',$_SESSION['voto'],2); 
        $_SESSION["continua"] = $arr[1];
        header("refresh:2;url=valutaStudenti.php");
      }

      if (isset($_POST["valutazione"])){
        $matricola = $_POST["matricola"];
        $idApp = $_POST["idApp"];
        $valutazione = strval($_POST["valutazione"]);
        if ($valutazione == ""){
            $valutazione = null;
        }

        $lode = "false";
        if (isset($_POST["lode"])){
            $lode = "true";
        }

        if (!isset($_SESSION["error"])){
            $dbConnect = openConnection();
            $query = "call ".UniNostra.".registraVotoEsame($1,$2,$3,$4,$5)";
            $res = pg_prepare($dbConnect, "", $query);
            $row = @pg_execute($dbConnect, "", array($_SESSION["idUtente"],$matricola,$idApp,$valutazione,$lode));
        }

        if(!$row){
            $_SESSION["error"] = parseError(pg_last_error());
            $_SESSION["voto"] = $matricola."+".$idApp;
        }else{
            $_SESSION["ok"] = "Voto Inserito con successo";
            if(isset($_POST["voto"] )){
                unset($_POST["voto"] );
            }
        }
        @redirect("moduloValutazioni.php");
      }

    ?>
    <div class="centroForm">
        <form action="moduloValutazioni.php" method="post">
            <div class="row mb-4">
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="matricola">Matricola</label>
                        <input required type="text" id="matricola" class="form-control" name="matricola" autocomplete="off" value ="<?php if(isset($_SESSION["voto"])){ $arr = explode('+',$_SESSION['voto'],2); echo $arr[0];} ?>" readonly/>
                    </div>
                </div>
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="idApp">Codice Appello</label>
                        <input required type="text" id="idApp" class="form-control" name="idApp" autocomplete="off" value ="<?php if(isset($_SESSION["voto"])){$arr = explode('+',$_SESSION['voto'],2); echo $arr[1];} ?>" readonly/>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="valutazione">Voto, campo vuoto per studente ritirato</label>
                        <input min="0" max="30" type="number" id="valutazione" class="form-control" name = "valutazione"/>
                    </div>
                </div>
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="lode">Lode</label>
                        <input class="form-check-input" style="width:20px; height:20px;" type="checkbox" name ="lode" value="" id="lode">
                    </div>
                </div>
            </div>
            <div class ="btnSub">
                <button type="submit" class="btn btn-primary btn-block mb-4">Invia</button>
            </div>
        </form>
    </div>

    <?php
        require("../footer.php");
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
  </body>
</html>