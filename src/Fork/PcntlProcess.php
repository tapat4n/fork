<?php

namespace Tapat4n\Fork\Fork;

use Tapat4n\Fork\Message\MessageInterface;
use Tapat4n\Fork\Worker\WorkerInterface;
use Throwable;

final class PcntlProcess implements ProcessForkInterface
{
    private const SUCCESS_STATUS = 0;

    private const SUB_PID = 0;

    private ?int $pid = null;

    private ?int $status = null;

    private bool $is_running = false;

    private WorkerInterface $worker;

    private MessageInterface $message;

    private MessageInterface $outputMessage;

    /**
     * @var callable|null
     */
    private $on_forked;

    public function __construct(
        WorkerInterface  $worker,
        MessageInterface $message,
        MessageInterface $outputMessage,
        callable|null    $on_forked = null,
    )
    {
        $this->worker = $worker;
        $this->message = $message;
        $this->outputMessage = $outputMessage;
        $this->on_forked = $on_forked;
    }

    public function getPid(): ?int
    {
        return $this->pid;
    }

    public function isRunned(): bool
    {
        if ($this->getPid() === null) {
            return false;
        }
        return $this->is_running;
    }

    public function isSub(): bool
    {
        return $this->getPid() === self::SUB_PID;
    }

    public function isParent(): bool
    {
        return !$this->isSub();
    }

    public function run(): void
    {
        if ($this->isRunned()) {
            throw new ProcessException("Can't fork process. Already forked");
        }
        $this->pid = pcntl_fork();
        if ($this->getPid() === -1) {
            throw new ProcessException("Can't fork process. Check `pcntl` extension.");
        }
        if ($this->on_forked) {
            call_user_func($this->on_forked, $this);
        }
        if ($this->getPid() === self::SUB_PID) {
            if (is_resource(STDIN)) {
                fclose(STDIN);
            }
            if (is_resource(STDOUT)) {
                fclose(STDOUT);
            }
            if (is_resource(STDERR)) {
                fclose(STDERR);
            }
            ob_start();
            register_shutdown_function(function () {
                $this->outputMessage->set(ob_get_contents());
                ob_clean();
            });
            try {
                $this->worker->run($this->message);
                exit(0);
            } catch (Throwable $exception) {
                $exception_message = json_encode(
                    [$exception->getMessage(), $exception->getTraceAsString()],
                    JSON_THROW_ON_ERROR
                );
                $this->outputMessage->set($exception_message);
                exit(1);
            }
        }
        $this->is_running = true;
    }

    public function wait(): void
    {
        if ($this->isRunned()) {
            pcntl_waitpid($this->getPid(), $this->status, WUNTRACED);
            $this->is_running = false;
        }
    }

    public function isSuccessful(): bool
    {
        return $this->status === self::SUCCESS_STATUS;
    }

    public function destroy(): void
    {
        $this->message->remove();
        $this->outputMessage->remove();
    }

    public function isRunning(): bool
    {
        return file_exists('/proc/' . $this->getPid());
    }
}
