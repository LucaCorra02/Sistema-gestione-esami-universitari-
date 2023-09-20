<?php
    function creaIntestazione($valori) {
        echo "<thead class='thead-dark' id = 'testaTabella'><tr>";
        foreach ($valori as $titolo) {
            echo "<th scope='col'>".$titolo."</th>";
        }
        echo "</tr></thead>";
    }

    function creaColonne($valori,$cont){
        echo "<tr>";
        echo "<th scope='row'>".$cont."</th>";
        foreach ($valori as $titolo) {
            echo "<td>".$titolo."</td>";
        }
        echo "</tr>";
    }

    //passare il id div esterno alla tabella
    function elliminaTabella($id){
        echo "<script  language='JavaScript' type='text/javascript'>
            var element = document.getElementById('".$id."');
            element.remove();
        </script>";
    }

    function barraRicerca(){
        echo "<div class='input-group centroNoBordoSotto'> 
            <label class='form-label' for='filtra' style='margin-right:10px;font-size:20px;'>Filtra</label>
            <input id='filtra' type='text' class='form-control' placeholder='Ricerca..'>
        </div>";
    }

?>