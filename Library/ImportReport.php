<?php
/**
 * ReportGenerator -- ImportReport.php
 * User: Simon Beattie
 * Date: 14/04/2014
 * Time: 10:22
 */

namespace Library;

class ImportReport extends \Library\ImportAbstract
{

    protected $xmlObj;
    protected $reportName;
    protected $reportID;

    public function createReport($xml)
    {
        $this->xmlObj = simplexml_load_file($xml);
        $this->reportName = $this->xmlObj->Report[0]['name'] . PHP_EOL;
        $createReport = $this->getPdo()->prepare('INSERT INTO reports (report_name, created) VALUES(?, ?)');
        $createdOk = $createReport->execute(array($this->xmlObj->Report[0]['name'], date('Y-m-d H:i:s')));
        if (!$createdOk) {
            die('Sorry, we couldn\'t create the new report: ' . $createReport->errorInfo()[2] . PHP_EOL);
        }

        $this->reportID = $this->getPdo()->lastInsertId();
        $this->createHost();

        return array('reportName' => $this->reportName);
    }

    public function completeReport()
    {
        $completedInsert = $this->getPdo()->prepare('UPDATE reports SET imported = 1 WHERE report_id = ?');
        $reportCompleted = $completedInsert->execute(array('1'));
        if (!$reportCompleted) {
            die('Sorry, we couldn\'t complete the report insert: ' . $completedInsert->errorInfo()[2] . PHP_EOL);
        }

        return $this->getPdo()->lastInsertId();
    }

    private function createHost()
    {
        $count = 1;
        $insertHost = $this->getPdo()->prepare('INSERT INTO hosts (report_id, host_name) VALUES(?, ?)');
        foreach ($this->xmlObj->Report[0]->ReportHost as $num => $host) {
            echo 'Importing host ' . $count . ' of ' . $this->xmlObj->Report[0]->ReportHost->count() . ' ... ';
            $insertedHost = $insertHost->execute(array($this->reportID, $host['name']));
            if (!$insertedHost) {
                die('Sorry, we couldn\'t insert the host: ' . $insertHost->errorInfo()[2] . PHP_EOL);
            }

            $hostID = $this->getPdo()->lastInsertId();
            $properties = $host[0]->HostProperties->children(); /* @var SimpleXMLElement $properties */

            $this->addHostDetails($hostID, $properties);

            $this->addVulnerability($host, $hostID);

            $count++;
        }
    }

    private function addHostDetails($hostID, $properties)
    {
        foreach ($properties as $tagItem) /* @var SimpleXMLElement $tagItem */ {

            $names = array('mac-address', 'system-type', 'operating-system', 'host-ip', 'host-fqdn', 'netbios-name');

            $attribs = $tagItem->attributes();
            $name = $attribs['name'];
            $value = (string)$tagItem;
            $hostUpdate = $this->getPdo()->prepare('UPDATE hosts SET ' . str_replace('-', '_', $name) . '=? WHERE id=?');

            if (in_array($name, $names)) {
                $updateHost = $hostUpdate->execute(array($value, $hostID));
                if (!$updateHost) {
                    die('Sorry, we couldn\'t update the host: ' . $hostUpdate->errorInfo()[2] . PHP_EOL);
                }
            }

        }
    }

    private function addVulnerability($host, $hostID)
    {
        $foundVulnerabilities = array();

        foreach ($host->ReportItem as $item) /* @var SimpleXMLElement $item */ {
            $attributes = array();


            $addVuln = $this->getPdo()->prepare('INSERT INTO vulnerabilities (pluginID, vulnerability, svc_name, severity, pluginFamily) VALUES(?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE pluginID=PluginID');
            $addVulnLink = $this->getPdo()->prepare('INSERT INTO host_vuln_link (report_id, host_id, plugin_id, port, protocol) VALUES(?, ?, ?, ?, ?)');

            foreach ($item->attributes() as $attribute => $value) {

                if ($attribute != 'pluginName') {
                    $attributes[$attribute] = (string)$value;
                }

            }

            $vulnAdded = $addVuln->execute(array($attributes['pluginID'], $item['pluginName'], $attributes['svc_name'], $attributes['severity'], $attributes['pluginFamily']));
            if (!$vulnAdded) {
                die('Sorry, we couldn\'t add the vulnerability: ' . $addVuln->errorInfo()[2] . PHP_EOL);
            }



            $vulnLinkAdded = $addVulnLink->execute(array($this->reportID, $hostID, $attributes['pluginID'], $attributes['port'],  $attributes['protocol'],));
            if (!$vulnLinkAdded) {
                die('Sorry, we couldn\'t add the vulnerability link: ' . $addVulnLink->errorInfo()[2] . PHP_EOL);
            }

            $foundVulnerabilities[$attributes['pluginID']] = (string)$item['pluginName'];

        }


        #print_r($foundVulnerabilities);
        echo "Found " . count($foundVulnerabilities) . " vulnerabilities" . PHP_EOL;

    }

}
