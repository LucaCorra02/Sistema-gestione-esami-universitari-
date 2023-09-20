<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home Studente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../Css/style.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
</head>
  <body>
    
    <?php
      require("../lib/connect.php");
      require("../navbar.php");
      if (! (isset($_SESSION["idUtente"]))){
        redirect("../login.php");
      }
      require("../lib/creaTabella.php");

      if(isset($_SESSION["laurea"])){
        unset($_SESSION["laurea"]);
      }

      if(isset($_POST["laurea"])){
        $_SESSION["laurea"] = $_POST["laurea"];
        @redirect("confermalaurea.php");
      }

      $connesione = openConnection();
      $query = "select * from ".UniNostra.".visualizzaidCdl()"; 
      $row = pg_query($connesione,$query); 
    ?>
  
    <div class = "container centroS" id ="elliminaTabella" >
        <div class= "selezioneCorsoLaurea centroNoBordo">
            <h1>Seleziona Cdl: </h1>
            <select class = "form-select" name="name_selezioneCdl" id="selezioneCdl">
                <?php
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
           $titoli = array("#","Matricola","Nome","Residenza","Data di nascita","Anno iscrizione","Stato","Nome Cdl","Id Cdl","Media","Laurea");
           creaIntestazione($titoli);
        ?>
        <tbody>
            <?php
                $connesione = openConnection();
                $query = "select * from ".UniNostra.".studentiprontilaurea($1)";   
                $res = pg_prepare($connesione, "", $query);
                $row = pg_execute($connesione, "", array($first));
        
                $cont = 1;
                $query = "select * from ".UniNostra.".mediaStudente($1)";   
                while ($res = pg_fetch_row($row)) {
                    
                    $res2 = pg_prepare($connesione, "", $query);
                    $media = pg_fetch_assoc(pg_execute($connesione, "", array($res[0])));
                    
                    $res[1] = $res[1]." ".$res[2];
                    $res[5] = parseData($res[5]);
                    $stato = "InCorso";
                    if($res[6]=="f"){
                        $stato = "Fuori Corso";
                    }
                    $res[6] = $stato;

                    $button = "<form method='post' action='laureastudenti.php'><button type='submit' class='btn btn-warning btn-sm' name='laurea' value='".$res[0]."'>Laurea</button></form>";
                    array_push($res,$media["mediastudente"]);
                    array_push($res,$button);
                    unset($res[2]);
                    creaColonne($res,$cont);
                    $cont++; 
                }
                endConnection($connesione);   
                ?> 
        </tbody>
      </table>
      <div id ="appendiErrore">
        <?php
            if($cont ==1){
                echo "<div class='alert alert-warning centroErr failureBis' role='alert' id='diverrore'>Warning : Nessun studente da laureare</div>";
            }

        ?>
      </div>
    </div>
          
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
    <script>
        $('#selezioneCdl').on('change', function () {
            $('tbody').empty();
            $('#appendiErrore').empty();
            var select = $(this).val();
            $.ajax({
                type: "POST",
                url: "studentipercdl.php",
                data: {'select': select },
                success: function (json) {
                    if(json ==""){
                        var errore = "<div class='alert alert-warning centroErr failureBis' role='alert' id='diverrore'>Warning : Nessun studente da laureare</div>";
                        $('tbody').empty();
                        $('#appendiErrore').append(errore);
                    }else{
                        console.log(json);
                        $('tbody').append(json);
                    }
                }
             });
        });
    </script>
     
  </body>
</html>