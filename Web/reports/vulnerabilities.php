<?php
/**
 * ReportGenerator -- vulnerabilities.php
 * User: Simon Beattie
 * Date: 15/04/2014
 * Time: 09:39
 */

require_once(__DIR__ . "/../config.php");

header('Content-Type: text/plain'); //Setting the page to plaintext so the tabs and carriage returns format correctly to allow cut&paste into pages


$reportId = $_GET['reportid'];
$severity = $_GET['severity'];


$reportData = json_decode(getReportData($reportId, $severity, $url)); //Get all report data from the API. Returns JSON so decoding that too

if (!$reportData)
{
    die("There is no data to display, try adjusting your severity settings");
}

outputVulnHostPort($reportData); // Picking out only the Vulnerabilities and each host, protocol and port from the full data.



function outputVulnHostPort($reportData) // Pass full report array to return hosts, ports and protocols sorted by vulnerability
{
    $data = array();
    foreach ($reportData as $hostData)
    {
        if (!$hostData->OS){
            $OS = "Unable to accurately identify";
        } else {
            $OS = $hostData->OS;
        }

        if (substr_count($OS, 'Windows') > 1)
        {
            $OS = "Microsoft Windows";
        }

        if ($hostData->fqdn == "")
        {
            $name = $hostData->netbios;
        } else {
            $name = $hostData->fqdn;
        }

        if (!$name)
        {
            $name = "Unable to accurately identify";
        }

        foreach ($hostData->vulnerabilities as $vulnerability)
        {
            $data[] = array(
                                                'ip' => ip2long($hostData->hostname),
                                                'name' => $name,
                                                'os' => $OS,
                                                'vuln' => $vulnerability->name,
                                                'risk' => $vulnerability->risk,
                                                'severity' => $vulnerability->severity);
        }

    }

    usort($data, function($firstArrayElement, $secondArrayElement)
    {
        $first = $firstArrayElement['ip'];
        $second = $secondArrayElement['ip'];

        $ret = strcmp($first, $second);
        if($ret == 0)
        {
            return strcmp($secondArrayElement['severity'], $firstArrayElement['severity']);
        }
        return $ret;

    });

    $ip = "";
    foreach ($data as $vuln)
    {
        if ($ip == long2ip($vuln['ip']))
        {
            print (" \t" . " \t" . " \t" . $vuln['vuln'] . "\t" . $vuln['risk'] . "\t" . $vuln['severity'] . "\n");
        } else {
            print (long2ip($vuln['ip']) . "\t" . $vuln['name'] . "\t" . $vuln['os'] . "\t" . $vuln['vuln'] . "\t" . $vuln['risk'] . "\t" . $vuln['severity'] . "\n");
            $ip = long2ip($vuln['ip']);
        }

    }
}


function getReportData($reportId, $severity, $url) // Pass reportID, severity and $url from config file to return full report JSON
{
    $query = '?report=2&reportid=' . $reportId . '&severity=' . $severity;
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

