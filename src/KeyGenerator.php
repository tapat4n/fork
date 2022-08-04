<?php

namespace Tapat4n\Fork;

use Exception;

final class KeyGenerator
{
    /**
     * @throws Exception
     */
    public function generate(): int
    {
        return random_int(0, PHP_INT_MAX);
    }

    /**
     * @throws Exception
     */
    public function generateId(): string
    {
        return uniqid($this->generate(), true);
    }
}
