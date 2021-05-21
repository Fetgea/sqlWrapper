<?php

if (!empty($_POST)) {
    $error = false;
    if($_POST["libraryType"] === "pdo") {
        require_once __DIR__ . "/pdo/pdo.php";
        $dbConnection = connect();
        if (isset($_POST["repopulateDb"])) {
            populateTestDB($dbConnection);
        }
    } elseif ($_POST["libraryType"] === "mysqli") {
        require_once __DIR__ . "/mysqli/mysqli_version.php";
        $dbConnection = connect();
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
            $result = getNElements($dbConnection, htmlspecialchars($_POST["tableName"]), (int) htmlspecialchars($_POST["limit"]), (int) htmlspecialchars($_POST["limit"]));
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
        if (!isset($_POST["conditionUpdate"])) {
            $result = "Error: No Where condition";
            $error = true;
        }
        if (!$error) {
            $result = updateRecord($dbConnection, htmlspecialchars($_POST["tableName"]), $insertArray, htmlspecialchars($_POST["conditionUpdate"]));
        }
    } elseif ($_POST["select"] == "deleteValues" && !$error) {
        if (!isset($_POST["conditionInsert"])) {
            $result = "Error: No Where condition";
            $error = true;
        } 
        if (!$error) {
            $result = deleteRecord($dbConnection, htmlspecialchars($_POST["tableName"]), htmlspecialchars($_POST["conditionDelete"]));
        }
    } else {
        die();
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

        <?=print_r($_POST);?>
        <form name="sqlibraryForm" action="/index.php" method="POST">
            <div class="dbVariants">
                <label>PDO
                    <input type="radio" name="libraryType" value="pdo" checked="true">
                </label>
                
                <label >MYSQLI
                    <input type="radio" name="libraryType" value="mysqli">
                </label>
                <label>
                    Repopulate SQLlite DB
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
                    <label id="inputHere">Condition to WHERE clause
                        <input type="text" name="conditionUpdate" disabled> 
                    </label>
                    <button type="button" class="updateButton" onclick="addInputsDelete()">Add Fields</button>
                </div>
            </div>

            <div class="formInputs deleteValues">
                <label>Condition to Where clause
                    <input type="text" name="conditionDelete" disabled>
                </label>
            </div>
            <button type="submit">Send</button>
        </form>
        <pre>
        <? if(isset($result)) {
            print_r($result);
            }?>
        </pre>
    </main>
<script src="js/script.js"></script>

</body>

</html>


