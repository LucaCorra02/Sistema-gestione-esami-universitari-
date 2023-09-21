<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home Docente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../Css/style.css" />
</head>
  <body>
    <?php
      require("../lib/connect.php");
      require("../navbar.php");
      require("../lib/creaTabella.php");
      if (! (isset($_SESSION["idUtente"]))){
        redirect("../login.php");
      }
      $connesione = openConnection();
      $query = "select * from ".UniNostra.".storicoInsegnamenti($1)";   
      $res = pg_prepare($connesione, "", $query);
      $row = pg_execute($connesione, "", array($_SESSION["idUtente"]));
    ?>
    <div class="centroNoBordo">
        <h1>Storico Insegnamenti</h1>
    </div>
    <div class = "container centroConScritte" id ="elliminaTabella" >
        <table class="table coloreTabella table-striped" >
            <?php
                $titoli = array("#","Codice Insegnamento","Nome Insegnamento","Cfu","Anno Inizio","Anno Fine","Docente");
                creaIntestazione($titoli);
            ?>
            <tbody>
                <?php   
                    $cont = 1;
                    while ($res = pg_fetch_row($row)) {
                        $res[5] = $res[5]." ".$res[6];
                        unset($res[6]);
                        creaColonne($res,$cont);
                        $cont++;
                    }
                    endConnection($connesione);  
                ?> 
            </tbody>
        </table>
    </div>
    <?php
        if ($cont==1){
            elliminaTabella("elliminaTabella");
            echo "<div class='alert alert-warning centroS' role='alert'><h4>Nessun Insegnamento pregresso. <a href='HomeDocenti.php' class='alert-link'>Home</a></h4>
            </div>";
          }
      
        require("../footer.php");
        if($cont < 4){
          spazioFooter();
        }
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
  </body>
</html>