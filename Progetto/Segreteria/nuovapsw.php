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
      if(!isset( $_SESSION["nuovapsw"])){
        redirect("recuperopsw.php");
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
            header("refresh:2;url=recuperopsw.php");
        }

        
        if(isset($_POST["psw"])){
            $psw = $_POST["psw"];
            $confermapsw = $_POST["confermapsw"];

            if (trim($psw) == "" || strlen($psw)< 4 ){
                $_SESSION["error"] = "Password non valida";
                @redirect("nuovapsw.php");
            }else{
                if (trim($psw) != trim($confermapsw)){
                    $_SESSION["error"] = "Le password non combaciano";
                    @redirect("nuovapsw.php");
                }
            }

            if(!isset($_SESSION["error"])){
                $dbConnect = openConnection();
                $query = "call ".UniNostra.".recuperopsw($1,$2,$3)";
                $res = pg_prepare($dbConnect, "", $query);
                $row = @pg_execute($dbConnect, "", array($_SESSION["idUtente"],$_SESSION["nuovapsw"],$psw));

                if(!$row){
                    $_SESSION["error"] = parseError(pg_last_error());
                }else{
                    $_SESSION["ok"] = "Password modificata!";
                }
                @redirect("nuovapsw.php");
            }
        }

        $dbConnect = openConnection();
        $query = "select * from ".UniNostra.".infoutente($1)";
        $res = pg_prepare($dbConnect, "", $query);
        $row = pg_fetch_assoc(@pg_execute($dbConnect, "", array($_SESSION["nuovapsw"])));

    ?>
    
    <div class="centroForm" id = "elliminaForm">
        <form action="nuovapsw.php" method="post">
            <div class="row mb-4">
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="nome">Nome: </label>
                        <input type="text" id="nome" class="form-control" name="nome" readonly value = "<?php echo $row['nome'].' '.$row['cognome']; ?>"/>
                    </div>
                </div>
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="email">Email: </label>
                        <input type="text" id="email" class="form-control" name="email" readonly value = "<?php echo $row['email']; ?>" />
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="psw">Nuova Password (almeno 4 caratteri): </label>
                        <input type="password" id="psw" class="form-control" name="psw" required/>
                    </div>
                </div>
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="confermapsw">Conferma: </label>
                        <input type="password" id="confermapsw" class="form-control" name="confermapsw" required/>
                    </div>
                </div>
            </div>
            <div class ="btnSub">
                <button type='submit' class='btn btn-warning btn-block mb-4'>Modifica</button>
            </div>
        </form>
    </div>
    <div class="centroForm" style="margin-top:10px;" id="indietro">
        <h5 style="display:inline;">Torna a visualizza utenti: </h5><button class ="btn btn-primary btn-sm" onclick="indietro();">Indietro</button>
    </div>
    <script>
        function indietro(){
            location.href = "http://127.0.0.1/Progetto/Segreteria/recuperopsw.php";
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
