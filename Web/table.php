<?php
/**
 * ReportGenerator -- index.php
 * User: Simon Beattie
 * Date: 15/04/2014
 * Time: 09:39
 */

require_once(__DIR__ . "/config.php");

header('Content-Type: text/plain'); //Setting the page to plaintext so the tabs and carriage returns format correctly to allow cut&paste into pages

echo "Click back to return to the report list\n";

if (array_key_exists('reportid', $_GET)) {
    $reportId = $_GET['reportid'];
    if (array_key_exists('severity', $_GET)) {
        $severity = $_GET['severity']; //Dealing with GET requests, setting $reportid and $severity variables
    }
}


$reportData = json_decode(getReportData($reportId, $severity, $url)); //Get all report data from the API. Returns JSON so decoding that too

outputVulnHostPort($reportData); // Picking out only the Vulnerabilities and each host, protocol and port from the full data.

if (!$reportData)
{
    die("There is no data to display, try adjusting your severity settings");
}

function outputVulnHostPort($reportData) // Pass full report array to return hosts, ports and protocols sorted by vulnerability
{
    foreach ($reportData as $vulnerability) {
        echo PHP_EOL . $vulnerability[0][0]->vulnerability . " - " .  count($vulnerability[1]) . PHP_EOL; // Output Vulnerability name

        $loop = 0;
        foreach ($vulnerability[1] as $hostObj) {
            if ($loop == 3) {
                print($hostObj->host_id . "\t" . strtoupper($hostObj->protocol) . "/" . $hostObj->port . "\n"); // Output the final column with carriage return
                $loop = 0;
                continue;
            }
            if ($loop < 3) {
                print($hostObj->host_id . "\t" . strtoupper($hostObj->protocol) . "/" . $hostObj->port . "\t"); // Output the first 3 columns with tab delimiters
                $loop++;
                continue;
            }
        }

        echo "\n\n"; // Just for tidying output

    }
}


function getReportData($reportId, $severity, $url) // Pass reportID, severity and $url from config file to return full report JSON
{
    $query = '?reportid=' . $reportId . '&severity=' . $severity;
    $report = curlGet($url, $query);
    return $report;
}


function curlGet($url, $query) // Curl function
{
    $url_final = $url . '' . $query;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_final);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $return = curl_exec($ch);
    curl_close($ch);
    return $return;
}

