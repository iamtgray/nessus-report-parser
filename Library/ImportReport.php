<?php
/**
 * ReportGenerator -- ImportReport.php
 * User: Simon Beattie @si_bt
 * Date: 14/04/2014
 * Time: 10:22
 */

namespace Library;

class ImportReport extends \Library\ImportAbstract
{

    protected $xmlObj;
    protected $reportName;
    protected $reportID;

    public function createReport($xml) // Create report in database and spawn further functions for vulnerabilities and hosts.
    {
        /**
         * @review I would set this either in the index file (your importReport.php) or rely on existing timezones
         * set on the machine.
         */
        date_default_timezone_set('Europe/London');
        $this->xmlObj = simplexml_load_file($xml);
        
        // @review I would confirm that the "Report" property exists in the XML (and then that it has a child with
        // a name property)
        
        // @review Why are you adding a new line to the end of the report name? 
        $this->reportName = $this->xmlObj->Report[0]['name'] . PHP_EOL;
        $createReport = $this->getPdo()->prepare('INSERT INTO reports (report_name, created) VALUES(?, ?)');
        
        // @review in your array here, I would use the $this->reportName rather than calling from the object again
        $createdOk = $createReport->execute(array($this->xmlObj->Report[0]['name'], date('Y-m-d H:i:s')));
        if (!$createdOk) {
            die('Sorry, we couldn\'t create the new report: ' . $createReport->errorInfo()[2] . PHP_EOL);
        }

        $this->reportID = $this->getPdo()->lastInsertId();
        $this->createHost();

        return array('reportName' => $this->reportName);
    }

    public function completeReport() // Complete report - NOT YET IMPLEMENTED
    {
        $completedInsert = $this->getPdo()->prepare('UPDATE reports SET imported = 1 WHERE report_id = ?');
        $reportCompleted = $completedInsert->execute(array('1'));
        if (!$reportCompleted) {
            die('Sorry, we couldn\'t complete the report insert: ' . $completedInsert->errorInfo()[2] . PHP_EOL);
        }

        return $this->getPdo()->lastInsertId();
    }

    private function createHost() // Create host ready to have vulnerabilities assigned. This will always create a new host for each report.
    {
        $count = 1;
        $insertHost = $this->getPdo()->prepare('INSERT INTO hosts (report_id, host_name) VALUES(?, ?)');
        
        // @review same as above, I would check the ReportHost exists within Report's 0th entity.
        foreach ($this->xmlObj->Report[0]->ReportHost as $num => $host) {
            echo 'Importing host ' . $count . ' of ' . $this->xmlObj->Report[0]->ReportHost->count() . ' ... ';
            $insertedHost = $insertHost->execute(array($this->reportID, $host['name']));
            if (!$insertedHost) {
                die('Sorry, we couldn\'t insert the host: ' . $insertHost->errorInfo()[2] . PHP_EOL);
            }

            $hostID = $this->getPdo()->lastInsertId();
            $properties = $host[0]->HostProperties->children();
            /* @var SimpleXMLElement $properties */

            $this->addHostDetails($hostID, $properties);

            $this->addVulnerability($host, $hostID);

            $count++;
        }
    }

    // @review check the properties is an instance of SimpleXMLElement, although you're not using it elsewhere
    // children could return empty.
    private function addHostDetails($hostID, $properties) // Add all host details such as FQDN, Operating system etc to the database
    {
        foreach ($properties as $tagItem) /* @var SimpleXMLElement $tagItem */ {

            $names = array('mac-address', 'system-type', 'operating-system', 'host-ip', 'host-fqdn', 'netbios-name');

            $attribs = $tagItem->attributes();
            $name = $attribs['name'];
            $value = (string)$tagItem;
            
            // @review although I understand why you have prepared thus, you should still be aware that if the name
            // has a char that mySQL would not accept as a column name, some icky errors could be thrown.
            $hostUpdate = $this->getPdo()->prepare('UPDATE hosts SET ' . str_replace('-', '_', $name) . '=? WHERE id=?');

            if (in_array($name, $names)) {
                $updateHost = $hostUpdate->execute(array($value, $hostID));
                if (!$updateHost) {
                    die('Sorry, we couldn\'t update the host: ' . $hostUpdate->errorInfo()[2] . PHP_EOL);
                }
            }

        }
    }

    // @review your method comment (end of line 109) should really be in a DockBlock above the method, so it can be 
    // Auto-detected by your IDE
    private function addVulnerability($host, $hostID) // Add vulnerabilities. This will add the vulnerability if it doesn't yet exist,
    { // and will add a link between the host and that vulnerability including the protocol and port recorded.
        $foundVulnerabilities = array();

        // @review Again, check ReportItem exists, and that $host is an instance of SimpleXMLElement
        foreach ($host->ReportItem as $item) /* @var SimpleXMLElement $item */ {
            $attributes = array();
            if (!$item->cvss_base_score)
            {
                $cvss = 0.0;
            } else {
                $cvss = $item->cvss_base_score;
            }



            $addVuln = $this->getPdo()->prepare('INSERT OR REPLACE INTO vulnerabilities (pluginID, vulnerability, svc_name, severity, pluginFamily, description, cve, risk_factor, see_also, solution, synopsis) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $addVulnLink = $this->getPdo()->prepare('INSERT INTO host_vuln_link (report_id, host_id, plugin_id, port, protocol) VALUES(?, ?, ?, ?, ?)');

            foreach ($item->attributes() as $attribute => $value) {

                if ($attribute != 'pluginName') {
                    $attributes[$attribute] = (string)$value;
                }

            }
            // @review as a very minor point, I would split these into multiple lines (the array that is) so it's more easily readable.
            $vulnAdded = $addVuln->execute(array($attributes['pluginID'], $item['pluginName'], $attributes['svc_name'], $cvss, $attributes['pluginFamily'], $item->description, $item->cve, $item->risk_factor, $item->see_also, $item->solution, $item->synopsis));
            if (!$vulnAdded) {
                die('Sorry, we couldn\'t add the vulnerability: ' . $addVuln->errorInfo()[2] . PHP_EOL);
            }


            $vulnLinkAdded = $addVulnLink->execute(array($this->reportID, $hostID, $attributes['pluginID'], $attributes['port'], $attributes['protocol'],));
            if (!$vulnLinkAdded) {
                die('Sorry, we couldn\'t add the vulnerability link: ' . $addVulnLink->errorInfo()[2] . PHP_EOL);
            }

            // @review if all you are doing is counting this at the end, you might as just incremenet a variable 
            // rather than creating a large array,
            $foundVulnerabilities[$attributes['pluginID']] = (string)$item['pluginName'];

        }

        echo "Found " . count($foundVulnerabilities) . " vulnerabilities" . PHP_EOL;

    }

}
