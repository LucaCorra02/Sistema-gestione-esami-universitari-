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
      if(!isset($_SESSION["modificaStud"])){
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

        
        if (isset($_POST["telefono"])){
            $telefono = $_POST["telefono"];
            $residenza = $_POST["residenza"];
            $data = $_POST["data"];

            if(trim($telefono) == "" || strlen($telefono) > 20){
                $_SESSION["error"] = "numero di telefono non valido";
                @redirect("aggiornainfostudente.php");
            }else{
                if(trim($residenza)==""){
                    $_SESSION["error"] = "residenza non valida";
                    @redirect("aggiornainfostudente.php");
                }
            }
        
            if(!isset($_SESSION["error"])){
                $dbConnect = openConnection();
                $query = "call ".UniNostra.".updateInfoStudente($1,$2,$3,$4)";
                $res = pg_prepare($dbConnect, "", $query);
                $row = @pg_execute($dbConnect, "", array($_SESSION["modificaStud"],$telefono,$data,$residenza));
                if(!$row){
                    $_SESSION["error"] = parseError(pg_last_error());
                }else{
                    $_SESSION["ok"] = "Informazioni modificate con successo!";
                }
                endConnection($dbConnect);
                @redirect("aggiornainfostudente.php");
            }
            
        }
        
        $matricola = $_SESSION["modificaStud"];
        $dbConnect = openConnection();
        $query = "select * from ".UniNostra.".visualizzaInfoDisiscrizione($1)";
        $res = pg_prepare($dbConnect, "", $query);
        $row = pg_fetch_assoc(pg_execute($dbConnect, "", array($matricola)));
        endConnection($dbConnect);
        //print_r($row);
    ?>

     <div class = "centroNoBordo">
        <h1>Modifica informazioni matricola: <?php echo $matricola;?></h1>
    </div>
    <div class="centroForm" id = "elliminaForm" style="margin-top:15px; margin-bottom:8%;">
        <form action="aggiornainfostudente.php" method="post">
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
                        <input type="text" maxlength="20" id="telefono" class="form-control" name="telefono" value = "<?php echo $row['telefono'];?>" required/>
                    </div>
                    <div class="col">
                        <label class="form-label" for="residenza">Residenza: </label>
                        <input type="text" id="residenza" class="form-control" name="residenza" value = "<?php echo $row['indirizzo'];?>" required/>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <label class="form-label" for="datepicker">Data di nascita: </label>
                    <div id="datepicker" class="input-group date" data-date-format="dd-mm-yyyy">
                        <input required class="form-control" type="text" name="data" value="<?php echo $row['datanascita'];?>" required/>
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
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"> </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
  </body>
</html>