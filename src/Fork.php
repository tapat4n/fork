<?php

namespace Tapat4n\Fork;

use Tapat4n\Fork\Message\MessageInterface;
use Tapat4n\Fork\Message\ShmopMessage;
use RuntimeException;

final class Fork
{
    private bool $isParent = true;

    /**
     * @var Process[]
     */
    private array $processes = [];

    private string $messageClass;

    private bool $waitAfterRun;

    public function __construct(
        bool $waitAfterRun = true,
        string $messageClass = ShmopMessage::class
    ) {
        if (!extension_loaded('pcntl')) {
            throw new RuntimeException('Exctension `pcntl` not loaded in php.ini');
        }
        $this->messageClass = $messageClass;
        $this->waitAfterRun = $waitAfterRun;
    }

    public function __destruct()
    {
        if ($this->isParent) {
            foreach ($this->processes as $process) {
                $process->clearMessage();
            }
        }
    }

    public function addWorker(callable|WorkerInterface $worker): void
    {
        $this->processes[] = new Process(
            $this->createMessage(),
            $this->createMessage(),
            $worker,
            function (Process $process) {
                if ($process->getPid() === 0) {
                    $this->isParent = false;
                }
            }
        );
    }

    public function getOutput(): array
    {
        $result = [];
        foreach ($this->processes as $process) {
            $result[$process->getPid()] = $process->getOutput();
        }
        return $result;
    }

    public function getMessages(): array
    {
        $messages = [];
        foreach ($this->processes as $process) {
            $messages[$process->getPid()] = $process->getMessage();
        }
        return $messages;
    }

    public function dispatch(): void
    {
        foreach ($this->processes as $process) {
            $process->run();
        }
        if ($this->waitAfterRun) {
            $this->wait();
        }
    }

    public function wait(): void
    {
        foreach ($this->processes as $process) {
            $process->wait();
        }
    }

    private function createMessage(): MessageInterface
    {
        $message = new $this->messageClass();
        if (!$message instanceof MessageInterface) {
            throw new RuntimeException('Property $messageClass must be instance of ' . MessageInterface::class);
        }
        return $message;
    }
}
