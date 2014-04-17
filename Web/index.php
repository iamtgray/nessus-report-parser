<?php
/**
 * ReportGenerator -- output.php
 * User: Simon Beattie
 * Date: 15/04/2014
 * Time: 11:24
 */

require_once(__DIR__ . "/config.php");

echo 'Imported Reports<br><br>';
$reports = json_decode(getReportList($url));
foreach ($reports as $report) {
    echo 'ID: ' . $report->id . '<br>';
    echo 'Name: ' . $report->report_name . '<br>';
    echo 'Created: ' . $report->created . '<br>';
    echo '<a href="table.php?reportid=' . $report->id . '&severity=' . $severity . '">View this report</a><br><br>';
}

function getReportList($url)
{
    $query = '?listreports=1';
    $report = curlGet($url, $query);
    if (!$report)
    {
        return "There are no reports to display";
    }
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