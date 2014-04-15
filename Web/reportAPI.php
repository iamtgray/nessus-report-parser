<?php
/**
 * ReportGenerator -- report.php
 * User: Simon Beattie
 * Date: 14/04/2014
 * Time: 12:27
 */

$config = require(__DIR__ . '/../config.php');

spl_autoload_register(function($className)
{
    $fileName = __DIR__ . '/' . str_replace('\\', '/', $className) . '.php';

    if(!file_exists($fileName))
    {
        return false;
    }

    require($fileName);
});

try {
    $pdo = new PDO(
        'mysql:host=' . $config['db']['hostname'] . ';dbname=' . $config['db']['database'],
        $config['db']['username'],
        $config['db']['password']
    );
} catch (PDOException $pdoError) {
    echo $pdoError->getMessage();
    exit;
}

$reports = new \Library\Reports($pdo, $config);


if ($_GET['listreports'] == '1')
{
    echo json_encode($reports->listReports());
};
if (array_key_exists('reportid', $_GET))
{
    if(array_key_exists('severity', $_GET))
    {
        echo json_encode($reports->getDetails($_GET['reportid'], $_GET['severity']));
    }
}

