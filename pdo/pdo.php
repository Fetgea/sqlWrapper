<?php

function connect()
{
    $config = parse_ini_file(__DIR__ . "/config.ini", true);
    if (!isset($config["database"]) || empty($config["database"])) {
        return false;
    }
    $configReq = ["dsn"];
    foreach ($configReq as $configOption) {
        if (!isset($config["database"][$configOption]) || empty($config["database"][$configOption])) {
            return false;
        }
    }
    try {
        $dbh = new PDO($config["database"]["dsn"]);
    } catch (PDOException $e) {
        return false;
    }
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $dbh;
}

function populateTestDB($dbh)
{
    if (!is_a($dbh, "PDO")) {
        return false;
    }
    $sql = file_get_contents(__DIR__ . "/ddl.sql");
    $query = $dbh->exec($sql);
    $sqlValues = file_get_contents(__DIR__ . "/populate_db.sql");
    $queryValues = $dbh->exec($sqlValues);
    if ($queryValues) {
        return true;
    }
    return false;
}

function getById($dbh, $tableName, $id, $idColumnName = "id")
{
    if (!is_a($dbh, "PDO")) {
        return false;
    }
    $tableName = clearInput($tableName);
    $idColumnName = clearInput($idColumnName);
    $query = "SELECT * FROM " . $tableName . " WHERE " . $idColumnName . " =  ?";
    
    $preparedQuery = $dbh->prepare($query);
    var_dump($query);
    $preparedQuery->bindValue(1, $id, PDO::PARAM_INT);
    if ($preparedQuery->execute()) {
        return $preparedQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    return false;
}

function getNElements($dbh, $tableName, $numberOfElements, $offset = 0)
{
    if (!is_a($dbh, "PDO")) {
        return false;
    }
    $tableName = clearInput($tableName);
    $query = "SELECT * FROM " . $tableName . " LIMIT ?, ?";
    $preparedQuery = $dbh->prepare($query);
    
    $preparedQuery->bindValue(1, $offset, PDO::PARAM_INT);
    $preparedQuery->bindValue(2, $numberOfElements, PDO::PARAM_INT);
    if ($preparedQuery->execute()) {
        return $preparedQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    return false;
}

function clearInput($value) {
    return preg_replace('/[^0-9a-zA-Z$_]/', '', $value);
}

function updateRecord($dbh, $tableName, $newValues, $condition)
{
    if (!is_a($dbh, "PDO")) {
        return false;
    }
    $tableName = clearInput($tableName);
    $counter = 0;
    $columnsNumber = count($newValues);
    $columnsString = "";
    foreach ($newValues as $key => $value) {
        $counter++;
        $columnsString .= clearInput($key) . "= ?";
        if ($counter != $columnsNumber) {
            $columnsString .= ", ";
        }
    }
    $query = "UPDATE " . $tableName . " SET " . $columnsString . " WHERE " . $condition;
    var_dump($query);
    $preparedQuery = $dbh->prepare($query);
    $counter = 0;
    foreach ($newValues as $value) {
        $counter++;
        bindType($preparedQuery, $value, $counter);
    }
    if ($preparedQuery->execute()) {
        return true;
    }
    return false;
}

function bindType($preparedQuery, $value, $numberOfParam)
{
    if (is_int($value)) {
        $param = PDO::PARAM_INT;
    } elseif (is_bool($value)) {
        $param = PDO::PARAM_BOOL;
    } elseif (is_null($value)) {
        $param = PDO::PARAM_NULL;
    } elseif (is_string($value)) {
        $param = PDO::PARAM_STR;
    } else {
        $param = FALSE;
    }
    $preparedQuery->bindValue($numberOfParam, $value, $param);
    return null;
}

function deleteRecord($dbh, $tableName, $condition)
{
    if (!is_a($dbh, "PDO")) {
        return false;
    }
    $tableName = clearInput($tableName);
    $query = "DELETE FROM " . $tableName . " WHERE " . $condition;
    $queryValues = $dbh->exec($query);
    if ($queryValues) {
        return true;
    }
    return false;
}

function addRecord($dbh, $tableName, $values)
{
    $tableName = clearInput($tableName);
    
    $columns = "";// preg_replace('/[^0-9a-zA-Z$_,]/', '', implode(",", array_keys($values)));
    
    $paramsString = "";
    $counter = 0;
    $columnsNumber = count($values);
    foreach ($values as $key => $value) {
        $counter++;
        $columns .= clearInput($key);
        $paramsString .= "?";
        if ($counter !== $columnsNumber) {
            $paramsString .= ", ";
            $columns .= ", ";
        }
    }
    $query = "INSERT INTO " . $tableName . " (" . $columns . ")" . " VALUES (" . $paramsString . ")";
    var_dump($query);
    if ($preparedQuery = $dbh->prepare($query)) {
        $counter = 0;
        foreach ($values as $value) {
            $counter++;
            bindType($preparedQuery, $value, $counter);
        }
        if ($preparedQuery->execute()) {
            return true;
        }
    }
    return false;
    
}