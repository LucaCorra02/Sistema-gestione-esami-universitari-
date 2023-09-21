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
      $_SESSION["ex"] = "true";
    ?>

    <?php
      if(isset($_POST['profiloEx'])) {
        redirect("profilo.php");
      }
      if (isset($_POST["excarriera"])){
        redirect("storicoCarriera.php");
      }
      if (isset($_POST["psw"])){
        redirect("cambiaPsw.php");
      }

    ?>
    <div class="centroS sfondoSporco">
      <div class="py-5">
        <div class="container">
          <div class="row hidden-md-up">
            <?php
              creaCardColonna("Profilo","Visualizza profilo studente","profiloEx","fa-solid fa-envelope fa-xl");
              creaCardColonna("Visualizza Carriera","Visualizza carriera passata","excarriera","fa-solid fa-folder-open fa-xl");
              creaCardColonna("Cambia Password","Cambia la password del tuo account","psw","fa-solid fa-bars fa-xl");
            ?>
          </div><br>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
    <?php 
      require("../footer.php");
      spazioFooter();
    ?>
  </body>
</html>