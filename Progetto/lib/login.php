<?php 
    require('connect.php');
    $dbConnect = openConnection();

    $psw = trim($_POST['psw']);
    $utente = trim($_POST['utente']);
    $tipo = $_POST['tipo'];

    if (isset($_SESSION["error"])){
        $_SESSION["error"] = "";
        unset($_SESSION["error"]);
    }

    if (empty($psw) || empty($utente) || $tipo == "Tipo Utente" ){
        $_SESSION["error"] = "Errore: Compila tutti i campi";
        redirect("../login.php");
    }

    $email = "@";
    switch($tipo){
        case "Studente" :
            $email = $utente.$email."studenti.UniNostra";
            break;
        case "Docente":
            $email = $utente.$email."docenti.UniNostra";
            break;
        case "Segretario":
            $email = $utente.$email."segreteria.UniNostra";
            break;
        default:
            $_SESSION["error"] = "Errore: Selezione tipo non valida";
            redirect("../login.php");
    }    
    $query = "select idU, tipoU from ".UniNostra.".login($1, $2)";
    $res = pg_prepare($dbConnect, "", $query);
    $row = pg_fetch_assoc(pg_execute($dbConnect, "", array($email, $psw)));

    if(!isset($_SESSION["error"])){
        if (is_null($row["idu"])) {
            $_SESSION["error"] = "email o password errati.";
            redirect("../login.php");
        }
    }

    $_SESSION["idUtente"] = $row["idu"];
    $_SESSION["tipoUtente"] = $row["tipou"];
    $_SESSION["email"] = $email;

    switch($row["tipou"]){
        case "Studente":
            redirect("../Studente/homeStudenti.php");
            break;
        case "Docente":
            redirect("../Docenti/HomeDocenti.php");
            break;
        case "Segretario":
            redirect("../Segreteria/homeSegreteria.php");
            break;
    }

    endConnection($dbConnect);
?>