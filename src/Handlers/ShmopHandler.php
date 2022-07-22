<?php

namespace Tapat4n\Fork\Handlers;

use Shmop;
use Exception;

final class ShmopHandler implements HandlerInterface
{
    private const DEFAULT_PERMISSION = 0644;

    private const SHARED_MEMORY_READONLY = 'a';
    private const SHARED_MEMORY_CREATE = 'c';
    private const SHARED_MEMORY_READ_WRITE = 'w';

    private Shmop|false $shared_memory_key;

    /**
     * @param int $key
     * @param int $size
     * @param int $perms
     * @throws \Exception
     */
    public function __construct(int $key, int $size, int $perms = self::DEFAULT_PERMISSION)
    {
        if ($this->handlerOpened($key)) {
            $this->shared_memory_key = shmop_open($key, self::SHARED_MEMORY_READ_WRITE, $perms, $size);
        } else {
            $this->shared_memory_key = shmop_open($key, self::SHARED_MEMORY_CREATE, $perms, $size);
        }
        if (!$this->shared_memory_key) {
            throw new Exception('Can`t open shared memeory');
        }
    }

    public function handlerOpened(int $key): bool
    {
        return (bool)@shmop_open($key, self::SHARED_MEMORY_READONLY, 0, 0);
    }

    public function write($data): bool
    {
        return (bool)shmop_write($this->shared_memory_key, $data, 0);
    }

    public function read(): string
    {
        return trim(shmop_read($this->shared_memory_key, 0, shmop_size($this->shared_memory_key)));
    }

    public function delete(): bool
    {
        shmop_write($this->shared_memory_key, str_pad('', shmop_size($this->shared_memory_key), ' '), 0);
        return shmop_delete($this->shared_memory_key);
    }

    public function close(): bool
    {
        return true;
    }
}