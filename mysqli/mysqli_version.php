<?php
/**
 * Function connects to database using information from config.ini file near function location
 *
 * @return false|mysqli return false if connection was unsuccessfull, or Database Handle otherwise
 */
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
/**
 * Creates tables in opened database and populates them with data
 *
 * @param mysqli $dbConnection Database Handler
 * @return bool True if no errors returned by MySql server in queries execution and false otherwise 
 */
function populateTestDB($dbConnection)
{
    $queriesCreateDB = fopen(__DIR__ . "/ddl.sql", "rb");
    if ($queriesCreateDB) {
        while (!feof($queriesCreateDB)) {
            $query = stream_get_line($queriesCreateDB, 4096, ";");
            if (empty($query)) {
                continue;
            }
            if (!mysqli_query($dbConnection, $query)) {
                return false;
            }
        }
    }

    $queriesPopDB = fopen(__DIR__ . "/populate_db.sql", "rb");
    if ($queriesPopDB) {
        while (!feof($queriesPopDB)) {
            $query = stream_get_line($queriesPopDB, 4096, ";");
            if (empty($query)) {
                continue;
            }
            if (!mysqli_query($dbConnection, $query)) {
                return false;
            }
        }
    }
    return true;
}

/**
 * Add new record to database
 *
 * @param mysqli $dbConnection Database Handler
 * @param string $tableName Name of a target table in db
 * @param array $values Associative array of values: ["column_name" => "new_value"];
 * @return bool return true if insert successful and false otherwise
 */
function addRecord($dbConnection, $tableName, $values)
{
    if (!is_a($dbConnection, "mysqli")) {
        return false;
    }
    $tableName = preg_replace('/[^0-9a-zA-Z$_]/', '', $tableName);
    $columns = preg_replace('/[^0-9a-zA-Z$_,]/', '', implode(",", array_keys($values)));
    $paramsString = "";
    $paramsTypeString = "";
    $numberOfElements = count($values);
    $counter = 0;
    foreach ($values as $key => $value) {
        $counter++;
        if ($counter === $numberOfElements) {
            $paramsString .= "?";
        } else {
            $paramsString .= "?,";
        }
        $paramsTypeString .= getTypeBind($value);
    }
    $query = "INSERT INTO " . $tableName . "(" . $columns . ")" . " VALUES (" . $paramsString . ")";
    if ($preparedQuery = mysqli_prepare($dbConnection, $query)) {
        if (mysqli_stmt_bind_param($preparedQuery, $paramsTypeString, ...array_values($values))) {
            return mysqli_stmt_execute($preparedQuery);
        }
    }
    return false;
}
/**
 * Returns 1 row from database with provided ID
 *
 * @param mysqli $dbConnection Database handle
 * @param string $tableName Name of a target table in db
 * @param int $id ID of row
 * @param string $idColumnName column name with Primary KEY - ID
 * @return array|false Array with row or false in case of error
 */
function getById($dbConnection, $tableName, $id, $idColumnName = "id")
{
    if (!is_a($dbConnection, "mysqli")) {
        return false;
    }
    $tableName = preg_replace('/[^0-9a-zA-Z$_]/', '', $tableName);
    $idColumnName = preg_replace('/[^0-9a-zA-Z$_]/', '', $idColumnName);
    if (empty($tableName) || empty($idColumnName) || empty($id)) {
        return false;
    }
    $query = "SELECT * FROM " . $tableName . " WHERE " . $idColumnName . " = ?";
    if ($preparedQuery = mysqli_prepare($dbConnection, $query)) {
        if (mysqli_stmt_bind_param($preparedQuery, "i", $id)) {
            if (mysqli_stmt_execute($preparedQuery)) {
                $result = mysqli_stmt_get_result($preparedQuery);
                return mysqli_fetch_all($result);
            }
        }
    }
    return false;
}
/**
 * Function returns $numberOfElements rows from specified table with optional $offset
 *
 * @param mysqli $dbConnection Database Handler
 * @param string $tableName Name of a target table in db
 * @param int $numberOfElements number of rows 
 * @param integer $offset offset from top results
 * @return array|false returns array of results or false in case of error
 */
