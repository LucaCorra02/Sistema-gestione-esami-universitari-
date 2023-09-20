<?php
    function creaExStudenteCard ($matricola,$nome,$stato,$cdl,$DataIscrizione,$DataConclusione,$inCorso){
        echo "
            <div class='container py-5 h-100 sopra'>
                <div class='row d-flex justify-content-center align-items-center h-100'>
                    <div class='col col-md-9 col-lg-7 col-xl-5'>
                        <div class='card' style='border-radius: 15px;'>
                            <div class='card-body p-4'>
                                <div class='d-flex text-black'>
                                    <div class='flex-grow-1 ms-3'>
                                            <h5 class='mb-1'>".$nome."</h5>
                                            <p class='mb-2 pb-1' style='color: #2b2a2a;'>".$stato."</p>
                                            <div class='d-flex justify-content-start rounded-3 p-2 mb-2' style='background-color: #efefef;'>
                                                <div>
                                                    <p class='small text-muted mb-1'>Cdl</p>
                                                    <p class='mb-0' style = 'padding-right:10px;'>".$cdl."</p>
                                                </div>
                                                <div>
                                                    <p class='small text-muted mb-1'>Data Iscrizione</p>
                                                    <p class='mb-0'>".parseData($DataIscrizione)."</p>
                                                </div>
                                                <div class='px-3'>
                                                    <p class='small text-muted mb-1'>Data Conclusione</p>
                                                    <p class='mb-0'>".parseData($DataConclusione)."</p>
                                                </div>
                                                <div>
                                                    <p class='small text-muted mb-1'>Stato</p>
                                                    <p class='mb-0'>".$inCorso."</p>
                                                </div>
                                                
                                            </div>
                                        <div class='d-flex pt-1'>
                                            <form action='exValutazioni.php' method='post'>
                                                <button type='submit' class='btn btn-primary flex-grow-1' name='matricola' value='".$matricola."'>Visualizza Carriera</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>";
    }

?>