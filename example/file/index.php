<?php

require '../../vendor/autoload.php';

use LDL\Session\SessionManager;
use LDL\Session\Handler\File\FileSessionHandler;

$manager = new SessionManager(
    new FileSessionHandler(),
    [
        'session.save_path' => __DIR__.'/sessions',
        'session.gc_probability' => 1
    ]
);

$manager->set('test', 1);

var_dump($manager->get('test'));