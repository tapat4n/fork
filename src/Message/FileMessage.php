<?php

namespace Tapat4n\Fork\Message;

use Tapat4n\Fork\Handlers\FileHandler;
use Tapat4n\Fork\KeyGenerator;

final class FileMessage implements MessageInterface
{
    public const SIZE = 2048;

    private FileHandler $handler;

    /**
     * @throws \Exception
     */
    public function __construct($message_size = self::SIZE)
    {
        $this->handler = new FileHandler((new KeyGenerator())->generate(), $message_size);
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