function getNElements($dbConnection, $tableName, $numberOfElements, $offset = 0)
{
    if (!is_a($dbConnection, "mysqli")) {
        return false;
    }
    $tableName = preg_replace('/[^0-9a-zA-Z$_]/', '', $tableName);
    $query = "SELECT * FROM " . $tableName . " LIMIT ?, ?";
    if ($preparedQuery = mysqli_prepare($dbConnection, $query)) {
        if (mysqli_stmt_bind_param($preparedQuery, "ii", $offset, $numberOfElements)) {
            if (mysqli_stmt_execute($preparedQuery)) {
                $result = mysqli_stmt_get_result($preparedQuery);
                return mysqli_fetch_all($result);
            }
        }
    }
    return false;
}
/**
 * Update record in database
 *
 * @param mysqli $dbConnection Database Handler
 * @param string $tableName Name of a target table in db
 * @param array $newValues Associative Array of new values ["column_name" => "new_column_value"]
 * @param string $condition sql WHERE condition
 * @return bool returns true if query is successfull and false otherwise
 */
function updateRecord($dbConnection, $tableName, $newValues, $condition)
{
    if (!is_a($dbConnection, "mysqli")) {
        return false;
    }
    $columnsString = "";
    $valuesString = "";
    $counter = 0;
    $elemQuantity = count($newValues);
    foreach ($newValues as $key => $value) {
        $counter++;
        $columnsString .= preg_replace('/[^0-9a-zA-Z$_]/', '', $key) . "= ?";
        $valuesString .= getTypeBind($value);
        if ($elemQuantity !== $counter) {
            $columnsString .= ", ";
        }
    }
    //mysqli_report(MYSQLI_REPORT_ALL);
    $tableName = preg_replace('/[^0-9a-zA-Z$_]/', '', $tableName);
    $query = "UPDATE " . $tableName . " SET " . $columnsString;
    $query = addWhereQuery($query, $condition);
    if ($query === false) {
        return false;
    }
    foreach ($query["whereValues"] as $whereValue) {
        $valuesString .= getTypeBind($whereValue);
    }
    if ($preparedQuery = mysqli_prepare($dbConnection, $query["query"])) {
        if (mysqli_stmt_bind_param($preparedQuery, $valuesString, ...array_values($newValues), ...$query["whereValues"])) {
            return mysqli_stmt_execute($preparedQuery);
        }
    }
    return false;
}
/**
 * returns bind type for provided Variable
 *
 * @param mixed $variable Variable needded to type bind
 * @return string type binding string for provided variable
 */
function getTypeBind($variable)
{
    switch (gettype($variable)) {
        case "int":
            return "i";
        case "float":
            return "d";
        case "string":
            return "s";
        default:
            return "b";
    }
}
/**
 * Deletes records from database $tableName 
 *
 * @param mysqli $dbConnection Database Handler
 * @param string $tableName Name of a target table in db
 * @param string $condition SQL WHERE condition
 * @return void
 */
function deleteRecord($dbConnection, $tableName, $condition)
{
    if (!is_a($dbConnection, "mysqli")) {
        return false;
    }
    $tableName = preg_replace('/[^0-9a-zA-Z$_]/', '', $tableName);
    $query = "DELETE FROM " . $tableName;
    $query = addWhereQuery($query, $condition);
    $valuesString = "";
    foreach ($query["whereValues"] as $whereValue) {
        $valuesString .= getTypeBind($whereValue);
    }
    if ($preparedQuery = mysqli_prepare($dbConnection, $query["query"])) {
        if (mysqli_stmt_bind_param($preparedQuery, $valuesString, ...$query["whereValues"])) {
            return mysqli_stmt_execute($preparedQuery);
        }
    }
    return mysqli_query($dbConnection, $query["query"]);
    
}

/**
 * Parses condition expressions array, checks for allowed operators and clears column names
 *
 * @param array $condition array of conditions formatted like: [
 *                                                                  ["column_name","operator", "column_value"],
 *                                                                  ["column_name","operator", "column_value"],
 *                                                              ]
 * @return array Array with values and string ready to input into stmt query string
 */
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
/**
 * Checks if supplied operator is in whitelist of operators
 *
 * @param string $operation string with operation type
 * @return false|string returns false if operator is not in whitelist and supplied string oyherwise
 */
function checkOperation($operation)
{
    $operation = trim($operation);
    $regex = "/^(=|<>|!=|<|<=|>|>=)$/";
    if (preg_match($regex, $operation) === false) {
        return false;
    }
    return $operation;
}
/**
 * Function adds WHERE condition to supplied query, using $condition array 
 *
 * @param string $query Query to which WHERE is added
 * @param array $condition Array of conditions 
 * 
 * [ "logicalOperator" => "logicalOperator" //"and" or "or"
 *   "expressions" => [
*                       ["column_name","operator", "column_value"],
*                       ["column_name","operator", "column_value"],
*                      ]
 * ]
 * @return array returns array [
 * "query" => query with added WHERE clause ready to STMT,
 * "whereValues" => values for WHERE conditions
 * ]
 */
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