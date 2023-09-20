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
      require("../lib/creaTabella.php");
      if (! (isset($_SESSION["idUtente"]))){
        redirect("../login.php");
      }
      if (!isset($_SESSION["modifica"])){
        redirect("visualizzaInsegnamenti.php");
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
        
        if (isset($_SESSION["error"])){
            unset($_SESSION["ok"]);
            echo '<script type="text/javascript">err();</script>';
            unset($_SESSION["error"]);
        }
        if (isset($_SESSION["ok"])){
            unset($_SESSION["ok"]);
            echo '<script type="text/javascript">ok();</script>';
            header("refresh:2;url=visualizzaInsegnamenti.php");
        }

        
        if (isset($_POST["idIns"])){
            $idIns = $_POST["idIns"];
            $descrizione = $_POST["descrizione"];
            $cfu = $_POST["cfu"];
            $idDoc = $_POST["docente"];

            if (trim($descrizione) == ""){
                $_SESSION["error"] = "descrizione non valida";
                @redirect("aggiornaInsegnamento.php");
            }
            
            $dbConnect = openConnection();
            $query = "call ".UniNostra.".updateInsegnamento($1,$2,$3,$4)";
            $res = pg_prepare($dbConnect, "", $query);
            $row = @pg_execute($dbConnect, "", array($idIns,$descrizione,$cfu,$idDoc));
            
            if(!$row){
                $_SESSION["error"] = parseError(pg_last_error());
            }else{
                $_SESSION["ok"] = "Modifiche effettuate!";
            }
            @redirect("aggiornaInsegnamento.php");
        }

        $dbConnect = openConnection();
        $query = "select * from ".UniNostra.".infoIns($1)";
        $res = pg_prepare($dbConnect, "", $query);
        $row = pg_fetch_assoc(pg_execute($dbConnect, "", array($_SESSION["modifica"])));
        endConnection($dbConnect);
        $docente = $row["iddoc"];
        $nome =  $row["nome"];
        $cognome = $row["cognome"];
    ?>
    
    <div class="centroForm" id = "elliminaForm">
        <form action="aggiornaInsegnamento.php" method="post">
            <div class="row mb-4">
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="idIns">Codice Insegnamento</label>
                        <input type="text" id="idIns" class="form-control" name="idIns" value="<?php echo $row["codice"];?>" readonly/>
                    </div>
                </div>
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="nomeIns">Nome Insegnamento</label>
                        <input type="text" id="idIns" class="form-control" name="nomeIns" value="<?php echo $row["nomeins"];?>" readonly/>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="descrizione">Descrizione</label>
                        <input type="text" id="cfu" class="form-control" name="descrizione" value="<?php echo $row["descrizione"];?>" />
                    </div>
                </div>
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="cfu">Cfu</label>
                        <input min="1" max="30" type="number" id="cfu" class="form-control" name="cfu" value="<?php echo $row["cfu"];?>" readonly/>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="docente">Docente Responsabile</label>
                        <select class = "form-select" name="docente" id="docente">
                        <?php
                            $dbConnect = openConnection();
                            $query = "select * from ".UniNostra.".docentiDisponibili($1)";
                            $res2 = pg_prepare($dbConnect, "", $query);
                            $row2 = pg_execute($dbConnect, "", array($_SESSION["idUtente"]));
                            endConnection($dbConnect);
                           
                            echo "<option value=".$docente." selected>".$nome." ".$cognome."</option>";
                            while ($res2 = pg_fetch_row($row2)) {
                               echo "<option value=".$res2[1].">".$res2[2]." ".$res2[3]."</option>";
                            }
                        ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class ="btnSub">
                <button type='submit' class='btn btn-warning btn-block mb-4'>Modifica</button>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
  </body>
</html>