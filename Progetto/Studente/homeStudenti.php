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
      require("../lib/creaCard.php");
      if (! (isset($_SESSION["idUtente"]))){
        redirect("../login.php");
      }

      $connesione = openConnection();
      $query = "select * from ".UniNostra.".isStudente($1)";
      $res = pg_prepare($connesione, "", $query);
      $row = pg_fetch_assoc(pg_execute($connesione, "", array($_SESSION["idUtente"])));

      if ($row["isstudente"] == "f"){
        if (isset($_SESSION["ex"])){
          unset($_SESSION["ex"]);
        }
        redirect("homeExStudente.php");
      }
      
    ?>

    <?php
      if(isset($_POST['profilo'])) {
        redirect("profilo.php");
      }
      if(isset($_POST['appelli'])) {
        redirect("iscrizioneappelli.php");
      }
      if(isset($_POST["iscrizioni"])){
        redirect("visualizzaAppelli.php");
      }
      if (isset($_POST["carriera"])){
        redirect("carriera.php");
      }
      if (isset($_POST["accetta"])){
        redirect("accettaorifiuta.php");
      }
      if (isset($_POST["excarriera"])){
        redirect("storicoCarriera.php");
      }
      if (isset($_POST["cdl"])){
        redirect("insegnamentiCdl.php");
      }
      if (isset($_POST["insegnamentiVari"])){
        redirect("insegnamentiVari.php");
      }
      if (isset($_POST["cambio"])){
        redirect("cambiaPsw.php");
      }

    ?>
    <div class="centroS sfondoSporco">
      <div class="py-5">
        <div class="container">
          <div class="row hidden-md-up">
            <?php
              creaCardColonna("Profilo","Visualizza profilo studente","profilo","fa-solid fa-envelope fa-xl");
              creaCardColonna("Iscriviti agli appelli","Iscrizione agli appelli del proprio cdl","appelli","fa-solid fa-calendar-days fa-xl");
              creaCardColonna("Visualizza iscrizioni","Visualizza iscrizioni confermate agli appelli","iscrizioni","fa-solid fa-bars fa-xl");
            ?>
          </div><br>
          <div class="row">
            <?php
              creaCardColonna("Valutazioni esami","Accetta o rifiuta le valutazioni degli esami","accetta","fa-solid fa-check fa-xl");
              creaCardColonna("Carriera","Visualizza la carriera e la propria media","carriera","fa-solid fa-book fa-xl");
              creaCardColonna("Storico Carriera","Visualizza la carriera passata","excarriera","fa-solid fa-folder-open fa-xl");
            ?>
          </div><br>
          <div class="row">
            <?php
              creaCardColonna("Insegnamenti Cdl","Visualizza gli insegnamenti del tuo cdl","cdl","fa-solid fa-magnifying-glass fa-xl");
              creaCardColonna("Insegnamenti vari","Visualizza gli insegnamenti di altri cdl","insegnamentiVari","fa-solid fa-list fa-xl");
              creaCardColonna("Cambio Password","Cambia la password dell'account","cambio","fa-solid fa-shield-halved fa-xl");
            ?>
          </div><br>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
    <?php 
      //require("../footer.php");
    ?>
  </body>
</html>