<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UniNostra Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="Css/style.css" />
</head>
  <body>
    <div class="container-fluid ps-md-0">
      <div class="row g-0">
        <div class="d-none d-md-flex col-md-4 col-lg-6 bg-image esterno" >
          <div class= "scritte">
            <img src="img/logo.png" id = 'logo' class="rounded mx-auto d-block" alt="logo">
              <h1 id='title'>UniNostra</h1>
          </div>
        </div>
        <div class="col-md-8 col-lg-6 destra">
          <div class="login d-flex align-items-center py-5">
            <div class="container">
              <div class="row">
                <div class="col-md-9 col-lg-8 mx-auto">
                  <h3 class="login-heading mb-4">Login Utente</h3>

                  <form action="lib/login.php" method="post">
                    <div class="form-floating mb-3">
                      <i class="fa-solid fa-user fa-2xl"></i>
                      <input type="text" class="form-control input" id="floatingInput" placeholder="user" name="utente">
                      <label for="floatingInput" class="sposta">Utente</label>
                    </div>
                    <div class="form-floating mb-3">
                      <i class="fa-solid fa-lock fa-2xl"></i>
                      <input type="password" class="form-control input" id="floatingPassword" placeholder="Password" name="psw">
                      <label for="floatingPassword" class="sposta">Password</label>
                    </div>
    
                    <div class="form-floating mb-3">
                        <i class="fa-solid fa-envelope fa-2xl icon"></i>
                      <select class="form-select form-select sm input centro" aria-label="Default select example" name="tipo">
                        <option selected>Tipo Utente</option>
                          <option value="Studente">Studente</option>
                          <option value="Docente">Docente</option>
                          <option value="Segretario">Segretario</option>
                      </select>
                    </div>
    
                    <div class="d-grid">
                      <button class="btn btn-lg btn-primary btn-login text-uppercase fw-bold mb-2" onclick="ellimina()" type="submit">Accedi</button>
                    </div>
                   
                  </form>
                </div>
              </div>
              <div class="error" id= "err">
                <?php
                  session_start();
                  if (isset($_SESSION["idUtente"])){
                    session_destroy();
                  }
                  if (isset($_SESSION["error"])) {
                    echo "<div class='alert alert-danger error' role='alert'>".$_SESSION["error"]."</div>";
                  }
                ?>            
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>

    <script>
      function ellimina(){
          var div = document.getElementById("err");
          while(div.firstChild) {
              div.removeChild(div.firstChild);
          } 
      }
    </script>
  </body>
</html>