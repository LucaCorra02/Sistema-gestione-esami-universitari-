<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home Segreteria</title>
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
      if (isset( $_SESSION["arr"])){
        unset($_SESSION["arr"]);
      }
    ?>

    <?php
      if(isset($_POST["profilo"])){
        redirect("profiloSegreteria.php");
      }
      if(isset($_POST["visCdl"])){
        redirect("visualizzaCdl.php");
      }
      if(isset($_POST["creaCdl"])){
        redirect("creaCdl.php");
      }
      if(isset($_POST["visIns"])){
        redirect("visualizzaInsegnamenti.php");
      }
      if(isset($_POST["visStud"])){
        redirect("visualizzaStudenti.php");
      }
      if(isset($_POST["appelli"])){
        redirect("appelli.php");
      }
      if(isset($_POST["laurea"])){
        redirect("laureastudenti.php");
      }
      if(isset($_POST["visexstud"])){
        redirect("visualizzaexstudenti.php");
      }
      if(isset($_POST["docenti"])){
        redirect("visualizzadocenti.php");
      }
      if(isset($_POST["segretari"])){
        redirect("visualizzasegretari.php");
      }
      if(isset($_POST["recupero"])){
        redirect("recuperopsw.php");
      }

    ?>
    <div class="centroS sfondoSporco" >
      <div class="py-5">
        <div class="container">
          <div class="row hidden-md-up">
            <?php
              creaCardColonna("Profilo","Visualizza profilo del segretario","profilo","fa-solid fa-user fa-xl");
              creaCardColonna("Visualizza Cdl","Visualizza cdl esistenti e piano studi","visCdl","fa-solid fa-bars fa-xl");
              creaCardColonna("Crea Corsi di Laurea","Crea nuovi corsi di laurea","creaCdl","fa-solid fa-plus fa-xl");
              //creaCardColonna("Visualizza Appelli","Visualizza appelli aperti e iscrizioni","visAppelli","fa-solid fa-bars fa-xl");
            ?>
          </div><br>
          <div class="row hidden-md-up">
            <?php
              creaCardColonna("Insegnamenti","Visualizza e aggiungi insegnamenti","visIns","fa-solid fa-pen-nib fa-xl");
              creaCardColonna("Visualizza Studenti","Visualizza gli studenti per cdl","visStud","fa-solid fa-person-circle-check fa-xl");
              creaCardColonna("Visualizza Appelli","Vedi appelli aperti e chiusi","appelli","fa-solid fa-list fa-xl");
            ?>
          </div><br>
          <div class="row hidden-md-up">
            <?php
              creaCardColonna("Inserisci Laurea","Visualizza e laurea gli studenti","laurea","fa-solid fa-graduation-cap fa-xl");
              creaCardColonna("Visualizza Ex-Studenti","Visualizza gli ex-studenti","visexstud","fa-solid fa-graduation-cap fa-xl");
              creaCardColonna("Visualizza Docenti","Vedi e Aggiungi aperti i docenti","docenti","fa-solid fa-person-chalkboard fa-xl");
            ?>
          </div><br>
          <div class="row hidden-md-up">
            <?php
              creaCardColonna("Visualizza Segretari","Visualizza e aggiungi segretari","segretari","fa-regular fa-chess-king fa-xl");
              //creaCardColonna("Visualizza Ex-Studenti","Visualizza gli ex-studenti","visexstud","fa-solid fa-graduation-cap fa-xl");
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
              creaCardColonna("Recupero Password","Vedi e Modifica la password di altri utenti","recupero","fa-solid fa-key fa-xl");
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