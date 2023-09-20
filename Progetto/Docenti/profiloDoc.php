<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profilo Utente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../Css/style.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
</head>
  <body>
    
    <?php
        require("../lib/connect.php");
        require("../navbar.php");
        require("../lib/creaTabella.php");
        if (! (isset($_SESSION["idUtente"]))){
        redirect("../login.php");
        }
      
        $dbConnect = openConnection();
        $query = "select * from ".UniNostra.".profiloDocente($1)";
        $res = pg_prepare($dbConnect, "", $query);
        $row = pg_fetch_assoc(pg_execute($dbConnect, "", array($_SESSION["idUtente"])));
        endConnection($dbConnect);
        //print_r($row);
    ?>
    <div class="centroNoBordo">
        <h1>Profilo Docente</h1>
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
                                    echo "<p>".$_SESSION["tipoUtente"]."</p>";
                                ?>
                            </div>
                        </div>
                        <div class="col-md-8 sfondoSporco">
                            <div class="card-body p-4">
                                <h6>Informazioni</h6>
                                <hr class="mt-0 mb-4">
                                <div class="row pt-1">
                                    <div class="col-6 mb-3">
                                        <h6>Numero Insegnamenti</h6>
                                        <?php
                                            echo "<p class='text-muted'>".$row["numdocenze"]."</p>";
                                        ?>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <h6>Docenze</h6>
                                        <?php
                                            $doc = $row["docenze"];
                                            if (is_null($row["docenze"])){
                                                $doc = "Nessuna Docenza";
                                            }
                                            echo "<p class='text-muted'>".$doc."</p>";
                                        ?>
                                    </div>
                                </div>
                                <div class="row pt-1">
                                    <div class="col-6 mb-3">
                                        <h6>Indirizzo Ufficio</h6>
                                        <?php
                                            echo "<p class='text-muted'>".$row["indufficio"]."</p>";
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

    <div class="centroNoBordo" >
        <h1>Insegnamenti di cui è repsonsabile</h1>
    </div>
    <div class = "container centroConScritte" id ="elliminaTabella" >
        <?php
            if( $row["docenze"] == 0){
                echo "<div class='alert alert-warning centroS' role='alert'><h4>Il docente non è responsabile di alcun insegnamento <a href='HomeDocenti.php' class='alert-link'>vai alla home</a></h4>
                </div>";
            }else{
        ?>
        <div class= "selezioneCorsoLaurea centroNoBordo">
            <h1>Seleziona Cdl: </h1>
            <select class = "form-select" name="name_selezioneCdl" id="selezioneCdl">
                <?php
                    $dbConnect = openConnection();
                    $query = "select * from ".UniNostra.".cdlDocente($1)";
                    $res = pg_prepare($dbConnect, "", $query);
                    $row = pg_execute($dbConnect, "", array($_SESSION["idUtente"]));
                    endConnection($dbConnect);

                    $f = 1;
                    $first ="";
                    while ($res = pg_fetch_row($row)) {
                        if ($f ==1){
                        $first = $res[0];
                        }
                        echo "<option value=".$res[0].">".$res[0]."</option>";
                        $f++;
                    }
                ?>
            </select>
        </div>
      <table class="table coloreTabella table-striped" >
        <?php
            $titoli = array("#","Codice Insegnamento","Nome Insegnamento","Cfu","Cdl","Anno Erogazione","Descrizione","Docente","Propredeuticità");
            creaIntestazione($titoli);
        ?>
        <tbody>
            <?php
                    
                    $connesione = openConnection();
                    $query = "select * from ".UniNostra.".visualizzaCdlDoc($1,$2)";   
                    $res = pg_prepare($connesione, "", $query);
                    $row = pg_execute($connesione, "", array($first,$_SESSION["idUtente"]));
                    
                    $cont = 1;
                    $query = "select * from ".UniNostra.".visualizzaProp($1,$2)";
                    while ($res = pg_fetch_row($row)) {
                        $res[6] = $res[6]." ".$res[7];
                        unset($res[7]);

                        $res2 = pg_prepare($connesione, "", $query);
                        $resProp = pg_fetch_assoc(pg_execute($connesione, "", array($res[3],$res[0])));
                        $pro = "Nessuna";
                        if ($resProp["visualizzaprop"] !="" ){
                            $pro = $resProp["visualizzaprop"];
                        }
                        array_push($res, $pro);
                        // print_r($res2);
                        creaColonne($res,$cont);
                        $cont++; 
                    }
                    endConnection($connesione);  
                }
            ?> 
        </tbody>
      </table>
    </div>
    <?php 
      //require("../footer.php");
    ?>
    <script>
        $('#selezioneCdl').on('change', function () {
            $('tbody').empty();
            $('#appendiErrore').empty();
            var select = $(this).val();
            var idDoc='<?php echo $_SESSION["idUtente"]; ?>';
            console.log(idDoc);
            $.ajax({
                type: "POST",
                url: "/Progetto/lib/visualizzaInsegnamenti.php",
                data: {'select': select,'idDoc':idDoc},
                success: function (json) {
                    if (json.toLowerCase().indexOf("errore") >= 0){
                        var arr = json.split("ERRORE: ");
                        var errore = "<div class='alert-box centroErr failureBis' id='diverrore'>ERRORE : "+arr[(arr.length)-1]+"</div>";
                        $('tbody').empty();
                        $('#appendiErrore').append(errore);
                    }else{
                        $('tbody').append(json);
                    }
                }
             });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
  </body>
</html>