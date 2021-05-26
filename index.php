<?php

if (!empty($_POST)) {
    $error = false;
    if ($_POST["libraryType"] === "pdo") {
        require_once __DIR__ . "/pdo/pdo.php";
        $dbConnection = connect();
    } elseif ($_POST["libraryType"] === "mysqli") {
        require_once __DIR__ . "/mysqli/mysqli_version.php";
        $dbConnection = connect();
    }
    if (!$dbConnection) {
        $error = true;
        $result = "Error: Error connecting to database";
    }
    if (isset($_POST["repopulateDb"])) {
        populateTestDB($dbConnection);
    }
    if (!isset($_POST["tableName"])) {
        $error = true;
        $result = "Error: No table name specified"; 
    }
    if ($_POST["select"] == "getById" && !$error) {
        if (!isset($_POST["rowId"]) || empty($_POST["rowId"])) {
            $result = "Error: No row ID specified";
            $error = true;
        }
        if (!$error) {
            if (isset($_POST["idTableName"])) {
                $result = getById($dbConnection, htmlspecialchars($_POST["tableName"]), (int) htmlspecialchars($_POST["rowId"]), htmlspecialchars($_POST["idTableName"]));
            } else {
                $result = getById($dbConnection, htmlspecialchars($_POST["tableName"]), (int) htmlspecialchars($_POST["rowId"]));
            }
        }
    } elseif ($_POST["select"] == "getNElements" && !$error) {
        if (isset($_POST["offset"])) {
            $result = getNElements($dbConnection, htmlspecialchars($_POST["tableName"]), (int) htmlspecialchars($_POST["limit"]), (int) htmlspecialchars($_POST["offset"]));
        } else {
            $result = getNElements($dbConnection, htmlspecialchars($_POST["tableName"]), (int) htmlspecialchars($_POST["limit"]));
        }
    } elseif ($_POST["select"] == "insertValues" && !$error) {
        $counter = 1;
        $insertArray = [];
        while (isset($_POST['columnNameInsert' . $counter]) && !empty($_POST['columnNameInsert' . $counter]) && isset($_POST['columnValueInsert' . $counter]) && !empty($_POST['columnValueInsert' . $counter])) {
            $insertArray[htmlspecialchars($_POST['columnNameInsert' . $counter])] = htmlspecialchars($_POST['columnValueInsert' . $counter]);
            $counter++;
        }
        $result = addRecord($dbConnection, htmlspecialchars($_POST["tableName"]), $insertArray);
    } elseif ($_POST["select"] == "updateValues" && !$error) {
        $counter = 1;
        $insertArray = [];
        while (isset($_POST['columnNameUpdate' . $counter]) && !empty($_POST['columnNameUpdate' . $counter]) && isset($_POST['columnValueUpdate' . $counter]) && !empty($_POST['columnValueUpdate' . $counter])) {
            $insertArray[htmlspecialchars($_POST['columnNameUpdate' . $counter])] = htmlspecialchars($_POST['columnValueUpdate' . $counter]);
            $counter++;
        }
        
        if (!isset($_POST["conditionUpdate1"]) || !isset($_POST["operationUpdate1"]) || !isset($_POST["valueUpdate1"]) || !isset($_POST["logicalOperatorUpdate"])) {
            $result = "Error: No Where condition";
            $error = true;
        }
        $counterSecond = 1;

        $whereArray = [
            "logicalOperator" => $_POST["logicalOperatorUpdate"]
        ];

        while (isset($_POST['conditionUpdate' . $counterSecond]) && !empty($_POST['conditionUpdate' . $counterSecond]) && isset($_POST['operationUpdate' . $counterSecond]) && !empty($_POST['operationUpdate' . $counterSecond]) && isset($_POST['valueUpdate' . $counterSecond]) && !empty($_POST['valueUpdate' . $counterSecond])) {
            $whereArray["expressions"][] = [htmlspecialchars($_POST["conditionUpdate" . $counterSecond]), $_POST["operationUpdate" . $counterSecond], $_POST["valueUpdate" . $counterSecond]];
            $counterSecond++;
        }
        if (!$error) {
            $result = updateRecord($dbConnection, htmlspecialchars($_POST["tableName"]), $insertArray, $whereArray);
        }
    } elseif ($_POST["select"] == "deleteValues" && !$error) {
        if (!isset($_POST["conditionDelete1"]) || !isset($_POST["operationDelete1"]) || !isset($_POST["valueDelete1"]) || !isset($_POST["logicalOperator"])) {
            $result = "Error: No Where condition";
            $error = true;
        }

        $counter = 1;
        $whereArray = [
            "logicalOperator" => $_POST["logicalOperator"]
        ];
        
        while (isset($_POST["conditionDelete" . $counter]) && isset($_POST["operationDelete" . $counter]) && isset($_POST["valueDelete" . $counter])) {
            $whereArray["expressions"][] = [htmlspecialchars($_POST["conditionDelete" . $counter]), $_POST["operationDelete" . $counter], $_POST["valueDelete" . $counter]];
            $counter++;
        }
        if (!$error) {
            $result = deleteRecord($dbConnection, htmlspecialchars($_POST["tableName"]), $whereArray);
        }
    } else {
        $error = true;
        $result = "Error: Method not selected";
    }
    if (is_array($result) && !empty($result)) {
        $resultString = "<table border='0'><tbody><tr>";
        $headers = array_keys($result[0]);
        foreach ($headers as $header) {
            $resultString .= "<th>" . $header . "</th>";
        }
        $resultString .= "</tr>";
        foreach ($result as $row) {
            $resultString .= "<tr>";
            foreach ($row as $value) {
                $resultString .= "<td>" . htmlspecialchars($value) . "</td>";
            }
            $resultString .= "</tr>";
        }
        $resultString .= "</tbody></table>";
    } elseif ($result === true) {
        $resultString = "OK";
    } elseif ($result === false) {
        $resultString = "Not OK";
    } elseif (is_string($result)) {
        $resultString = $result;
    } elseif (empty($result)) {
        $resultString = "No results matched the query";
    }
    $dbConnection = null;
}

