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
      if(!isset($_SESSION["laurea"])){
        redirect("laureastudenti.php");
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
            header("refresh:2;url=laureastudenti.php");
        }


        if (isset($_POST["voto"])){

            $dbConnect = openConnection();
            $query = "select * from ".UniNostra.".mediaStudente($1)";
            $res = pg_prepare($dbConnect, "", $query);
            $row = pg_fetch_assoc(@pg_execute($dbConnect, "", array($_SESSION["laurea"])));

            $media = $row["mediastudente"];
            $voto = $_POST["voto"];
            
            switch ($media) {
                case $media >= 18 and $media <= 21:
                    if ($voto > 77){
                        $_SESSION["error"] = "il voto di laurea deve essere nel range 60-77";
                        @redirect("confermalaurea.php");
                    }
                    break;
                case $media >= 22 and $media <= 25:
                    if ($voto < 78 or $voto > 92){
                        $_SESSION["error"] = "il voto di laurea deve essere nel range 78-92";
                        @redirect("confermalaurea.php");
                    }
                    break;
                case $media >= 26 and $media <= 27:
                    if ($voto < 93 or $voto > 100){
                        $_SESSION["error"] = "il voto di laurea deve essere nel range 93-100";
                        @redirect("confermalaurea.php");
                    }
                    break;
                case $media >= 28:
                    if ($voto < 101){
                        $_SESSION["error"] = "il voto di laurea deve essere nel range 101-110";
                        @redirect("confermalaurea.php");
                    }
                    break;
            }
            if(!isset($_SESSION["error"])){
                $dbConnect = openConnection();
                $query = "call ".UniNostra.".registraLaurea($1,$2,$3)";
                $res = pg_prepare($dbConnect, "", $query);
                $row = @pg_execute($dbConnect, "", array($_SESSION["laurea"],"Laureato",$voto));

                if(!$row){
                    $_SESSION["error"] = parseError(pg_last_error());
                }else{
                    $_SESSION["ok"] = "Laurea registrata correttamente!";
                }
                endConnection($dbConnect);
                @redirect("confermalaurea.php");
            }
            
        }
        $dbConnect = openConnection();
        $query = "select * from ".UniNostra.".idAssociatoMatricola($1)";
        $res = pg_prepare($dbConnect, "", $query);
        $row2 = @pg_execute($dbConnect, "", array($_SESSION["laurea"]));

        $row = array("matricola"=>"","telefono"=>"","indirizzoresidenza"=>"","annoiscrizione"=>"","incorso"=>"","idcorso"=>"","nome"=>"","cognome"=>"");
        if($row2){
            $ris= pg_fetch_assoc($row2);
            $mat = $ris["idassociatomatricola"];
            $dbConnect = openConnection();
            $query = "select * from ".UniNostra.".profiloStudente($1)";
            $res = pg_prepare($dbConnect, "", $query);
            $row = pg_fetch_assoc(@pg_execute($dbConnect, "", array($mat)));
            if ($row["incorso"] == "t"){
                $row["incorso"] = "in corso";
            }else{
                $row["incorso"] = "fuori corso";
            }
        }

    ?>

     <div class = "centroNoBordo">
        <h1>Registra Laurea</h1>
    </div>
    <div class="centroForm" id = "elliminaForm" style="margin-top:15px; margin-bottom:8%;">
        <form action="confermalaurea.php" method="post">
            <div class="row mb-4">
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="nome">Nome</label>
                        <input type="text" id="nome" class="form-control" name="nome" readonly value ="<?php echo $row['nome'].' '.$row['cognome'];?>"/>
                    </div>
                </div>
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="matricola">Matricola: </label>
                        <input type="text" id="matricola" class="form-control" name="matricola" readonly value ="<?php echo $row['matricola'];?>"/>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <label class="form-label" for="residenza">Residenza: </label>
                    <input type="text" id="residenza" class="form-control" name="residenza" readonly value ="<?php echo $row['indirizzoresidenza'];?>"/>
                </div>
                <div class="col">
                    <label class="form-label" for="data">Telefono: </label>
                    <input type="text" id="data" class="form-control" name="data" readonly  value ="<?php echo $row['telefono'];?>" />
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <label class="form-label" for="cdl">Corso di laurea: </label>
                    <input type="text" id="cdl" class="form-control" name="cdl" readonly  value ="<?php echo $row['idcorso'];?>"/>
                </div>
                <div class="col">
                    <label class="form-label" for="stato">Stato: </label>
                    <input type="text" id="stato" class="form-control" name="stato" readonly  value ="<?php echo $row['incorso'];?>"/>
                </div>
               
            </div>
            <div class="row mb-4">
                <div class="col">
                    <label class="form-label" for="voto">Voto: </label>
                    <input type="number" min ="60" max ="110" id="voto" class="form-control" name="voto" required />
                </div>
            </div>
            <div class ="btnSub">
                <button type='submit' class='btn btn-primary btn-block mb-4'>Registra</button>
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