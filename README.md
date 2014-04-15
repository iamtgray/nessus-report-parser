nessus-report-parser
====================

Parser and outputter for Nessus XML reports

REQUIREMENTS:

apache2
mysql-sever
php5-mysql
php5-curl
curl

DATABASE SETUP:

Create a database in MySQL and import the mysql.schema

        mysql -u <USER> -p <DATABASE NAME> < mysql.schema

CONFIGURATION:

Edit config.php and enter the username, password and database name
Edit Web/config.php and ensure that the path is correct to the reportsAPI.php.

Create Apache2 vhost with Web as the root directory

USAGE:

To Import a Report:

Run import.php with the Nessus xml report filename as an argument

        php import.php nessus_report.nessus

To view the output:

Navigate to Web/index.php from a browser.
