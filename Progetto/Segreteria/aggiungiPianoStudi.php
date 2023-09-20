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
            header("refresh:2;url=visualizzaPianoStudi.php");
        }

        if (isset($_POST["annoCorso"])){
            $idCdl = $_SESSION["aggiungi"];
            $idIns = $_POST["selezionaInsegnamento"];
            $annoCorso = $_POST["annoCorso"];

            $dbConnect = openConnection();
            $query = "call ".UniNostra.".inserisciPianoStudi($1,$2,$3)";
            $res = pg_prepare($dbConnect, "", $query);
            $row = @pg_execute($dbConnect, "", array($idCdl,$idIns,$annoCorso));
            
            if(!$row){
                $_SESSION["error"] = parseError(pg_last_error());
            }else{
                $_SESSION["ok"] = "Esame inserito con successo";
            }
        
            @redirect("aggiungiPianoStudi.php");
        }

        if (isset($_POST["aggiungi"])){
        $_SESSION["aggiungi"] = $_POST["aggiungi"] ;
        }

        $dbConnect = openConnection();
        $query = "select * from ".UniNostra.".insegnamentiDisponibili($1)";
        $res = pg_prepare($dbConnect, "", $query);
        $row = @pg_execute($dbConnect, "", array($_SESSION["aggiungi"]));
        endConnection($dbConnect);
    ?>
    
    <div class="centroForm" id = "elliminaForm">
        <form action="aggiungiPianoStudi.php" method="post">
            <div class="row mb-4">
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="ins">Insegnamento</label>
                        <select class = "form-select" name="selezionaInsegnamento" id="ins">
                        <?php
                            $f = 1;
                            $first;
                            echo"sss";
                            while ($res = pg_fetch_row($row)) {
                                if ($f ==1){
                                    $first = $res;
                                }
                                echo "<option value=".$res[0].">".$res[0]." - ".$res[1]."</option>";
                            $f++;
                            }
                        ?>
                        </select>
                    </div>
                </div>
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="annoCorso">Anno Corso</label>
                        <?php
                            $dbConnect = openConnection();
                            $query = "select * from ".UniNostra.".anniPossibili($1)";
                            $res = pg_prepare($dbConnect, "", $query);
                            $row = pg_fetch_assoc(@pg_execute($dbConnect, "", array($_SESSION["aggiungi"])));
                            $ris = str_replace("{", "", $row["annipossibili"]);
                            $ris = str_replace("}", "", $ris);
                            $arrRis = explode( ',',$ris);
                        ?>
                        <select class = "form-select" name="annoCorso" id="annoCorso" required>
                        <?php
                            foreach ($arrRis as &$val) {
                                echo "<option value=".$val.">".$val."</option>";
                            }
                        ?>
                        </select>    
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="cfu">Cfu</label>
                        <input min="0" max="30" type="number" id="cfu" class="form-control" name="cfu" value="<?php echo $first[3];?>" readonly/>
                    </div>
                </div>
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="docente">Docente</label>
                        <input min="0" max="30" type="text" id="docente" class="form-control" name="docente" value="<?php echo $first[4]." ".$first[5];?>" readonly/>
                    </div>
                </div>
            </div>
            <div class ="btnSub">
                <button type='submit' class='btn btn-primary btn-block mb-4'>Invia</button>
            </div>
        </form>
    </div>
    <?php
        if ($f==1){
            elliminaTabella("elliminaForm");
            echo "<div class='alert alert-warning centroS' role='alert'><h4>Non ci sono insegnamenti da aggiungere. <a href='visualizzaPianoStudi.php' class='alert-link'>Visualizza Piano Studi</a></h4>
            </div>";
        }
        require("../footer.php");
        spazioFooter();
    ?>
    <script>
        $('#ins').on('change', function () {
            var select = $(this).val();
            $.ajax({
                type: "POST",
                url: "/Progetto/Segreteria/infoInsegnamento.php",
                data: {'ins': select },
                success: function (json) {
                    var info = jQuery.parseJSON(json);
                    //console.log(info);
                    $("#cfu").val(info["cfu"]);
                    var nome = info["nome"].concat(" ", info["cognome"]);
                    $("#docente").val(nome);
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
  </body>
</html>