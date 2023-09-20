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
        }

        if (!isset($_SESSION["error"])){
            $dbConnect = openConnection();
            $query = "select * from ".UniNostra.".numInsegnamenti($1)";
            $res = pg_prepare($dbConnect, "", $query);
            $row = pg_fetch_assoc(@pg_execute($dbConnect, "", array($_SESSION["idUtente"])));
            $num = $row["numins"];
        }
        
        endConnection($dbConnect);

        if (isset($_POST["idIns"])){
            $idIns = $_POST["idIns"];
            $cdl = $_POST["idCdl"];
            $aula = $_POST["aula"];
            $data =  parseDataReverse($_POST["data"]);
            $oraIn =  $_POST["oraInizio"];
            $oraFi =  $_POST["oraFine"];
            $descrizione = $_POST["descrizione"];
            
            $regex = '/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/';
            if (!preg_match($regex, $oraIn) || !preg_match($regex, $oraFi)) {
                $_SESSION["error"] = "Errore: Formato ora inizio o ora fine incrorretto!";
                @redirect("creaAppelli.php");
            }
            $regex = "/\w+\w/i";
            if (!preg_match($regex, $aula)) {
                $_SESSION["error"] = "Errore: Formato aule non corretto!";
                @redirect("creaAppelli.php");
            }

            if (!isset($_SESSION["error"])){
                $dbConnect = openConnection();
                $query = "call ".UniNostra.".inserimentoAppello($1,$2,$3,$4,$5,$6,$7,$8)";
                $res = pg_prepare($dbConnect, "", $query);
                $row = @pg_execute($dbConnect, "", array($idIns,$_SESSION["idUtente"],$aula,$descrizione,$data,$oraIn,$oraFi,$cdl));
            }

            if(!$row){
                $_SESSION["error"] = parseError(pg_last_error());
                $list[] = ['idIns' => $idIns, 'cdl' => $cdl, 'aula' => $aula, 'data' => $data, 'oraIn' => $oraIn, 'oraFi' => $oraFi, 'descrizione' => $descrizione];
                $_SESSION["arr"] = $list; 
            }else{
                $_SESSION["ok"] = "Appello inserito con succeso";
                if(isset($_SESSION["arr"])){
                    unset($_SESSION["arr"]);
                }
            }
            @redirect("creaAppelli.php");
            unset($_POST["idIns"]);
            
        }
    ?>
    
    <?php
        if ($num==0){
            echo "<div class='alert alert-warning centroS' role='alert'><h4>Il docente non è responsabile di alcun insegnamento <a href='HomeDocenti.php' class='alert-link'>vai alla home</a></h4>
            </div>";
        }else{     
    ?>
    <div class="centroForm">
        <form action="creaAppelli.php" method="post">
            <div class="row mb-4">
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="idIns">Codice Insegnamento</label>
                        <select class="form-select" aria-label="Select Insegnamento" id="idIns" name="idIns">
                            <?php
                                $dbConnect = openConnection();
                                $query = "select * from ".UniNostra.".idCorsiDoc($1)";
                                $res = pg_prepare($dbConnect, "", $query);
                                $row = pg_execute($dbConnect, "", array($_SESSION["idUtente"]));
                                endConnection($dbConnect);
                                
                                $f = 1;
                                while ($res = pg_fetch_row($row)) {
                                    if ($f==1){
                                        echo "<option selected value=".$res[0].">".$res[0]." - ".$res[1]."</option>";
                                    }else{
                                        echo "<option value=".$res[0].">".$res[0]." - ".$res[1]."</option>";
                                    }
                                    $f+=1;
                                }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="cdl">Cdl</label>
                        <select class="form-select" aria-label="Select Cdl" id="cdl" name ="idCdl">
                            <?php
                               creaMenuTendina("cdlDocente",$_SESSION["idUtente"]);
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="aula">Aula/e Aula1+Aula2..</label>
                        <input required type="text" id="aula" class="form-control" name="aula" autocomplete="off" value ="<?php if(isset($_SESSION['arr'])){echo $_SESSION['arr'][0]['aula'];} ?>" />
                    </div>
                </div>
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="datepicker">Seleziona Data</label>
                        <div id="datepicker" class="input-group date" data-date-format="dd-mm-yyyy">
                            <input required class="form-control" type="text" readonly name="data" value ="<?php if(isset($_SESSION['arr'])){echo $_SESSION['arr'][0]['data'];} ?>"/>
                            <span class="input-group-addon"></span>
                        </div>
                        <script>
                            $(function () {
                                $("#datepicker").datepicker({ 
                                    autoclose: true, 
                                    todayHighlight: true,
                                }).datepicker('update', new Date());
                            });
                        </script>            
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="oraInizio">Ora Inizio: hh:mm:ss</label>
                        <input required type="text" id="oraInizio" class="form-control" name="oraInizio" autocomplete="off" value ="<?php if(isset($_SESSION['arr'])){echo $_SESSION['arr'][0]['oraIn'];} ?>"/>
                    </div>
                </div>
                <div class="col">
                    <div class="form-outline">
                        <div class="form-outline">
                            <label class="form-label" for="oraFine">Ora Fine: hh:mm:ss</label>
                            <input required type="text" id="oraFine" class="form-control" name="oraFine" autocomplete="off" value ="<?php if(isset($_SESSION['arr'])){echo $_SESSION['arr'][0]['oraFi'];} ?>"/>
                        </div>           
                    </div>
                </div>
            </div>
            <div class="form-outline mb-4">
                <label class="form-label" for="descrizione">Descrizione</label>
                <input required type="text" id="descrizione" class="form-control" name="descrizione" value ="<?php if(isset($_SESSION['arr'])){echo $_SESSION['arr'][0]['descrizione'];} ?>"/>
            </div>
            <div class ="btnSub">
                <button type="submit" class="btn btn-primary btn-block mb-4">Crea</button>
            </div>
        </form>
    </div>
    <?php
        }
        
    ?>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"> </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
    <?php
        require("../footer.php");
    ?>
  </body>
</html>