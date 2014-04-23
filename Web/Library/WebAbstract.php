<?php
/**
 * ReportGenerator -- ImportAbstract.php
 * User: Simon Beattie @si_bt
 * Date: 14/04/2014
 * Time: 10:18
 */

namespace Library;


class WebAbstract
{

    /**
     * @var PDO $pdo
     */
    protected $pdo;

    /**
     * @var array $config
     */
    protected $config;

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