<?php

namespace Tapat4n\Fork\Fork;

use Tapat4n\Fork\Message\FileMessage;
use Tapat4n\Fork\Worker\WorkerInterface;

interface ForkInterface
{
    public const DEFAULT_LIMIT = 10;

    public function __construct(
        int $fork_limit = self::DEFAULT_LIMIT,
        bool $waitAfterRun = true,
        string $messageClass = FileMessage::class
    );

    public function addWorker(callable|WorkerInterface $worker): void;

    public function dispatch(): void;

    public function getOutput(): array;

    public function getMessages(): array;
}
