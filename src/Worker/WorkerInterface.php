<?php

namespace Tapat4n\Fork\Worker;

use Tapat4n\Fork\Message\MessageInterface;

interface WorkerInterface
{
    public function run(MessageInterface $message);
}
