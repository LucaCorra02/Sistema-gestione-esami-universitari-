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

    <nav class="navbar navbar-dark bg-primary logoNav">
        <a class="navbar-brand" href="#">
          <img src="../img/logo.png" width="60" height="60" class="d-inline-block align-top" alt="logo" >
          <p id="testoLogo" onclick="Redirect();">UniNostra</p>
          <div>
              <h6 class="btnNav" id = "testoNav">
                  <?php
                    if (isset($_SESSION["idUtente"])){
                      echo $_SESSION["email"];
                    }
                  ?>
              </h6>
            <button class="btn btn-outline-light my-2 my-sm-0 btnNav" onclick= "window.location.href = '../lib/logout.php' ">Logout</button>
          </div>
        </a>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
    <script>
        function Redirect(){
          console.log("ciao");
          var session='<?php echo $_SESSION["tipoUtente"];?>';
          switch (session) {
            case 'Studente':
              location.href = "homeStudenti.php";
            break;
            case 'Docente':
              location.href = "HomeDocenti.php";
              break;
            case 'Segretario':
              location.href = "homeSegreteria.php";
            break;
          }
        }

    </script>
  </body>
</html>

