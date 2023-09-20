<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cambia Psw</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../Css/style.css" />
  </head>
  <body>
    <?php
      require("../lib/connect.php");
      require("../navbar.php");
      if (!(isset($_SESSION["idUtente"]))){
        redirect("../login.php");
      }
    ?>
    <button id="errorebtn"></button>
    <button id="tuttook"></button>
    <div class="alert-box success centroErr" id ="divsuccesso">Cambio Password avvenuto con successo!</div>
    <div class="alert-box failure centroErr" id="diverrore">
      <?php 
        if (isset($_SESSION["errorPsw"])){  
          echo $_SESSION["errorPsw"];
        }
      ?>
    </div>

    <script>
      function Errore(){
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

      if(isset($_SESSION["errorPsw"])){
        $err = $_SESSION["errorPsw"];
        switch($err){
          case $err == "La password nuova non è valida":
            echo '<script type="text/javascript">Errore();</script>';
            break;
          case $err == "La password nuova deve avere almeno 4 caratteri":
            echo '<script type="text/javascript">Errore();</script>';
            break;
          case $err == "Le password non corrispondono":
            echo '<script type="text/javascript">Errore();</script>';
            break;
          default : 
            if(isset($_SESSION["tuttook"])){
              unset($_SESSION["tuttook"]);
              echo '<script type="text/javascript">ok();</script>';
            }else{
              echo '<script type="text/javascript">Errore();</script>';
            }
        }
      }

    ?>
    
    <?php
      if (isset($_POST["inputPswOld"]) && isset($_POST["inputPswNew"]) && isset($_POST["inputPswConfirm"]) ){
        $nuovaPsw = trim($_POST["inputPswNew"]);
        switch($nuovaPsw){
          case trim($nuovaPsw) ==" ":
            $_SESSION["errorPsw"] = "La password nuova non è valida";
            redirect("cambiaPsw.php");
            break;
          case strlen($nuovaPsw) < 4:
            $_SESSION["errorPsw"] = "La password nuova deve avere almeno 8 caratteri";
            redirect("cambiaPsw.php");
            break;
          case $nuovaPsw != $_POST["inputPswConfirm"]:
            $_SESSION["errorPsw"] = "Le password non corrispondono";
            redirect("cambiaPsw.php");
            break;
          default:
            $connesione = openConnection();
            $query = "call ".UniNostra.".aggiornaCredenzialiUtente($1,$2,$3,$4)";
            $res = pg_prepare($connesione, "", $query);
            $row = pg_execute($connesione, "", array($_SESSION["email"],$_POST["inputPswOld"],$_SESSION["email"],$_POST["inputPswNew"]));
            
            if(!$row){
              $_SESSION["errorPsw"] = parseError(pg_last_error());
              redirect("cambiaPsw.php");
            }else{
              $_SESSION["tuttook"] = "true";
              redirect("cambiaPsw.php");
            }
            

        }
      }

    ?>

      <div class="card card-outline-secondary centroS">
        <div class="card-header">
          <h3 class="mb-0">Cambio Password</h3>
        </div>
        <div class="card-body">
          <form class="form" autocomplete="off" method="post" action = "cambiaPsw.php">
            <div class="form-group">
                <label for="inputPswOld">Password Attuale</label>
                <input type="password" name = "inputPswOld" class="form-control" id="inputPswOld" required="">
            </div>
            <div class="form-group">
                <label for="inputPswNew">Password Nuova</label>
                <input type="password" name = "inputPswNew" class="form-control" id="inputPswNew" required="">
                <p class="form-text small text-muted">la password deve avere almeno 8 caratteri</p>
            </div>
            <div class="form-group">
                <label for="inputPswConfirm">Ripeti la password</label>
                <input type="password" class="form-control" name ="inputPswConfirm" id="inputPswConfirm" required="">
            </div>
            <div class="form-group">
                <button type="submit" style= "margin-top: 10px;"class="btn btn-primary">Invia</button>
            </div>
          </form>
        </div>
      </div>
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
    <?php
      require("../footer.php");
    ?>
  </body>
</html>