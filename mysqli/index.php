<?php

require_once __DIR__ . "/mysqli_version.php";

if ($connection = connect()) {
    $insertArray = [
        "vend_id" => "DLL01",
        "prod_name" => "Fish bean bag toy",
        "prod_price" => "3.49",
        "prod_desc" => "complete with bean bag worms with which to feed it"
    ];
    //addRecord($connection, "products", $insertArray);
    $getById = getById($connection, "products", 4, "prod_id");
    print_r($getById);
    $getNElements = getNElements($connection, "products", 3, 2);
}