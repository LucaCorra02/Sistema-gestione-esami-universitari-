<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home Segreteria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../Css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
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
      if(!(isset($_SESSION["disiscrivi"]))){
        redirect("studentiCdl.php");
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
            header("refresh:2;url=visualizzaStudenti.php");
        }

        if (isset($_POST["nome"])){
            $dbConnect = openConnection();
            $query = "call ".UniNostra.".registraLaurea($1,$2,$3)";
            $res = pg_prepare($dbConnect, "", $query);
            $row = @pg_execute($dbConnect, "", array($_SESSION["disiscrivi"],"Ritirato",null));
            if(!$row){
                $_SESSION["error"] = parseError(pg_last_error());
            }else{
                $_SESSION["dis"] = "true";
                $_SESSION["ok"] = "Studente disiscritto con successo!";
            }
            endConnection($dbConnect);
            @redirect("disiscriviStudente.php");
        }
        
        $matricola = "";
        $row = array("nome"=>"","cognome"=>"","email"=>"","cfu"=>"","telefono"=>"","indirizzo"=>"","datanascita"=>"","idcorso"=>"");
        
        if(!isset($_SESSION["dis"])){
            $matricola = $_SESSION["disiscrivi"];
            $dbConnect = openConnection();
            $query = "select * from ".UniNostra.".visualizzaInfoDisiscrizione($1)";
            $res = pg_prepare($dbConnect, "", $query);
            $row = pg_fetch_assoc(pg_execute($dbConnect, "", array($matricola)));
            endConnection($dbConnect);
        }

        //print_r($row);
    ?>

     <div class = "centroNoBordo">
        <h1>Conferma Disiscrizione matricola: <?php echo $matricola;?></h1>
    </div>
    <div class="centroForm" id = "elliminaForm" style="margin-top:15px; margin-bottom:8%;">
        <form action="disiscriviStudente.php" method="post">
            <div class="row mb-4">
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="nome">Nome: </label>
                        <input type="text" id="nome" class="form-control" name="nome"  value = "<?php echo $row['nome'];?>" readonly/>
                    </div>
                </div>
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="cognome">Cognome: </label>
                        <input type="text" id="cognome" class="form-control" name="cognome" value = "<?php echo $row['cognome'];?>" readonly/>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <label class="form-label" for="email">Email: </label>
                    <input type="text" id="email" class="form-control" name="email" readonly value = "<?php echo $row['email']; ?>"/>
                </div>
                <div class="col">
                    <label class="form-label" for="cfu">cfu: max 16 caratteri</label>
                    <input type="text" maxlength="16" id="cfu" class="form-control" name="cfu" readonly value = "<?php echo $row['cfu']; ?>"/>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <div class="col">
                        <label class="form-label" for="telefono">Telefono: </label>
                        <input type="text" maxlength="20" id="telefono" class="form-control" name="telefono" readonly value = "<?php echo $row['telefono'];?>"/>
                    </div>
                    <div class="col">
                        <label class="form-label" for="residenza">Residenza: </label>
                        <input type="text" id="residenza" class="form-control" name="residenza" readonly value = "<?php echo $row['indirizzo'];?>"/>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <label class="form-label" for="data">Data di nascita: </label>
                    <input type="text" id="data" class="form-control" name="data" value ="<?php echo $row['datanascita'];?>" readonly/>
                </div>
                <div class="col">
                    <label class="form-label" for="idCdl">Id Corso: </label>
                    <input type="text" maxlength="20" id="idCdl" class="form-control" name="idCdl" required value ="<?php  echo $row['idcorso']; ?>" readonly/>
                </div>
            </div>
            <div class ="btnSub">
                <button type='submit' class='btn btn-primary btn-block mb-4'>Conferma</button>
            </div>
        </form>
    </div>
    <?php
        require("../footer.php");
        //spazioFooter();
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
  </body>
</html>