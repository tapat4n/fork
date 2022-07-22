<?php

namespace Tapat4n\Fork\Message;

interface MessageInterface
{
    public function set(string $msg);

    public function get(): string;

    public function remove(): bool;
}
