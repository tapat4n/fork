<?php

namespace Tapat4n\Fork\Worker;

use Tapat4n\Fork\Message\MessageInterface;

class CallbackWorker implements WorkerInterface
{
    private $worker;

    public function __construct(callable $worker)
    {
        $this->worker = $worker;
    }

    public function run(MessageInterface $message)
    {
        $w = $this->worker;
        return $w($message);
    }
}
