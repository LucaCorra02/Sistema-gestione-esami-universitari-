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
      if(!isset( $_SESSION["modificadocente"])){
        redirect("visualizzadocenti.php");
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
            header("refresh:2;url=visualizzadocenti.php");
        }

        if(isset($_POST["indirizzo"])){
            $indirizzo = $_POST["indirizzo"];
            $cellulare = $_POST["cellulare"];

            if (trim($indirizzo) == ""){
                $_SESSION["error"] = "indirizzo non valido";
                @redirect("modificadocente.php");
            }else{
                if (trim($indirizzo) == "" or strlen($cellulare) > 10){
                    $_SESSION["error"] = "cellulare non valido";
                    @redirect("modificadocente.php");
                }
            }
            if(!isset($_SESSION["error"])){
               
                $dbConnect = openConnection();
                $query = "call ".UniNostra.".updateinfodocente($1,$2,$3)";
                $res = pg_prepare($dbConnect, "", $query);
                $row = @pg_execute($dbConnect, "", array($_SESSION["modificadocente"],$indirizzo,$cellulare));

                if(!$row){
                    $_SESSION["error"] = parseError(pg_last_error());
                }else{
                    $_SESSION["ok"] = "Modifiche effettuate!";
                }
                @redirect("modificadocente.php");

            }
        }
        $dbConnect = openConnection();
        $query = "select * from ".UniNostra.".profiloDocente($1)";
        $res = pg_prepare($dbConnect, "", $query);
        $row = pg_fetch_assoc(pg_execute($dbConnect, "", array($_SESSION["modificadocente"])));
        endConnection($dbConnect);
    ?>
    
    <div class="centroForm" id = "elliminaForm">
        <form action="modificadocente.php" method="post">
            <div class="row mb-4">
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="id">Id utente: </label>
                        <input type="text" id="id" class="form-control" name="id" value="<?php echo $_SESSION["modificadocente"];?>" readonly/>
                    </div>
                </div>
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="nome">Nome: </label>
                        <input type="text" id="nome" class="form-control" name="nome" value="<?php echo $row['nome'].' '.$row['cognome'];?>" readonly/>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="indrizzo">Indirizzo Ufficio: </label>
                        <input type="text" id="indrizzo" class="form-control" name="indirizzo" value="<?php echo $row["indufficio"];?>" required/>
                    </div>
                </div>
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="cellulare">Cellulare: </label>
                        <input type="text" id="cellulare" class="form-control" name="cellulare" value="<?php echo $row["telefono"];?>" required />
                    </div>
                </div>
            </div>
            <div class ="btnSub">
                <button type='submit' class='btn btn-warning btn-block mb-4'>Modifica</button>
            </div>
        </form>
    </div>
    <div class="centroForm" style="margin-top:10px;" id="indietro">
        <h5 style="display:inline;">Torna a visualizza docenti: </h5><button class ="btn btn-primary btn-sm" onclick="indietro();">Indietro</button>
    </div>
    <script>
        function indietro(){
            location.href = "http://127.0.0.1/Progetto/Segreteria/visualizzadocenti.php";
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
    <?php
        require("../footer.php");
        spazioFooter();
    ?>
  </body>
</html>