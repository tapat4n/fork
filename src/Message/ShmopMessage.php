<?php

namespace Tapat4n\Fork\Message;

use Exception;
use Tapat4n\Fork\Handlers\ShmopHandler;

final class ShmopMessage implements MessageInterface
{
    public const SIZE = 1024;

    private ShmopHandler $handler;

    private int $key;

    private int $status = 0;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->generateKey();
        $this->handler = new ShmopHandler($this->key, self::SIZE);
    }

    public function set(string $msg): bool
    {
        return $this->handler->write($msg);
    }

    public function get(): string
    {
        return $this->handler->read();
    }

    public function remove(): bool
    {
        return $this->handler->delete() && $this->handler->close();
    }

    /**
     * @throws Exception
     */
    private function generateKey(): void
    {
        $this->key = random_int(0, PHP_INT_MAX);
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }
}
