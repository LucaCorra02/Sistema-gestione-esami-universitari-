<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profilo Utente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../Css/style.css" />
</head>
  <body>
    
    <?php
      require("../lib/connect.php");
      require("../navbar.php");
      if (! (isset($_SESSION["idUtente"]))){
        redirect("../login.php");
      }

      $dbConnect = openConnection();
      if (!isset($_SESSION["ex"])){
        $query = "select s.matricola from ".UniNostra.".utente u inner join ".UniNostra.".studente s on s.idutente = u.idutente where u.idutente =".$_SESSION["idUtente"].";";
        $row = pg_fetch_assoc(pg_query($dbConnect, $query));

        $query = "call ".UniNostra.".aggiornaInCorso($1)";
        $res = pg_prepare($dbConnect, "", $query);
        $res = pg_execute($dbConnect, "", array($row["matricola"]));

        $query = "select * from ".UniNostra.".profiloStudente($1)";
        $res = pg_prepare($dbConnect, "", $query);
        $row = pg_fetch_assoc(pg_execute($dbConnect, "", array($_SESSION["idUtente"])));
      }else{
        $query = "select * from ".UniNostra.".visualizzaProfiloEx($1)";
        $res = pg_prepare($dbConnect, "", $query);
        $row = pg_fetch_assoc(pg_execute($dbConnect, "", array($_SESSION["idUtente"])));
      }
      //print_r($row);
    ?>
    <div class="centroNoBordo">
        <h1>Profilo Utente</h1>
    </div>
    <div class="container py-5 h-100" style = "width:100%;">
        <div class="row d-flex justify-content-center align-items-center h-100" style = "width:100%;">
            <div class="col col-lg-6 mb-4 mb-lg-0">
                <div class="card mb-3" style="border-radius: .5rem;">
                    <div class="row g-0">
                        <div class="col-md-4 gradient-custom text-center text-white" style="border-top-left-radius: .5rem; border-bottom-left-radius: .5rem; background-color: #0D6EFD;">
                            <div style="margin-top:30px; padding:30px;">
                                <i class="fa-solid fa-user fa-2xl" style="padding-bottom:20%;"></i>
                                <?php
                                    echo "<h5>".$row["nome"]." ".$row["cognome"]."</h5>";
                                    if (isset($_SESSION["ex"])){
                                        if ($row["stato"] == 'Laureato'){
                                            $row["stato"] = $row["stato"]." voto: ".$row["votolaur"];
                                        }
                                        echo "<p>".$row["stato"]."</p>";
                                    }else{
                                        echo "<p>".$_SESSION["tipoUtente"]."</p>";
                                    }
                                   
                                ?>
                            </div>
                        </div>
                        <div class="col-md-8 sfondoSporco">
                            <div class="card-body p-4">
                                <h6>Informazioni</h6>
                                <hr class="mt-0 mb-4">
                                <div class="row pt-1">
                                    <div class="col-6 mb-3">
                                        <h6>Matricola</h6>
                                        <?php
                                            echo "<p class='text-muted'>".decbin(intval($row["matricola"]))."</p>";
                                        ?>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <h6>Corso di laurea</h6>
                                        <?php
                                            echo "<p class='text-muted'>".$row["idcorso"]."</p>";
                                        ?>
                                    </div>
                                </div>
                                <div class="row pt-1">
                                    <div class="col-6 mb-3">
                                        <h6>Stato</h6>
                                        <?php
                                            $stato = "in corso";
                                            if (($row["incorso"]) == "f"){
                                                $stato = "fuori corso";
                                            }
                                            echo "<p class='text-muted'>".$stato."</p>";
                                        ?>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <h6>Data Iscrizione</h6>
                                        <?php
                                            echo "<p class='text-muted'>".parseData($row["annoiscrizione"])."</p>";
                                        ?>
                                    </div>
                                </div>
                                <div class="row pt-1">
                                    <div class="col-6 mb-3">
                                        <h6>Indirizzo</h6>
                                        <?php
                                            echo "<p class='text-muted'>".$row["indirizzoresidenza"]."</p>";
                                        ?>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <h6>Telefono</h6>
                                        <?php
                                            echo "<p class='text-muted'>".$row["telefono"]."</p>";
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php 
      require("../footer.php");
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
  </body>
</html>