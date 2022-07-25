<?php

namespace Tapat4n\Fork;

use Tapat4n\Fork\Message\MessageInterface;

interface WorkerInterface
{
    public function run(MessageInterface $message);
}
