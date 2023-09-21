<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home Studente</title>
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
        require("../lib/creaEx.php");
        require("../lib/creaTabella.php");

        $connesione = openConnection();
        $query = "select * from ".UniNostra.".visualizzaExCarriere($1)";
        $res = pg_prepare($connesione, "", $query);
        $row = pg_execute($connesione, "", array($_SESSION["idUtente"]));

    ?>
  
    <div class = "centroEx" id = "elliminaStorico">
        <?php
            $cont = 1;
            while ($res = pg_fetch_row($row)) {
                $stato = $res[3];
                if ($stato=="Laureato"){
                  $stato = $stato." con voto: ".$res[4];
                }
                $inCorso = "in corso";
                if ($res[8] == "f"){
                  $inCorso = "fuori corso";
                }
                creaExStudenteCard($res[0],$res[1]." ".$res[2],$stato,$res[5],$res[6],$res[7],$inCorso);
                $cont++;
            }
        ?>
    </div>
    <?php
      if ($cont==1){
        elliminaTabella("elliminaStorico");
        echo "<div class='alert alert-warning centroConScritte erroreGiallo' role='alert' style ='margin-top:5%;'><h4>Nessuna Carriera Pregessa</h4>
            <a href='homeStudenti.php' class='alert-link'>Vai alla home</a>
        </div>";
      }
      require("../footer.php");
      if($cont <= 2){
        spazioFooter();
      }
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
  </body>
</html>