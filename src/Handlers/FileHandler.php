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

final class FileHandler implements HandlerInterface
{
    private int $key;
    private int $size;

    /**
     * @var resource
     * @psalm-param closed-resource|resource
     */
    private $stream;

    public function __construct(int $key, int $size)
    {
        $this->key = $key;
        $this->size = $size;
        $this->stream = fopen($this->getPath(), 'w+b');
        if (!$this->stream) {
            throw new \RuntimeException('Can`t open stream for file');
        }
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
        $fsize = filesize($this->getPath());
        if ($fsize > 0) {
            fseek($this->stream, 0);
            return fread($this->stream, $fsize);
        }
        return '';
    }

    public function delete(): bool
    {
        if (!file_exists($this->getPath())) {
            return true;
        }
        return unlink($this->getPath());
    }

    public function close(): bool
    {
        fclose($this->stream);
        return true;
    }

    private function getPath(): string
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->key;
    }
}
