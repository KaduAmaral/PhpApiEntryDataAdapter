# PhpApiEntryDataAdapter
API Entry Data Adapter


### Usage with MySQLAdapter:

```php

// $_GET = ['companyId' => 1234, 'status' => ['Pending','Error']]

$loadOptions = new RequestLoadOptions([
    'filters' => $_GET
]);

$filterCollection = $loadOptions->filters();

$mysqlAdapter = new MySQLAdapter([
    'companyId' => 'com.id',
    'status'    => 'com.status'
]);

/** @var MySQLAdapterResult */
$statement = $filterCollection->getStatement($mysqlAdapter);

$filters = '';
$vars = NULL;
if ($statement->sql) { 
    // statement.sql = "com.id = :companyId AND com.status IN (:status0, :status1)"
    $filters = 'AND ' . $statement->sql;
    // statement.vars = [':companyId' => 1234, ':status0' => 'Pending', ':status1' => 'Error']
    $vars    = $statement->vars;
}

$sql = "SELECT com.* 
        FROM company com
        WHERE com.active = 1
        $filters
";

$pdo = new PDO(...);
$stmt = $pdo->prepare($sql);
$stmt->execute($vars);
$data = $stmt->fetchAll(PDO::FETCH_OBJ);
```