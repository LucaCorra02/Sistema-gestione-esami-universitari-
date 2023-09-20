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
      require("../lib/creaTabella.php");

      $connesione = openConnection();
      $query = "select * from ".UniNostra.".visualizzaInfoCdl($1)";
      $res = pg_prepare($connesione, "", $query);
      $row = pg_fetch_assoc(pg_execute($connesione, "", array($_SESSION["idUtente"])));
      
      $connesione = openConnection();
      $query = "select * from ".UniNostra.".visualizzaCdl($1)";
      $res2 = pg_prepare($connesione, "", $query);
      $row2 = pg_execute($connesione, "", array($_SESSION["idUtente"]));

      endConnection($connesione);
    ?>
    <div class="container py-5 h-100" style = "width:100%;">
      <div class="row d-flex justify-content-center align-items-center h-100" style = "width:100%;">
        <div class="col col-lg-6 mb-4 mb-lg-0">
          <div class="card mb-3" style="border-radius: .5rem;">
            <div class="row g-0">
              <div class="col-md-4 gradient-custom text-center text-white" style="border-top-left-radius: .5rem; border-bottom-left-radius: .5rem; background-color: #0D6EFD;">
                <div style="margin-top:30px; padding:30px;">
                  <h5>Corso di Laurea </h5>
                  <h5 style ="margin-top:10px;">
                    <?php 
                      echo $row["codicecdl"];
                    ?>
                  </h5>  
                </div>
              </div>
              <div class="col-md-8 sfondoSporco">
                <div class="card-body p-4">
                  <h6>Informazioni</h6>
                  <hr class="mt-0 mb-4">
                  <div class="row pt-1">
                    <div class="col-6 mb-3">
                      <h6>Nome Cdl</h6>
                        <?php
                          echo "<p class='text-muted'>".$row["nomecdl"]."</p>";
                        ?>
                    </div>
                    <div class="col-6 mb-3">
                      <h6>Attivo</h6>
                      <?php
                        $stato = "True";
                        if ($row["isattivo"]=="f"){
                          $stato = "False";
                        }
                        echo "<p class='text-muted'>".$stato."</p>";
                      ?>
                    </div>
                  </div>
                  <div class="row pt-1">
                    <div class="col-6 mb-3">
                      <h6>Descrizione</h6>  
                      <?php
                        echo "<p class='text-muted'>".$row["descrizione"]."</p>";
                      ?>
                    </div>
                    <div class="col-6 mb-3">
                      <h6>Durata</h6>
                      <?php
                        $durata = "Triennale";
                        if ($row["durata"] == "5"){
                          $durata = "Magistrale";
                        }
                        echo "<p class='text-muted'>".$durata."</p>";
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
    <div class="centroNoBordo" style="margin-top: -3%;">
        <h1>Piano Studi</h1>
    </div>
    <div class = "container centroConScritte" id ="elliminaTabella" >
      <table class="table coloreTabella table-striped" >
        <?php
          $titoli = array("#","Codice Insegnamento","Nome Insegnamento","Cfu","Cdl","Anno Erogazione","Descrizione","Docente","Propredeuticità");
          creaIntestazione($titoli);
        ?>
        <tbody>
          <?php
            $cont2 = 1;
            $connesione = openConnection();
            $query = "select * from ".UniNostra.".visualizzaProp($1,$2)";
            
            while ($res2 = pg_fetch_row($row2)) {   
                $res2[6] = $res2[6]." ".$res2[7];
                unset($res2[7]);

                $res = pg_prepare($connesione, "", $query);
                $resProp = pg_fetch_assoc(pg_execute($connesione, "", array($res2[3],$res2[0])));
                $pro = "Nessuna";
                if ($resProp["visualizzaprop"] !="" ){
                  $pro = $resProp["visualizzaprop"];
                }
                array_push($res2, $pro);
               // print_r($res2);
                creaColonne($res2,$cont2);
                $cont2++;
            }
            endConnection($connesione);
          ?>
        </tbody>
      </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
    <?php
      //require("../footer.php");
    ?>
  </body>
</html>