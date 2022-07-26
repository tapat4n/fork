<?php

namespace Tapat4n\Fork\Handlers;

use function fopen;
use function fwrite;
use function fread;
use function fseek;
use function fclose;
use function unlink;
use function sys_get_temp_dir;
use function is_resource;
use function filesize;
use function strlen;

class FileHandler implements HandlerInterface
{
    private int $key;
    private int $size;

    private $stream;

    public function __construct(int $key, int $size)
    {
        $this->key = $key;
        $this->size = $size;
        $this->stream = fopen($this->getPath(), 'w+b');
    }

    public function handlerOpened(int $key): bool
    {
        return true;
    }

    public function write(string $data): bool
    {
        return (bool)fwrite($this->stream, $data, strlen($data));
    }

    public function read(): string
    {
        $size = filesize($this->getPath());
        if ($size > 0) {
            fseek($this->stream, 0);
            return fread($this->stream, filesize($this->getPath()));
        }
        return '';
    }

    public function delete(): bool
    {
        return unlink($this->getPath());
    }

    public function close(): bool
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
        return true;
    }

    private function getPath(): string
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->key;
    }
}
