<?php

namespace Tapat4n\Fork\Handlers;

interface HandlerInterface
{
    public function __construct(int $key, int $size);

    public function handlerOpened(int $key): bool;

    public function write(string $data): bool;

    public function read(): string;

    public function delete(): bool;

    public function close(): bool;
}
