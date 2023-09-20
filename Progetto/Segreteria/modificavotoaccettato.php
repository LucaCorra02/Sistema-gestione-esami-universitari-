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
      if(!isset($_SESSION["modificavoto"])){
        redirect("iscrizioneappelli.php");
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
            header("refresh:2;url=iscrizioniappelli.php");
        }

        $arr = explode("+",$_SESSION["modificavoto"]);
        $matricola = $arr[0];
        $idApp = $arr[1];

        if(isset($_POST["voto"])){
            $voto = $_POST["voto"];
            $lode = "false";
            if ($voto == "31"){
                $voto = "30";
                $lode = "true";
            }

            if (!isset($_SESSION["error"])){
                $dbConnect = openConnection();
                $query = "call ".UniNostra.".cambiavotoaccettato($1,$2,$3,$4)";
                $res = pg_prepare($dbConnect, "", $query);
                $row = @pg_execute($dbConnect, "", array($matricola,$idApp,$voto,$lode));

                if(!$row){
                    $_SESSION["error"] = parseError(pg_last_error());
                }else{
                    $_SESSION["ok"] = "Modifiche effettuate!";
                }
                endConnection($dbConnect);
                @redirect("modificavotoaccettato.php");
            }
        }
        $dbConnect = openConnection();
        $query = "select * from ".UniNostra.".esitoStud($1,$2)";
        $res = pg_prepare($dbConnect, "", $query);
        $row = pg_fetch_assoc(@pg_execute($dbConnect, "", array($matricola,$idApp)));
    ?>

     <div class = "centroNoBordo">
        <h1>Modifica Esito</h1>
    </div>
    <div class="centroForm" id = "elliminaForm" style="margin-top:15px; margin-bottom:8%;">
        <form action="modificavotoaccettato.php" method="post">
            <div class="row mb-4">
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="nome">Nome: </label>
                        <input type="text" id="nome" class="form-control" name="nome" readonly value="<?php echo $row['nome']; ?>"/>
                    </div>
                </div>
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="matricola">Matricola: </label>
                        <input type="text" id="matricola" class="form-control" name="matricola" readonly value="<?php echo $row['matricola']; ?>" />
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <label class="form-label" for="nomeins">Nome Insegnamento: </label>
                    <input type="text" id="nomeins" class="form-control" name="nomeins" readonly value="<?php echo $row['nomeins'].' - '.$row['cfu'].' cfu - '.$row['cdl']; ?>"/>
                </div>
                <div class="col">
                    <label class="form-label" for="voto">Voto: 31 per lode</label>
                    <input type="number" min = "18" max ="31" id="voto" class="form-control" name="voto" required value="<?php echo $row['votoesame']; ?>"/>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="stato">Stato: </label>
                        <input type="text" id="stato" class="form-control" name="stato" value = "Accettato" readonly/>
                    </div>
                </div>
            </div>
            <div class ="btnSub">
                <button type='submit' class='btn btn-primary btn-block mb-4'>Modifica</button>
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