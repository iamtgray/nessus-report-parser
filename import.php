<?php
/**
 * ReportGenerator -- import.php
 * User: Simon Beattie
 * Date: 14/04/2014
 * Time: 10:19
 */

$config = require(__DIR__ . '/config.php');
#$xml = __DIR__ . '/nessus_report.nessus';

if (isset($argv[1]))
{
    $xml = $argv[1];
}
else
{
    die('You must provide report as argument');
}

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

$report = new \Library\ImportReport($pdo, $config);

echo "Creating report" . PHP_EOL;

print_r($report->createReport($xml));