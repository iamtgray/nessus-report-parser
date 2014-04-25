<?php
/**
 * ReportGenerator -- report.php
 * User: Simon Beattie @si_bt
 * Date: 14/04/2014
 * Time: 12:27
 */


// GET
// reportid=<REPORTID>&severity=<SEVERITY>
// listreports=1

// @review, you are using this SPL method and PDO creation twice, remove from this and the importer (directory above)
// and pop into a single application bootstrap file to be included.
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

$reports = new \Library\Reports($pdo); // Create report object


if (array_key_exists('listreports', $_GET)) {
    echo json_encode($reports->listReports()); // Return list of reports imported into the system
    die();
};

// @review SO report is a sort of report type, so 1 is for hosts, 2 is for vulnerabilities, 3 is for descriptions.
if (array_key_exists('report', $_GET)) {
    if (array_key_exists('reportid', $_GET)) {
        switch($_GET['report']) {

            case 1:
                if (array_key_exists('severity', $_GET)) {
                    echo json_encode($reports->getHosts($_GET['reportid'], $_GET['severity'])); // Return report details in JSON format.
                }
                else
                {
                    die("You must pass a severity level");
                }
                break;
            case 2:
                if (array_key_exists('severity', $_GET)) {
                    echo json_encode($reports->getVulnerabilities($_GET['reportid'], $_GET['severity']));
                }
                else
                {
                    die("You must pass a severity level");
                }
                break;
            case 3:
                if (array_key_exists('severity', $_GET)) {
                    echo json_encode($reports->getDescriptions($_GET['reportid'], $_GET['severity']));
                }
                else
                {
                    die("You must pass a severity level");
                }
                break;
        }

    }
    else
    {
        die("You must pass a reportID");
    }
}

/**
 * @review I have renamed report to reportType (because as far as I can tell, that's what it is)
 * And also changed reportid to reportId (camel casing).
 */

if(array_key_exists('reportType', $_GET))
{
    if(!array_key_exists('reportId', $_GET) || !array_key_exists('severity', $_GET))
    {
        die('You must pass me both the report ID and the severity level you wish to view.');
    }
    
    $reportType = $_GET['reportType'];
    $reportId = $_GET['reportId'];
    $severity = $_GET['severity'];
    
    switch($reportType)
    {
        case 'hosts':
            echo json_encode($reports->getHosts($reportId, $severity)); // Return report details in JSON format.
            break;
            
        case 'vulnerabilities':
            echo json_encode($reports->getVulnerabilities($reportId, $severity));
            break;
            
        case 'descriptions':
            echo json_encode($reports->getDescriptions($reportId, $severity));
            break;
            
        default:
            die('Sorry, I don\t understand the reportType you requested.');
    }
}

