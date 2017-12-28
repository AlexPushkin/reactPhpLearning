<?php

require '../vendor/autoload.php';

$loop = \React\EventLoop\Factory::create();
$connector = new \React\Socket\Connector($loop);
$stdin = new \React\Stream\ReadableResourceStream(STDIN, $loop);
$stdout = new \React\Stream\WritableResourceStream(STDOUT, $loop);

$connector->connect('127.0.0.1:8080')
    ->then(
        function (\React\Socket\ConnectionInterface $connection) use ($stdin, $stdout): void {
            $connection->on('data', function (?string $data) use ($stdout): void {
                $stdout->write($data);
            });

            $stdin->on('data', function ($data) use ($connection): void {
                $connection->write($data);
            });
        },
        function (\Exception $exception): void {
            echo "Connection failed: {$exception->getMessage()}";
        }
    );

$loop->run();