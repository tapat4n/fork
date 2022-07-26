<?php

namespace Tapat4n\Fork\Message;

use Tapat4n\Fork\Handlers\FileHandler;
use Tapat4n\Fork\KeyGenerator;

class FileMessage implements MessageInterface
{
    public const SIZE = 1024;

    private FileHandler $handler;

    private int $key;

    public function __construct()
    {
        $this->key = (new KeyGenerator())->generate();
        $this->handler = new FileHandler($this->key, self::SIZE);
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
