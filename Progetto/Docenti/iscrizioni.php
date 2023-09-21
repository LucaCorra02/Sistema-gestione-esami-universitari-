<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home Docenza</title>
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

        require("../lib/creaEx.php");

        $connesione = openConnection();
        $query = "select * from ".UniNostra.".iscrittiAppello($1)";
        $res = pg_prepare($connesione, "", $query);
        $row = pg_execute($connesione, "", array($_POST["iscrizioni"]));
    ?>
    <div class="centroNoBordo">
        <h1>Studenti Iscritti</h1>
    </div>
    <div class = "centroEx" id = "elliminaStorico">
        <?php
            $cont = 1;
            while ($res = pg_fetch_row($row)) {
                echo "
                    <div class='container py-5 h-100 sopra'>
                        <div class='row d-flex justify-content-center align-items-center h-100'>
                            <div class='col col-md-9 col-lg-7 col-xl-5'>
                                <div class='card' style='border-radius: 15px;'>
                                    <div class='card-body p-4'>
                                        <div class='d-flex text-black'>
                                            <div class='flex-grow-1 ms-3'>
                                                    <h3 class='mb-1'>".$res[1]." ".$res[2]."</h3>
                                                    <div class='d-flex justify-content-start rounded-3 p-2 mb-2' style='background-color: #efefef;padding:10px;'>
                                                        <div style ='padding-left:30px;'>
                                                            <h3 class='small text-muted mb-1'>Matricola</h3>
                                                            <h4 class='mb-0' style = 'padding-right:10px;'>".decbin($res[0])."</h4>
                                                        </div>
                                                        <div style ='padding-left:30px;'>
                                                            <h3 class='small text-muted mb-1'>Stato</h3>
                                                            <h4 class='mb-0'>".$res[3]."</h4>
                                                        </div>  
                                                        <div style ='padding-left:30px;'>
                                                            <h3 class='small text-muted mb-1'>Cdl</h3>
                                                            <h4 class='mb-0'>".$res[4]."</h4>
                                                        </div>                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>";
                $cont++;
            }
        ?>
    </div>

    <?php
        require("../footer.php");
        if($cont<=2){
            spazioFooter();
        }
       
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/3bda55893c.js" crossorigin="anonymous"></script>
  </body>
</html>