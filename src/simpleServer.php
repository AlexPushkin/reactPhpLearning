<?php

require '../vendor/autoload.php';
require  'ConnectionsPool.php';

$loop = \React\EventLoop\Factory::create();
$socket = new \React\Socket\Server('127.0.0.1:8080', $loop);
$pool = new ConnectionsPool();

$socket->on('connection', function (\React\Socket\ConnectionInterface $connection) use ($pool) {
    $pool->add($connection);
});

echo "Listening on {$socket->getAddress()}\n";
$loop->run();