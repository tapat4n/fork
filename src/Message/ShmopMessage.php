<?php

namespace Tapat4n\Fork\Message;

use Exception;
use Tapat4n\Fork\Handlers\ShmopHandler;
use Tapat4n\Fork\KeyGenerator;

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
        $this->key = (new KeyGenerator())->generate();
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
}
