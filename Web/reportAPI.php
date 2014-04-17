<?php
/**
 * ReportGenerator -- report.php
 * User: Simon Beattie
 * Date: 14/04/2014
 * Time: 12:27
 */


// GET
// reportid=<REPORTID>&severity=<SEVERITY>
// listreports=1

spl_autoload_register(function ($className) {
    $fileName = __DIR__ . '/' . str_replace('\\', '/', $className) . '.php';

    if (!file_exists($fileName)) {
        return false;
    }

    require($fileName);
});

try { // Create PDO Object
#    $pdo = new PDO(
#        'mysql:host=' . $config['db']['hostname'] . ';dbname=' . $config['db']['database'],
#        $config['db']['username'],
#        $config['db']['password']
#    );
    $pdo = new PDO('sqlite:../reports.sqlite');
} catch (PDOException $pdoError) {
    echo $pdoError->getMessage();
    exit;
}

$reports = new \Library\Reports($pdo, $config); // Create report object


if ($_GET['listreports'] == '1') {
    echo json_encode($reports->listReports()); // Return list of reports imported into the system
};
if (array_key_exists('reportid', $_GET)) {
    if (array_key_exists('severity', $_GET)) {
        echo json_encode($reports->getDetails($_GET['reportid'], $_GET['severity'])); // Return report details in JSON format.
    }
}

