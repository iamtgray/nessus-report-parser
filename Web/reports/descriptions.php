<?php
/**
 * ReportGenerator -- index.php
 * User: Simon Beattie
 * Date: 15/04/2014
 * Time: 09:39
 */

require_once(__DIR__ . "/../config.php");

$reportId = $_GET['reportid'];
$severity = $_GET['severity']; //Dealing with GET requests, setting $reportid and $severity variables

$reportData = json_decode(getReportData($reportId, $severity, $url)); //Get all report data from the API. Returns JSON so decoding that too

if (!$reportData)
{
    die("There is no data to display, try adjusting your severity settings");
}

getDescriptions($reportData); // Picking out only the Vulnerabilities and each host, protocol and port from the full data.


function getDescriptions($reportData) // Pass full report array to return hosts, ports and protocols sorted by vulnerability
{
    foreach ($reportData as $vulnerability)
    {
        echo "<b>" . $vulnerability[0]->vulnerability . "</b><br>";
        if ($vulnerability[0]->randomstormed == 1)
        {
            echo '<font color="green"><i>This has been updated by a RandomStorm staff member</i><br><br></font>';
        } else {
            echo '<font color="red"><i>This has NOT been updated by a RandomStorm staff member</i><br><br></font>';
        }
        echo "<b>Synopsis</b><br>";
        echo $vulnerability[0]->synopsis . "<br>";
        echo "<b>Description</b><br>";
        echo $vulnerability[0]->description . "<br>";
        echo "<b>Solution</b><br>";
        echo $vulnerability[0]->solution . "<br>";
        echo "<b>Plugin Family</b><br>";
        echo $vulnerability[0]->pluginFamily . "<br>";
        echo "<b>CVE References</b><br>";
        echo $vulnerability[0]->cve . "<br>";
        echo "<b>Risk Factor</b><br>";
        echo $vulnerability[0]->risk_factor . "<br>";
        echo "<b>See Also</b><br>";
        echo $vulnerability[0]->see_also;

        echo "<hr>";
    }

}


function getReportData($reportId, $severity, $url) // Pass reportID, severity and $url from config file to return full report JSON
{
    $query = '?report=3&reportid=' . $reportId . '&severity=' . $severity;
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

