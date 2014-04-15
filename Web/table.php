<?php
/**
 * ReportGenerator -- index.php
 * User: Simon Beattie
 * Date: 15/04/2014
 * Time: 09:39
 */

require_once(__DIR__ . "/config.php");

header('Content-Type: text/plain');

echo "Click back to return to the report list\n";

if (array_key_exists('reportid', $_GET))
{
    $reportId = $_GET['reportid'];
    if(array_key_exists('severity', $_GET))
    {
        $severity = $_GET['severity'];
    }
}


$reportData = json_decode(getReportData($reportId, $severity, $url));
outputVulnHostPort($reportData);



function outputVulnHostPort($reportData)
{
    foreach ($reportData as $vulnerability) {
        echo PHP_EOL . $vulnerability[0][0]->vulnerability . PHP_EOL;
        $count = count($vulnerability[1]);
        $loop = 0;

        foreach ($vulnerability[1] as $hostObj) {
            if ($loop < 3) {
                print($hostObj->host_id . "\t" . strtoupper($hostObj->protocol) . "/" . $hostObj->port . "\t");
                $loop++;
            }

            if ($loop == 3) {
                print($hostObj->host_id . "\t" . strtoupper($hostObj->protocol) . "/" . $hostObj->port . "\n");
                $loop = 0;
            }
        }

        echo "\n\n";

    }
}

function getReportData($reportId, $severity, $url)
{
    $query = '?reportid=' . $reportId . '&severity=' . $severity;
    $report = curlGet($url, $query);
    return $report;
}


function curlGet($url, $query)
{
    $url_final = $url . '' . $query;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_final);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $return = curl_exec($ch);
    curl_close($ch);
    return $return;
}

