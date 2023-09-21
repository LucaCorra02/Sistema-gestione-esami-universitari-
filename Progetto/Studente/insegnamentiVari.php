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

      $connesione = openConnection();
      $query = "select * from ".UniNostra.".visualizzaidCdl()"; 
      $row = pg_query($connesione,$query); 
    ?>
  
    <div class = "container centroS" id ="elliminaTabella" style="margin-bottom:100px;">
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
           $titoli = array("#","Codice Insegnamento","Nome Insegnamento","Cfu","Cdl","Anno Erogazione","Descrizione","Docente","Propredeuticità");
           creaIntestazione($titoli);
        ?>
        <tbody>
            <?php
               
                $connesione = openConnection();
                $query = "select * from ".UniNostra.".visualizzaTuttiCdl($1)";   
                $res = pg_prepare($connesione, "", $query);
                $row = pg_execute($connesione, "", array($first));
        
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
                ?> 
        </tbody>
      </table>
      <div id ="appendiErrore"></div>
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
                url: "/Progetto/lib/visualizzaInsegnamenti.php",
                data: {'select': select },
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
    <?php
      require("../footer.php");
    ?>
     
  </body>
</html>