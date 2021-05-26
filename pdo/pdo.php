<?php
/**
 * Function connects to database using information from config.ini file near function location
 *
 * @return false|PDO return false if connection was unsuccessfull, or Database Handle otherwise
 */
function connect()
{
    $config = parse_ini_file(__DIR__ . "/config.ini", true);
    if ((!isset($config["database"]) || empty($config["database"])) && (!isset($config["database"]["type"]) || empty($config["database"]["type"]))) {
        return false;
    }
    switch (strtolower($config["database"]["type"])) {
        case "mysql":
            $configReq = ["hostname", "username", "password", "database"];
            break;
        case "sqlite":
            $configReq = ["database"];
            break;
        default: 
            return false;
    }
    foreach ($configReq as $configOption) {
        if (!isset($config["database"][$configOption]) || empty($config["database"][$configOption])) {
            return false;
        }
    }
    switch (strtolower($config["database"]["type"])) {
        case "mysql":
            $connectionString = "mysql:host=" . $config["database"]["hostname"] . ";dbname=" . $config["database"]["database"];
            try {
                $dbh = new PDO($connectionString, $config["database"]["username"], $config["database"]["password"]);
            } catch (PDOException $e) {
                return false;
            }
            break;
        case "sqlite":
            $connectionString = "sqlite:" . $config["database"]["database"];
            try {
                $dbh = new PDO($connectionString, $config["database"]["username"]["password"]);
            } catch (PDOException $e) {
                return false;
            }
            break;
        default: 
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
    $config = parse_ini_file(__DIR__ . "/config.ini", true);
    if ((!isset($config["database"]) || empty($config["database"])) && (!isset($config["database"]["type"]) || empty($config["database"]["type"]))) {
        return false;
    }
    if (strtolower($config["database"]["type"]) === "mysql") {
        $sql = file_get_contents(__DIR__ . "/ddl_mysql.sql");
        $sqlValues = file_get_contents(__DIR__ . "/populate_db_mysql.sql");
    } elseif (strtolower($config["database"]["type"] === "sqlite")) {
        $sql = file_get_contents(__DIR__ . "/ddl.sql");
        $sqlValues = file_get_contents(__DIR__ . "/populate_db.sql");
    } else {
        return false;
    }
    $query = $dbh->exec($sql);
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
    $preparedQuery->bindValue(1, (int) $id, PDO::PARAM_INT);
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

    $preparedQuery->bindValue(1, (int) $offset, PDO::PARAM_INT);
    $preparedQuery->bindValue(2, (int) $numberOfElements, PDO::PARAM_INT);
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
function clearInput($value)
{
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
    $query = "UPDATE " . $tableName . " SET " . $columnsString;

    $query = addWhereQuery($query, $condition);
    $preparedQuery = $dbh->prepare($query["query"]);
    $counter = 0;
    foreach ($newValues as $value) {
        $counter++;
        bindType($preparedQuery, $value, $counter);
    }
    foreach ($query["whereValues"] as $conditionValue) {
        $counter++;
        bindType($preparedQuery, $conditionValue, $counter);
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
 * @return bool return true in case successfull delete and false otherwise
 */
function deleteRecord($dbh, $tableName, $condition)
{
    if (!is_a($dbh, "PDO")) {
        return false;
    }
    $tableName = clearInput($tableName);
    $query = "DELETE FROM " . $tableName;
    $queryWithWhere = addWhereQuery($query, $condition);
    if ($query === false) {
        return false;
    }

    $preparedQuery = $dbh->prepare($queryWithWhere["query"]);
    $counter = 0;
    foreach ($queryWithWhere["whereValues"] as $parameter) {
        $counter++;
        bindType($preparedQuery, $parameter, $counter);
    }
    $queryValues = $preparedQuery->execute();
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
    $columns = "";
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

function parseWhere($condition) {

    if (is_array($condition)) {
        $returnArray = [];
        $whereString = "";
        foreach ($condition as $where) {
            if (!isset($where[0]) || !isset($where[1]) || !isset($where[2])) {
                return false;
            }
            if ($operation = checkOperation($where[1])) {
                $whereString = clearInput(trim($where[0])) . $operation . "?";
                $returnArray[] = [$whereString, $where[2]];
            } else {
                return false;
            }
        }
        return $returnArray;
    }
    return false;   
}

function checkOperation($operation)
{
    $operation = trim($operation);
    $regex = "/^(=|<>|!=|<|<=|>|>=)$/";
    if (preg_match($regex, $operation) === false) {
        return false;
    }
    return $operation;
}

function addWhereQuery($query, $condition) {
    $query .= " WHERE ";
    if (isset($condition["logicalOperator"]) && strtolower($condition["logicalOperator"]) === "and") {
        $glue = "and";
    } elseif (isset($condition["logicalOperator"]) && strtolower($condition["logicalOperator"]) === "or") {
        $glue = "or";
    } else {
        return false;
    }
    $conditionParsed = parseWhere($condition["expressions"]);
    if ($conditionParsed === false) {
        return false;
    }
    $counter = 0;
    $arrayCount = count($conditionParsed);
    $whereValues = [];
    foreach ($conditionParsed as $whereCondition) {
        $query .= $whereCondition[0];
        $counter++;
        if ($counter !== $arrayCount) {
            $query .= " {$glue} ";
        }
        $whereValues[] = $whereCondition[1];
    }
    return ["query" => $query, "whereValues" => $whereValues];
}