<?php


namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class TableDetails
{
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Get the date of the last modification on a table
     * @param $table
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function lastUpdate($table)
    {
        $conn = $this->manager->getConnection();

        $sql = 'SHOW TABLE STATUS LIKE :table';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['table' => $table]);

        $data = $stmt->fetchAll();

        return $data['0']['Update_time'];

    }
}