?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <title>Frontend</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <main>
        <h1>TEST PAGE!</h1>
        <form name="sqlibraryForm" action="<?=htmlentities($_SERVER['PHP_SELF'])?>" method="POST">
            <div class="dbVariants">
                <label>PDO
                    <input type="radio" name="libraryType" value="pdo" checked="true">
                </label>
                <label>MYSQLI
                    <input type="radio" name="libraryType" value="mysqli">
                </label>
                <label>
                    Recreate Test DB
                    <input type="checkbox" name="repopulateDb" value="1">
                </label>
            </div>
            <label>Choose one method
                <select name="select" onchange="showFields(this.value)">
                    <option value="getById">getById</option>
                    <option value="getNElements">Get N Elements</option>
                    <option value="insertValues">Insert Record</option>
                    <option value="updateValues">Update Record</option>
                    <option value="deleteValues">Delete Record</option>
                </select>
            </label>
            <label>Table Name
                <input type="text" name="tableName" required>
            </label>
            <div class="formInputs getById">
                <label>Element ID
                    <input type="text" name="rowId">
                </label>
                <label>Element id column name, defaults to "id"
                    <input type="text" name="idTableName">
                </label>
            </div>
            <div class="formInputs getNElements">
                <label>Number of returned Rows
                    <input type="text" name="limit" disabled required>
                </label>
                <label>Query Offset, defaults to 0
                    <input type="text" name="offset" disabled>
                </label>
            </div>
            <div class="formInputs insertValues">
                <div class="grid">
                    <label>Name of column to insert value
                        <input type="text" name="columnNameInsert1" disabled>
                    </label>
                    <label>Inserted Value
                        <input type="text" name="columnValueInsert1" disabled>
                    </label>
                    <button id="insertButton" class="insertButton" type="button" onclick="addInputsInsert()">Add Fields</button>
                </div>
            </div>
            <div class="formInputs updateValues">
                <div class="grid">
                    <label>Name of updated Column
                        <input type="text" name="columnNameUpdate1" disabled>
                    </label>
                    <label>New Value for column
                        <input type="text" name="columnValueUpdate1" disabled>
                    </label>
                    <button id="updateButton" type="button" class="updateButton" onclick="addInputsUpdate()">Add Fields</button>
                </div>
                <fieldset class="logicalOperators">
                    <legend> Logical Operator </legend>
                    <label>AND
                        <input type="radio" name="logicalOperatorUpdate" value="and" checked="true">
                    </label>
                    <label>OR
                        <input type="radio" name="logicalOperatorUpdate" value="or">
                    </label>
                </fieldset>
                <div class="grid-3-col">
                    <label>Column Name
                        <input type="text" name="conditionUpdate1" disabled>
                    </label>
                    <label>Operation
                        <input type="text" name="operationUpdate1" disabled>
                    </label>
                    <label>Value
                        <input type="text" name="valueUpdate1" disabled>
                    </label>
                    <button id="updateWhereButton" type="button" class="deleteButton" onclick="addInputsUpdateWhere()">Add Where Fields</button>
                </div>
            </div>
            <div class="formInputs deleteValues">
            <fieldset class="logicalOperators">
                <legend> Logical Operator </legend>
                <label>AND
                    <input type="radio" name="logicalOperator" value="and" checked="true">
                </label>
                <label>OR
                    <input type="radio" name="logicalOperator" value="or">
                </label>
            </fieldset>
                <div class="grid-3-col"> 
                    <label>Column Name
                        <input type="text" name="conditionDelete1" disabled>
                    </label>
                    <label>Operation
                        <input type="text" name="operationDelete1" disabled>
                    </label>
                    <label>Value
                        <input type="text" name="valueDelete1" disabled>
                    </label>
                    <button id="deleteButton" type="button" class="deleteButton" onclick="addInputsDelete()">Add Where Fields</button>
                </div>
            </div>
            <button type="submit">Send</button>
        </form>
        <div>
        <? if (isset($result)) {
            //print_r($result);
            print_r($resultString);
            }?>
        </div>
    </main>
<script src="js/script.js"></script>
</body>
</html>


