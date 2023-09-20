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
      require("../lib/creaCard.php");
      if (! (isset($_SESSION["idUtente"]))){
        redirect("../login.php");
      }
    ?>

    <?php
      if(isset($_SESSION["arr"])){
        unset($_SESSION["arr"]);
      }
      if(isset($_POST['profilo'])) {
        redirect("profiloDoc.php");
      }
      if (isset($_POST["crea"])){
        redirect("creaAppelli.php");
      }
      if (isset($_POST["visAppelli"])){
        redirect("visualizzaAperti.php");
      }
      if(isset($_POST["valuta"])){
        redirect("valuta.php");
      }
      if(isset($_POST["storico"])){
        redirect("storicoAppelli.php");
      }
      if(isset($_POST["storicoIns"])){
        redirect("storicoInsegnamenti.php");
      }
      if(isset($_POST["psw"])){
        redirect("cambiaPsw.php");
      }

    ?>
    <div class="centroS sfondoSporco">
      <div class="py-5">
        <div class="container">
          <div class="row hidden-md-up">
            <?php
              creaCardColonna("Profilo","Visualizza profilo del docente","profilo","fa-solid fa-user fa-xl");
              creaCardColonna("Crea Appelli","Crea appelli per le docenze","crea","fa-solid fa-plus fa-xl");
              creaCardColonna("Visualizza Appelli","Visualizza appelli aperti e iscrizioni","visAppelli","fa-solid fa-bars fa-xl");
            ?>
          </div><br>
          <div class="row hidden-md-up">
            <?php
              creaCardColonna("Immetti Valutazioni","Valuta gli studenti per gli appelli chiusi","valuta","fa-solid fa-pen-nib fa-xl");
              creaCardColonna("Storico Appelli","Visualizza appelli passati","storico","fa-solid fa-briefcase fa-xl");
              creaCardColonna("Storico Insegnamenti","Insegnamenti tenuti in precedenza","storicoIns","fa-solid fa-clock-rotate-left fa-xl");
            ?>
          </div><br>
          <div class="row hidden-md-up">
            <?php
               echo "<div class='col-md-4'> 
               <div class='card' style='display: none;'>
                   <div class='card-block cardTest cardDim'>
                       <div>
                           <i class=''></i>
                           <h4 class='card-title'></h4>
                       </div>
                       <p class='card-text p-y-1'></p>
                       <form method='post'>
                           <button type='submit' class='btn btn-primary' name='' value=''>Visualizza</button>
                       </form>
                   </div>
               </div>
              </div>";
              creaCardColonna("Cambia Password","Cambia password del tuo account","psw","fa-solid fa-key fa-xl");
            ?>
          </div><br>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
    <?php 
      require("../footer.php");
    ?>
  </body>
</html>