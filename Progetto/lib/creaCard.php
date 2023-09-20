<?php
    function creaCardColonna($titolo,$paragrafo,$nome,$icon){
        echo "<div class='col-md-4'> 
                <div class='card'>
                    <div class='card-block cardTest cardDim'>
                        <div>
                            <i class='".$icon."'></i>
                            <h4 class='card-title'>".$titolo."</h4>
                        </div>
                        <p class='card-text p-y-1'>".$paragrafo."</p>
                        <form method='post'>
                            <button type='submit' class='btn btn-primary' name='".$nome."' value='".$nome."'>Visualizza</button>
                        </form>
                    </div>
                </div>
        </div>";
    }
?>