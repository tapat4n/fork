<?php

namespace Tapat4n\Fork\Fork;

use Tapat4n\Fork\Message\MessageInterface;
use Tapat4n\Fork\Worker\WorkerInterface;

interface ProcessForkInterface
{
    public function __construct(
        WorkerInterface $worker,
        MessageInterface $message,
        MessageInterface $outputMessage,
        callable|null $on_forked = null,
    );

    public function getPid(): ?int;

    public function isRunned(): bool;

    public function isRunning(): bool;

    public function isSuccessful(): bool;

    public function isSub(): bool;

    public function isParent(): bool;

    public function run(): void;

    public function wait(): void;

    public function destroy(): void;
}
