<?php

class ConnectionsPool
{
    /** @var \SplObjectStorage */
    private $connections;

    public function __construct()
    {
        $this->connections = new SplObjectStorage();
    }

    public function add(\React\Socket\ConnectionInterface $connection): void
    {
        $connection->write("Hi!\n");

        $this->initEvents($connection);
        $this->connections->attach($connection);
        $this->sendAll("New user entered the chat!\n", $connection);
    }

    private function initEvents(\React\Socket\ConnectionInterface $connection): void
    {
        $connection->on('data', function ($data) use ($connection) {
            $this->sendAll($data, $connection);
        });

        $connection->on('close', function () use ($connection) {
            $this->sendAll("User leaves the chat\n", $connection);
        });
    }

    private function sendAll(string $string, \React\Socket\ConnectionInterface $connection): void
    {
        foreach ($this->connections as $conn) {
            if ($conn !== $connection) {
                $conn->write($string);
            }
        }
    }
}