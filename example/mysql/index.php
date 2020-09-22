<?php

require '../../vendor/autoload.php';

use LDL\Session\SessionManager;
use LDL\Session\Handler\PDO\MySQL\MySQLSessionHandler;

$dsn = 'mysql:host=localhost;dbname=ldl_auth';

$pdo = new \PDO($dsn,'test', '123456',[
    \PDO::ATTR_EMULATE_PREPARES => false,
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
]);

$manager = new SessionManager(
    new MySQLSessionHandler(
        $pdo
    )
);

$manager->set('test', 1);

var_dump($manager->get('test'));