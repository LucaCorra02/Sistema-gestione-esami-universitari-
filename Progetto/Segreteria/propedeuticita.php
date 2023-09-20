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
      if (!isset($_SESSION["prop"])){
        redirect("visualizzaPianoStudi.php");
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
        }

        $arrRis = explode("+",$_SESSION["prop"]);
        $ins = $arrRis[0];
        $cdl = $arrRis[1];


        if (isset($_POST["addprop"])){
            $prop = $_POST["addprop"];
            $dbConnect = openConnection();
            if (isset($_POST["aggiungi"])){
                $query = "call ".UniNostra.".inserisciPropedeuticita($1,$2,$3)";
                $res = pg_prepare($dbConnect, "", $query);
                $row = @pg_execute($dbConnect, "", array($ins,$prop,$cdl));

                if(!$row){
                    $_SESSION["error"] = parseError(pg_last_error());
                }else{
                    $_SESSION["ok"] = "Propedeuticità: ".$ins." -> ".$prop." inserita";
                }
                @redirect("propedeuticita.php");
               
            }else{
                if (isset($_POST["rimuovi"])){
                    $query = "call ".UniNostra.".elliminaPropedeuticita($1,$2,$3)";
                    $res = pg_prepare($dbConnect, "", $query);
                    $row = @pg_execute($dbConnect, "", array($ins,$prop,$cdl));
    
                    if(!$row){
                        $_SESSION["error"] = parseError(pg_last_error());
                    }else{
                        $_SESSION["ok"] = "Propedeuticità: ".$ins." -> ".$prop." elliminata";
                    }
                    @redirect("propedeuticita.php");
                }
            }
            endConnection($dbConnect);
        }
        $dbConnect = openConnection();
        $query = "select * from ".UniNostra.".visualizzaProp($1,$2)";
        $res = pg_prepare($dbConnect, "", $query);
        $row = pg_fetch_assoc(pg_execute($dbConnect, "", array($cdl,$ins)));
        endConnection($dbConnect);
        
        $propedeuticita = $row["visualizzaprop"];
        if ($propedeuticita == ""){
            $propedeuticita = "Nessuna";
        }
       
        $dbConnect = openConnection();
        $query = "select * from ".UniNostra.".infoIns($1)";
        $res = pg_prepare($dbConnect, "", $query);
        $row = pg_fetch_assoc(pg_execute($dbConnect, "", array($ins)));
        endConnection($dbConnect);
        $docente = $row["iddoc"];
        $nome =  $row["nome"];
        $cognome = $row["cognome"];
       

    ?>
     <div class = "centroNoBordo">
        <h1>Aggiugni propedeuticita per l'insegnamento : <?php echo $ins; ?></h1>
    </div>
    <div class="centroForm" id = "elliminaForm" style="margin-top:15px;">
        <form action="propedeuticita.php" method="post">
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
                <label class="form-label" for="prope">Propedeuticità Attuali : </label>
                <h5 class="form-input" style='color:red;'><?php echo $propedeuticita; ?></h5>
                
            </div>
            <div class="row mb-4">
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="addprop">Propedeuticità disponibili</label>
                        <select class = "form-select" name="addprop" id="addprop">
                        <?php
                            $dbConnect = openConnection();
                            $query = "select * from ".UniNostra.".propDisponibili($1,$2)";
                            $res2 = pg_prepare($dbConnect, "", $query);
                            $row2 = pg_execute($dbConnect, "", array($cdl,$ins));
                            endConnection($dbConnect);

                            while ($res2 = pg_fetch_row($row2)) {
                               echo "<option value=".$res2[0].">".$res2[0]." - ".$res2[1]." -Anno erogazione:  ".$res2[2]."</option>";
                            }
                        ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <div class ="btnSub">
                        <button type='submit' class='btn btn-success btn-block mb-4' name="aggiungi" value ="aggiungi">Aggiungi</button>
                    </div>
                </div>
                <div class="col">
                    <div class ="btnSub">
                        <button type='submit' class='btn btn-danger btn-block mb-4' name="rimuovi" value="rimuovi">Rimuovi</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="centroForm" style="margin-top:10px;" id="indietro">
        <h3>Torna a visualizza insegnamenti: </h3><button class ="btn btn-primary" onclick="indietro();">Indietro</button>
    </div>
    <?php
        require("../footer.php");
        //spazioFooter();
    ?>
    <script>
        function indietro(){
            location.href = "http://127.0.0.1/Progetto/Segreteria/visualizzaPianoStudi.php";
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
  </body>
</html>