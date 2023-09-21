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
      if(!(isset($_SESSION["addseg"]))){
        redirect("visualizzasegretari.php");
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
            header("refresh:2;url=visualizzasegretari.php");
        }

        if (isset($_POST["email"])){
            $nome = $_POST["nome"];
            $cognome = $_POST["cognome"];
            $email = $_POST["email"];
            $cfu = $_POST["cfu"];
            $psw = $_POST["psw"];
            $confpsw = $_POST["confpsw"];
            $telefono = strval($_POST["telefono"]);
            $residenza = $_POST["residenza"];
            $nomedip = $_POST["nomedip"];

            
            $_SESSION["arr"] = array($nome,$cognome,$email,$cfu,$psw,$confpsw,$telefono,$residenza,$nomedip);

            if (trim($nome)==""){
                $_SESSION["error"] = "nome non valido";
                @redirect("aggiungisegretario.php");
            }else{
                if(trim($cognome)==""){
                    $_SESSION["error"] = "cognome non valido";
                    @redirect("aggiungisegretario.php");
                }else{
                    if(trim($email)=="" || !str_contains($email,'.')){
                        $_SESSION["error"] = "email non valida";
                        @redirect("aggiungisegretario.php");
                    }else{
                        $email = $email."@segreteria.UniNostra";
                        if(trim($cfu)==""){
                            $_SESSION["error"] = "codice fiscale non valido";
                            @redirect("aggiungisegretario.php");
                        }else{
                            if(trim($psw) == "" || strlen($psw) < 4){
                                $_SESSION["error"] = "password non valida";
                                @redirect("aggiungisegretario.php");
                            }else{
                                if($psw != $confpsw){
                                    $_SESSION["error"] = "le password non combaciano";
                                    @redirect("aggiungisegretario.php");
                                }else{
                                    if(trim($telefono) == ""){
                                        $_SESSION["error"] = "numero di telefono non valido";
                                        @redirect("aggiungisegretario.php");
                                    }else{
                                        if(trim($residenza)==""){
                                            $_SESSION["error"] = "residenza non valida";
                                            @redirect("aggiungisegretario.php");
                                        }else{
                                            if(trim($nomedip )==""){
                                                $_SESSION["error"] = "nome dipartimeto non valido";
                                                @redirect("aggiungisegretario.php");
                                            }
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
                $query = "call ".UniNostra.".inserisciSegretario($1,$2,$3,$4,$5,$6,$7,$8)";
                $res = pg_prepare($dbConnect, "", $query);
                $row = @pg_execute($dbConnect, "", array($nome,$cognome,$email,$psw,$cfu,$residenza,$nomedip,$telefono));

                if(!$row){
                    $_SESSION["error"] = parseError(pg_last_error());
                }else{
                    $_SESSION["ok"] = "Segretario aggiunto con successo!";
                }
                endConnection($dbConnect);
                @redirect("aggiungisegretario.php");
            }
        
        }
    ?>

     <div class = "centroNoBordo">
        <h1>Aggiungi Segretario: </h1>
    </div>
    <div class="centroForm" id = "elliminaForm" style="margin-top:15px;">
        <form action="aggiungisegretario.php" method="post">
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
                        <input type="text" maxlength="10" id="telefono" class="form-control" name="telefono" required value = "<?php if(isset($_SESSION['arr'])){echo $_SESSION['arr'][6];} ?>"/>
                    </div>
                    <div class="col">
                        <label class="form-label" for="residenza">Indririzzo ufficio: </label>
                        <input type="text" id="residenza" class="form-control" name="residenza" required value = "<?php if(isset($_SESSION['arr'])){echo $_SESSION['arr'][7];} ?>"/>
                    </div>
                    <div class="col">
                        <label class="form-label" for="nomedip">Nome dipartimento: </label>
                        <input type="text" id="nomedip" class="form-control" name="nomedip" required value = "<?php if(isset($_SESSION['arr'])){echo $_SESSION['arr'][8];} ?>"/>
                    </div>
                </div>
            </div>
            <div class ="btnSub">
                <button type='submit' class='btn btn-primary btn-block mb-4'>Iscrivi</button>
            </div>
        </form>
    </div>
    <div class="centroForm" style="margin-top:5px;" id="indietro">
        <h5 style="display:inline;">Torna a visualizza docenti: </h5><button class ="btn btn-primary btn-sm" onclick="indietro();">Indietro</button>
    </div>
    <script>
        function indietro(){
            location.href = "http://127.0.0.1/Progetto/Segreteria/visualizzadocenti.php";
        }
    </script>
    <?php
        require("../footer.php");
        //spazioFooter();
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
  </body>
</html>