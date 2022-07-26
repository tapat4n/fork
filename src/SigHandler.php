<?php

namespace Tapat4n\Fork;

use RuntimeException;

final class SigHandler
{
    private array $handlers = [];

    private bool $async_signals = true;

    public function __construct(bool $async_signals = true)
    {
        if (!extension_loaded('pcntl')) {
            throw new RuntimeException('Extension pcntl not loaded');
        }

        $this->async_signals = $async_signals;
        $this->asyncSignals();
    }

    public function __invoke($signal): void
    {
        $this->handle($signal);
    }

    public function registerWithCallback(int $signal, callable $handler): void
    {
        $this->register($signal);
        $this->subscribe($signal, $handler);
    }

    public function registerFromArray(array $data): void
    {
        foreach ($data as $signal => $callback) {
            $this->registerWithCallback($signal, $callback);
        }
    }

    private function register(int $signal): void
    {
        pcntl_signal($signal, $this);
    }

    private function handle(int $signal): void
    {
        if (!empty($this->handlers[$signal])) {
            foreach ($this->handlers[$signal] as $signalHandler) {
                $signalHandler($signal);
                $this->unsubscribe($signal, $signalHandler);
            }
        }
    }

    private function subscribe($sigNumber, $handler): void
    {
        $this->handlers[$sigNumber][$this->getFunctionHash($handler)] = $handler;
    }

    private function unsubscribe($signal, $handler): void
    {
        unset($this->handlers[$signal][$this->getFunctionHash($handler)]);
    }

    private function asyncSignals(): void
    {
        pcntl_async_signals($this->async_signals);
    }

    private function getFunctionHash(object $callable): string
    {
        return spl_object_hash($callable);
    }
}
