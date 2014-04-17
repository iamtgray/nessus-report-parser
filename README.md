nessus-report-parser
====================

Parser and outputter for Nessus XML reports

REQUIREMENTS:

apache2
sqlite3
php5-sqlite
php5-curl
curl


CONFIGURATION:

Create Apache2 vhost with Web as the root directory
Edit Web/config.php and ensure that the path is correct to the reportsAPI.php.


USAGE:

To Import a Report:

Run import.php with the Nessus xml report filename as an argument

        php import.php nessus_report.nessus

To view the output:

Navigate to Web/index.php from a browser.

Updates:

16th April 2014:
    Changed storage engine from MySQL to SQLite3
