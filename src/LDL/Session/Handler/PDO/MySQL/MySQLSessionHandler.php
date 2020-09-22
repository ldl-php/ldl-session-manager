<?php declare(strict_types=1);

namespace LDL\Session\Handler\PDO\MySQL;

use LDL\Session\Handler\SessionHandlerInterface;

class MySQLSessionHandler implements SessionHandlerInterface
{
    public const DEFAULT_MYSQL_TABLE = 'ldl_session';

    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \PDO
     */
    private $connection;

    public function __construct(
        \PDO $connection,
        string $table=null
    )
    {
        $this->connection = $connection;

        $this->table = $table ?? self::DEFAULT_MYSQL_TABLE;
        $this->createTable();
    }

    public function open($save_path, $name) : bool
    {
        $this->name = $name;
        $this->createTable();
        return true;
    }

    private function createTable() : void
    {
        $sql = sprintf('
            CREATE TABLE IF NOT EXISTS `%s`(
              `name` VARBINARY(255) NOT NULL,
              `session` VARCHAR(255) NOT NULL,
              `data` BLOB NOT NULL,
              `createdAt` DATETIME NOT NULL,
              `updatedAt` DATETIME NULL,
              PRIMARY KEY(`name`, `session`)
            ) CHARSET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB',
            $this->table
        );

        $this->connection->exec($sql);
    }

    public function close() : bool
    {
        return true;
    }

    public function read($session_id) : string
    {
        $sql = sprintf('SELECT `data` FROM `%s` WHERE `session`=:session LIMIT 1',$this->table);
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':session' => $session_id
        ]);

        return (string) $stmt->fetchColumn();
    }

    public function write($id, $data) : bool
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        if($this->read($id)){
            $sql = sprintf(
                'UPDATE `%s` SET `updatedAt`=:updated, `data`=:data WHERE `session`=:session AND `name`=:name LIMIT 1',
                $this->table
            );

            $stmt = $this->connection->prepare($sql);

            return $stmt->execute([
                ':data' => $data,
                ':session' => $id,
                ':name' => $this->name,
                ':updated' => $now->format('Y-m-d H:i:s')
            ]);
        }

        $sql = sprintf(
            'INSERT INTO `%s` SET `name`=:name, `data`=:data, `session`=:session, `createdAt`=:created',
            $this->table
        );

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            ':name' => $this->name,
            ':data' => $data,
            ':session' => $id,
            ':created' => $now->format('Y-m-d H:i:s')
        ]);
    }

    public function destroy($id)
    {
        $sql = 'DELETE FROM `%s` WHERE `name`=:name AND `session`=:session';

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            ':session' => $id,
            ':name' => $this->name
        ]);
    }

    public function gc($maxlifetime)
    {
        $sql = sprintf(
            'SELECT `session` FROM `%s` WHERE DATE_ADD(`updatedAt`, INTERVAL :interval) < NOW() AND  `name`=:name',
            $this->table
        );

        $stmt = $this->connection->prepare($sql);

        $stmt->execute([
            ':interval' => $maxlifetime,
            ':name' => $this->name
        ]);

        foreach($stmt->fetchAll(\PDO::FETCH_COLUMN) as $session){
            $this->destroy($session);
        }

        return true;
    }

}