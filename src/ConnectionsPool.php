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
        $connection->write('Enter your name: ');
        $this->initEvents($connection);
        $this->setConnectionData($connection, []);
    }

    private function initEvents(\React\Socket\ConnectionInterface $connection): void
    {
        $connection->on('data', function (?string $data) use ($connection) {
            $connectionData = $this->getConnectionData($connection);

            if (!isset($connectionData['name'])) {
                $this->addNewMember($data, $connection);

                return;
            }

            $name = $connectionData['name'];
            $this->sendAll("$name: $data", $connection);
        });

        $connection->on('close', function () use ($connection) {
            $connectionData = $this->getConnectionData($connection);
            $name = $connectionData['name'] ?? '';
            $this->connections->offsetUnset($connection);
            $this->sendAll("User $name leaves the chat\n", $connection);
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

    private function setConnectionData(\React\Socket\ConnectionInterface $connection, array $data): void
    {
        $this->connections->offsetSet($connection, $data);
    }

    private function getConnectionData(\React\Socket\ConnectionInterface $connection): array
    {
        return $this->connections->offsetGet($connection);
    }

    private function addNewMember(?string $name, \React\Socket\ConnectionInterface $connection): void
    {
        $name = str_replace(["\n", "\r"], '', $name);

        $this->setConnectionData($connection, ['name' => $name]);

        $this->sendAll("User $name joins the chat\n", $connection);
    }
}