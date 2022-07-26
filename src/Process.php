<?php

namespace Tapat4n\Fork;

use RuntimeException;
use Tapat4n\Fork\Message\MessageInterface;

use const WUNTRACED;

final class Process
{
    private MessageInterface $message;

    private WorkerInterface $worker;

    private MessageInterface $outputMessage;

    private $afterFork;

    private ?int $pid = null;

    private ?int $status = null;

    private bool $wasRun = false;

    public function __construct(
        MessageInterface $message,
        MessageInterface $outputMessage,
        WorkerInterface|callable $worker,
        callable $afterFork
    ) {
        if (is_callable($worker)) {
            $worker = $this->createFromCallable($worker);
        }
        $this->worker = $worker;
        $this->message = $message;
        $this->afterFork = $afterFork;
        $this->outputMessage = $outputMessage;
    }

    public function getPid(): ?int
    {
        return $this->pid;
    }

    public function run(): void
    {
        if ($this->wasRun) {
            throw new RuntimeException("Can't fork process. Already forked");
        }
        $this->pid = pcntl_fork();
        if ($this->pid === -1) {
            throw new RuntimeException("Can't fork process. Check `pcntl` extension.");
        }
        if ($this->afterFork) {
            call_user_func($this->afterFork, $this);
        }
        if ($this->pid === 0) {
            ob_start();
            register_shutdown_function(function () {
                $this->outputMessage->set(ob_get_contents());
                ob_clean();
            });
            try {
                $this->worker->run($this->message);
            } catch (\Throwable $exception) {
                $this->outputMessage->set(json_encode([$exception->getMessage(), $exception->getTraceAsString()], JSON_THROW_ON_ERROR));
                exit(1);
            }
            exit(0);
        }
        $this->wasRun = true;
    }

    public function wait(): void
    {
        if ($this->pid > 0) {
            pcntl_waitpid($this->pid, $this->status, WUNTRACED);
        }
    }

    public function getMessage(): string
    {
        return $this->message->get();
    }

    public function getOutput(): string
    {
        return $this->outputMessage->get();
    }

    public function clearMessage(): bool
    {
        return $this->message->remove();
    }

    private function createFromCallable(callable $worker): WorkerInterface
    {
        return new class($worker) implements WorkerInterface {
            private $worker;

            public function __construct(callable $worker)
            {
                $this->worker = $worker;
            }

            public function run(MessageInterface $message)
            {
                return call_user_func($this->worker, $message);
            }
        };
    }
}
