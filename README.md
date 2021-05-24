# Библиотека Wrapper для подключения к БД
Библиотека реализует возможность подключения к БД MySql или SQLite и позволяет выполнять запросы к БД используя функции wrapperы. Есть возможность работы через графический пользовательский интерфейс - index.php в корне репозитория.

## Установка
Проверка работы проводилась на PHP v7.4, MySQL v5.7 и SQLite v3.35.5. 

## Запуск
Для тестовой работы внести данные для подключения в config.ini файл в папках pdo или mysqli (примеры заполнения в описании функции connect).
Запустить index.php в корне репозитория.
## Доступные функции
- connect
- populateTestDB
- addRecord
- getById
- getNElements
- updateRecord
- deleteRecord

### connect
Функция для подключения к базе данных. Данные для подключения должны находится в файле config.ini в папке используемого варианта библиотеки. Возвращает объект подключения при удачном подключении.

Пример config.ini для MySQLi версии библиотеки(лежит в папке mysqli)
`
[database]
hostname = 127.0.0.1
username = mysql
password = mysql
database = db_test
`

Пример config.ini для PDO+Sqlite
`
[database]
dsn = sqlite:test1.sqlite
`
### populateTestDB
Создает таблицы в подключенной базе данных и заполняет их тестовыми данными, если таблицы уже существуют предварительно удаляет их.
На вход принимает объект подключения к базе данных (PDO, MySQLi)

Пример вызова:
`
populateTestDB($dbConnection);
`

### addRecord
Добавляет новую запись в определенную таблицу базы.
$dbConnection - объект подключения к базе данных (PDO, MySQLi)
$tableName - строка с названием таблицы в которую производится запись.
$insertArray - массив значений вида:

`
[
    "column_name" => "value",
    "column_name" => "value"
]
`

Пример вызова 
`
$result = addRecord($dbConnection, $tableName, $insertArray);

`

### getById
Возвращает 1 запись из базы данных, id которой совпадает с указанным в параметрах вызова.
- $dbConnection - объект подключения к базе данных (PDO, MySQLi)
- $tableName - строка с названием таблицы из которой будет получена строка
- $id - id строки, которую нужно получить из таблицы
- $idTableName - Имя столбца в котором хранится id (не обязательный параметр, если не указан будет искать в столбце "id")
Пример вызова:
`
$result = getById($dbConnection, $tableName, $id, $idTableName);

`

### getNElements
Возвращает $limit элементов из таблицы базы данных, со сдвигом $offset (если не указан - равен 0)
- $dbConnection - объект подключения к базе данных (PDO, MySQLi)
- $tableName - строка с названием таблицы из которой будут получены строки
- $limit количество строк, которые будут возвращены
- $offset смещение от начала таблицы с которого будут возращены $limit элементов

Пример вызова:
`
$result = getNElements($dbConnection, $tableName, $limit, $offset);

`
### updateRecord
Обновляет записи в таблице которые подходят под $condition, значениями $value.
- $dbConnection - объект подключения к базе данных (PDO, MySQLi)
- $tableName - строка с названием таблицы из которой будут получены строки
- $insertArray новые значения для столбцов, массив значений вида:
`
[
    "column_name" => "value",
    "column_name" => "value"
]
`
- $condition условие отбора строк для обновления данных, используется синтаксис выражений WHERE из SQL

`
$result = updateRecord($dbConnection, $tableName, $insertArray, $condition);

`

### deleteRecord
Удаляет записи из таблицы, если они удовлетворяют условию в $condition.
- $dbConnection - объект подключения к базе данных (PDO, MySQLi)
- $tableName - строка с названием таблицы из которой будут получены строки
- $condition условие отбора строк для удаления данных, используется синтаксис выражений WHERE из SQL


`
$result = deleteRecord($dbConnection, htmlspecialchars($_POST["tableName"]), htmlspecialchars($_POST["conditionDelete"]));
`