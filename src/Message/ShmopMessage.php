<?php

namespace Tapat4n\Fork\Message;

use Exception;
use Tapat4n\Fork\Handlers\ShmopHandler;

final class ShmopMessage implements MessageInterface
{
    public const SIZE = 1024;

    private ShmopHandler $handler;

    private int $key;

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
        $deleted = $this->handler->delete();
        $this->handler->close();
        return $deleted;
    }

    /**
     * @throws Exception
     */
    private function generateKey(): void
    {
        $this->key = random_int(0, PHP_INT_MAX);
    }
}
