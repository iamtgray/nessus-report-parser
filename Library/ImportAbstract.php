<?php
/**
 * ReportGenerator -- ImportAbstract.php
 * User: Simon Beattie @si_bt
 * Date: 14/04/2014
 * Time: 10:18
 * 
 * 
 * @review I would suggest putting a use PDO; at the top of this abstract, so that it's easy to spot going in
 * that this abstract makes use of PDO.
 */

namespace Library;


class ImportAbstract
{

    /**
     * @var PDO $pdo
     */
    protected $pdo;


    public function __construct($pdo)
    {
        $this->setPdo($pdo);
    }


    /**
     * setPdo sets the pdo property in object storage
     *
     * @param \PDO $pdo
     * @throws InvalidArgumentException
     * @return StatusAbstract
     * 
     * @review As a minor point, I would suggest type hinting PDO in this argument. Someone could submit a string
     * or anything else rather than a PDO object otherwise :)
     */
    public function setPdo($pdo)
    {
        if (empty($pdo)) {
            throw new \InvalidArgumentException(__METHOD__ . ' cannot accept an empty pdo');
        }
        $this->pdo = $pdo;
        return $this;
    }

    /**
     * getPdo returns the pdo from the object
     *
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }


} 
