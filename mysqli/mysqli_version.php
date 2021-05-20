<?php

function connect()
{
    $config = parse_ini_file(__DIR__ . "/config.ini", true);
    if (!isset($config["database"]) || empty($config["database"])) {
        return false;
    }
    $configReq = ["hostname", "username", "password", "database"];
    foreach ($configReq as $configOption) {
        if (!isset($config["database"][$configOption]) || empty($config["database"][$configOption])) {
            return false;
        }
    }
    $mysqli = mysqli_connect($config["database"]["hostname"], $config["database"]["username"], $config["database"]["password"], $config["database"]["database"]);
    if (mysqli_connect_errno()) {
        return false;
    }
    return $mysqli;
}


function addRecord($dbConnection, $tableName, $values) {
    if (!is_a($dbConnection, "mysqli")) {
        return false;
    }
    $tableName = preg_replace('/[^0-9a-zA-Z$_]/', '', $tableName);

    mysqli_report(MYSQLI_REPORT_ALL);

    $columns = preg_replace('/[^0-9a-zA-Z$_,]/', '', implode(",", array_keys($values)));
    
    $paramsString = "";
    $paramsTypeString = "";
    foreach ($values as $key => $value) {
        
        if ($key === array_key_last($values)) {
            $paramsString .= "?";
        } else {
            $paramsString .= "?,";
        }

        switch(gettype($value)) {
            case "int":
                $paramsTypeString .= "i";
                break;
            case "float":
                $paramsTypeString .= "d";
                break;
            case "string":
                $paramsTypeString .= "s";
                break;
            default:
                $paramsTypeString .= "d";
                break;
        }
    } 
    $query = "INSERT INTO " . $tableName . "(" . $columns . ")" . " VALUES (" . $paramsString . ")";
    $preparedQuery = mysqli_prepare($dbConnection, $query);
    mysqli_stmt_bind_param($preparedQuery, $paramsTypeString, ...array_values($values));
    return mysqli_stmt_execute($preparedQuery);
}

function getById($dbConnection, $tableName, $id, $idColumnName = "id") {
    if (!is_a($dbConnection, "mysqli")) {
        return false;
    }
    //mysqli_report(MYSQLI_REPORT_ALL);
    $tableName = preg_replace('/[^0-9a-zA-Z$_]/', '', $tableName);
    $idColumnName = preg_replace('/[^0-9a-zA-Z$_]/', '', $idColumnName);
    if (empty($tableName) || empty($idColumnName) || empty($id)) {
        return false;
    }
    $query = "SELECT * FROM " . $tableName . " WHERE " . $idColumnName ." = ?";
    if ($preparedQuery = mysqli_prepare($dbConnection, $query)) {
        if (mysqli_stmt_bind_param($preparedQuery, "i", $id)) {
            if (mysqli_stmt_execute($preparedQuery)){
                $result = mysqli_stmt_get_result($preparedQuery);
                return mysqli_fetch_all($result);
            }
        }
    }
    return false;
}

function getNElements($dbConnection, $tableName, $numberOfElements, $offset = 0) {
    if (!is_a($dbConnection, "mysqli")) {
        return false;
    }
    //mysqli_report(MYSQLI_REPORT_ALL);
    $tableName = preg_replace('/[^0-9a-zA-Z$_]/', '', $tableName);
    $query = "SELECT * FROM " . $tableName . " LIMIT ?, ?";
    if ($preparedQuery = mysqli_prepare($dbConnection, $query)) {
        if (mysqli_stmt_bind_param($preparedQuery, "ii", $offset, $numberOfElements)) {
            if (mysqli_stmt_execute($preparedQuery)){
                $result = mysqli_stmt_get_result($preparedQuery);
                return mysqli_fetch_all($result);
            }
        }
    }
    return false;

}