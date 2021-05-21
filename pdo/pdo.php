<?php
/**
 * Function connects to database using information from config.ini file near function location
 *
 * @return false|PDO return false if connection was unsuccessfull, or Database Handle otherwise
 */
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
/**
 * Populates created db by test values (creates tables and inputs values in them)
 *
 * @param PDO $dbh Database Handle
 * @return bool true if no errors and false otherwise
 */
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

/**
 * Returns 1 row from database with provided ID
 *
 * @param PDO $dbh Database handle
 * @param string $tableName Name of a target table in db
 * @param int $id ID of row
 * @param string $idColumnName column name with Primary KEY - ID
 * @return array|false Array with row or false in case of error
 */
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


/**
 * Function returns $numberOfElements rows from specified table with optional $offset
 *
 * @param PDO $dbh Database Handler
 * @param string $tableName Name of a target table in db
 * @param int $numberOfElements number of rows 
 * @param integer $offset offset from top results
 * @return array|false returns array of results or false in case of error
 */
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
/**
 * Clears input for TableName and ColumnName params in other functions
 *
 * @param string $value String to clear
 * @return string Cleared input string
 */
function clearInput($value) {
    return preg_replace('/[^0-9a-zA-Z$_]/', '', $value);
}
/**
 * Update record in database
 *
 * @param PDO $dbh Database Handler
 * @param string $tableName Name of a target table in db
 * @param array $newValues Associative Array of new values ["column_name" => "new_column_value"]
 * @param string $condition sql WHERE condition
 * @return bool returns true if query is successfull and false otherwise
 */
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
/**
 * Binds type to PDO prepared query
 *
 * @param PDOStatement $preparedQuery result of PDO::prepare
 * @param mixed $value value to type bind
 * @param int $numberOfParam Number of parameter in query 
 * @return null
 */
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
/**
 * Deletes records from database $tableName 
 *
 * @param PDO $dbh Database Handler
 * @param string $tableName Name of a target table in db
 * @param string $condition SQL WHERE condition
 * @return void
 */
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
/**
 * Add new record to database
 *
 * @param PDO $dbh Database Handler
 * @param string $tableName Name of a target table in db
 * @param array $values Associative array of values: ["column_name" => "new_value"];
 * @return bool return true if insert successful and false otherwise
 */
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