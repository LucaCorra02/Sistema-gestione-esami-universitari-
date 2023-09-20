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
      if(!(isset($_SESSION["addStud"]))){
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

        if (isset($_POST["email"])){
            $nome = $_POST["nome"];
            $cognome = $_POST["cognome"];
            $email = $_POST["email"];
            $cfu = $_POST["cfu"];
            $psw = $_POST["psw"];
            $confpsw = $_POST["confpsw"];
            $telefono = $_POST["telefono"];
            $residenza = $_POST["residenza"];
            $data = $_POST["data"];
            $cdl = $_POST["idCdl"];

            $_SESSION["arr"] = array($nome,$cognome,$email,$cfu,$psw,$confpsw,$telefono,$residenza,$data,$cdl);

            if (trim($nome)==""){
                $_SESSION["error"] = "nome non valido";
                @redirect("aggiungiStudente.php");
            }else{
                if(trim($cognome)==""){
                    $_SESSION["error"] = "cognome non valido";
                    @redirect("aggiungiStudente.php");
                }else{
                    if(trim($email)=="" || !str_contains($email,'.')){
                        $_SESSION["error"] = "email non valida";
                        @redirect("aggiungiStudente.php");
                    }else{
                        $email = $email."@studenti.UniNostra";
                        if(trim($cfu)==""){
                            $_SESSION["error"] = "codice fiscale non valido";
                            @redirect("aggiungiStudente.php");
                        }else{
                            if(trim($psw) == "" || strlen($psw) < 4){
                                $_SESSION["error"] = "password non valida";
                                @redirect("aggiungiStudente.php");
                            }else{
                                if($psw != $confpsw){
                                    $_SESSION["error"] = "le password non combaciano";
                                    @redirect("aggiungiStudente.php");
                                }else{
                                    if(trim($telefono) == ""){
                                        $_SESSION["error"] = "numero di telefono non valido";
                                        @redirect("aggiungiStudente.php");
                                    }else{
                                        if(trim($residenza)==""){
                                            $_SESSION["error"] = "residenza non valida";
                                            @redirect("aggiungiStudente.php");
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            if (!isset($_SESSION["error"])){
                $dbConnect = openConnection();
                $query = "call ".UniNostra.".aggiungiStudente($1,$2,$3,$4,$5,$6,$7,$8,$9)";
                $res = pg_prepare($dbConnect, "", $query);
                $row = @pg_execute($dbConnect, "", array($nome,$cognome,$email,$psw,$cfu,$telefono,$residenza,$data,$cdl));

                if(!$row){
                    $_SESSION["error"] = parseError(pg_last_error());
                }else{
                    $_SESSION["ok"] = "Studente iscritto con successo!";
                }
                endConnection($dbConnect);
                @redirect("aggiungiStudente.php");
            }





        }
    ?>

     <div class = "centroNoBordo">
        <h1>Aggiungi Studente al cdl : <?php echo $_SESSION["addStud"]; ?></h1>
    </div>
    <div class="centroForm" id = "elliminaForm" style="margin-top:15px; margin-bottom:8%;">
        <form action="aggiungiStudente.php" method="post">
            <div class="row mb-4">
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="nome">Nome: </label>
                        <input type="text" id="nome" class="form-control" name="nome" required value = "<?php if(isset($_SESSION['arr'])){echo $_SESSION['arr'][0];} ?>"/>
                    </div>
                </div>
                <div class="col">
                    <div class="form-outline">
                        <label class="form-label" for="cognome">Cognome: </label>
                        <input type="text" id="cognome" class="form-control" name="cognome" required value = "<?php if(isset($_SESSION['arr'])){echo $_SESSION['arr'][1];} ?>"/>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <label class="form-label" for="email">Email: </label>
                    <input type="text" id="email" class="form-control" name="email" required value = "<?php if(isset($_SESSION['arr'])){echo $_SESSION['arr'][2];} ?>"/>
                </div>
                <div class="col">
                    <label class="form-label" for="cfu">cfu: max 16 caratteri</label>
                    <input type="text" maxlength="16" id="cfu" class="form-control" name="cfu" required value = "<?php if(isset($_SESSION['arr'])){echo $_SESSION['arr'][3];} ?>"/>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <label class="form-label" for="psw">Password: minimo 4 caratteri</label>
                    <input type="password" id="psw" class="form-control" name="psw" required autocomplete="off" value = "<?php if(isset($_SESSION['arr'])){echo $_SESSION['arr'][4];} ?>"/>
                </div>
                <div class="col">
                    <label class="form-label" for="confpssw">Conferma Password: </label>
                    <input type="password" id="confpsw" class="form-control" name="confpsw" required autocomplete="off" value = "<?php if(isset($_SESSION['arr'])){echo $_SESSION['arr'][5];} ?>"/>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <div class="col">
                        <label class="form-label" for="telefono">Telefono: </label>
                        <input type="text" maxlength="20" id="telefono" class="form-control" name="telefono" required value = "<?php if(isset($_SESSION['arr'])){echo $_SESSION['arr'][6];} ?>"/>
                    </div>
                    <div class="col">
                        <label class="form-label" for="residenza">Residenza: </label>
                        <input type="text" id="residenza" class="form-control" name="residenza" required value = "<?php if(isset($_SESSION['arr'])){echo $_SESSION['arr'][7];} ?>"/>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <label class="form-label" for="datepicker">Data di nascita: </label>
                    <div id="datepicker" class="input-group date" data-date-format="dd-mm-yyyy">
                        <input required class="form-control" type="text" readonly name="data" />
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
                    <input type="text" maxlength="20" id="idCdl" class="form-control" name="idCdl" required value ="<?php echo $_SESSION['addStud'];?>" readonly/>
                </div>
            </div>
            <div class ="btnSub">
                <button type='submit' class='btn btn-primary btn-block mb-4'>Iscrivi</button>
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