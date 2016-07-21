<?php


function createPDO(){
    $dbh = new PDO('mysql:host=;dbname=', "", "");
    return $dbh;
}


?>