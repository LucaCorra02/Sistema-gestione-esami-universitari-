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
            header("refresh:2;url=visualizzaCdl.php");
        }

        if (isset($_POST["idCdl"])){
            $idCdl = $_POST["idCdl"];
            $nomeCdl = $_POST["nomeCdl"];
            $descrizione = "null";
            if (isset($_POST["descrizione"])){
                $descrizione = $_POST["descrizione"];
            }
            $durata = "3";

            //$_SESSION["arr"] = array($idCdl,$nomeCdl,$descrizione);
            if ($_POST["durataCdl"] != "magistrale" and $_POST["durataCdl"] != "triennale"){
                $_SESSION["error"] = "durata non valida";
                @redirect("creaCdl.php");
            }
            if ($_POST["durataCdl"] == "magistrale"){
                $durata ="5";
            }

            if (trim($idCdl) == "" or strlen($idCdl) > 10 ){
                $_SESSION["error"] = "id cdl non valido ";
                @redirect("creaCdl.php");
            }
            if (trim($nomeCdl)== ""){
                $_SESSION["error"] = "nome cdl non valido ";
                @redirect("creaCdl.php");
            }

            if (!isset($_SESSION["error"])){
                $dbConnect = openConnection();
                $query = "call ".UniNostra.".inserisciCorsoLaurea($1,$2,$3,$4)";
                $res = pg_prepare($dbConnect, "", $query);
                $row = @pg_execute($dbConnect, "", array($idCdl,$nomeCdl,$descrizione,$durata));

                if(!$row){
                    $_SESSION["error"] = parseError(pg_last_error());
                }else{
                    $_SESSION["ok"] = "Corso di laurea inserito!";
                }
                endConnection($dbConnect);
                @redirect("creaCdl.php");
            }
        }
    ?>

     <div class = "centroNoBordo">
        <h1>Crea Corso di Laurea</h1>
    </div>
    <div class="centroForm" id = "elliminaForm" style="margin-top:15px; margin-bottom:8%;">
        <form action="creaCdl.php" method="post">
            <div class="row mb-4">
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="idCdl">Codice Corso di laurea, massimo 10 caratteri</label>
                        <input type="text" id="idCdl" class="form-control" name="idCdl" required/>
                    </div>
                </div>
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="nomeCdl">Nome Corso di laurea</label>
                        <input type="text" id="nomeCdl" class="form-control" name="nomeCdl" required/>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <label class="form-label" for="descrizione">Descrizione: </label>
                    <input type="text" id="descrizione" class="form-control" name="descrizione"/>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="durataCdl">Durata Cdl</label>
                        <select class = "form-select" name="durataCdl" id="durataCdl" required>
                            <option value="triennale">Triennale</option>
                            <option value="magistrale">Magistrale</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class ="btnSub">
                <button type='submit' class='btn btn-primary btn-block mb-4'>Aggiungi</button>
            </div>
        </form>
    </div>
    <?php
        require("../footer.php");
        spazioFooter();
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
  </body>
</html>