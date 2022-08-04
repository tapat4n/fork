<?php

namespace Tapat4n\Fork\Fork;

use RuntimeException;
use Tapat4n\Fork\Message\MessageInterface;
use Tapat4n\Fork\Message\ShmopMessage;
use Tapat4n\Fork\Process;
use Tapat4n\Fork\Worker\WorkerInterface;

final class PcntlFork implements ForkInterface
{
    private bool $isParent = true;

    /**
     * @var Process[]
     */
    private array $processes = [];

    private string $messageClass;

    private bool $waitAfterRun;

    private int $fork_limit;

    public function __construct(
        int $fork_limit = self::DEFAULT_LIMIT,
        bool $waitAfterRun = true,
        string $messageClass = ShmopMessage::class
    ) {
        if (!extension_loaded('pcntl')) {
            throw new RuntimeException('Exctension `pcntl` not loaded in php.ini');
        }
        $this->fork_limit = $fork_limit;
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
            $pid = $process->getPid();
            if ($pid !== null) {
                $result[$pid] = $process->getOutput();
            }
        }
        return $result;
    }

    public function getMessages(): array
    {
        $messages = [];
        foreach ($this->processes as $process) {
            $pid = $process->getPid();
            if ($pid !== null) {
                $messages[$pid] = $process->getMessage();
            }
        }
        return $messages;
    }

    public function dispatch(): void
    {
        foreach (array_chunk($this->processes, $this->fork_limit, true) as $processes) {
            foreach ($processes as $process) {
                $process->run();
            }
            if ($this->waitAfterRun) {
                $this->wait($processes);
            }
        }
    }

    private function wait(array $processes): void
    {
        foreach ($processes as $process) {
            $process->wait();
        }
    }

    private function createMessage(): MessageInterface
    {
        if (!class_exists($this->messageClass)) {
            throw new RuntimeException('Class ' . $this->messageClass . ' not exists');
        }
        $message = new $this->messageClass();
        if (!$message instanceof MessageInterface) {
            throw new RuntimeException('Property $messageClass must be instance of ' . MessageInterface::class);
        }
        return $message;
    }
}
