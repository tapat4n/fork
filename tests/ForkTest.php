<?php

use PHPUnit\Framework\TestCase;
use Tapat4n\Fork\Message\MessageInterface;

class ForkTest extends TestCase
{
    public function testAddWorker(): void
    {
        $fork = new Tapat4n\Fork\Fork();
        $ref = new ReflectionClass($fork);
        $this->assertTrue($ref->hasProperty('processes'));
        $processes = $ref->getProperty('processes');
        $processes->setAccessible(true);
        $this->assertSame(0, count($processes->getValue($fork)));

        $fork->addWorker(function (MessageInterface $message) {
            echo 'ok';
        });
        $this->assertSame(1, count($processes->getValue($fork)));
    }

    public function testRun(): void
    {
        $fork = new Tapat4n\Fork\Fork();
        $fork->addWorker(function (MessageInterface $message) {
            $message->set('run1');
        });
        $fork->addWorker(function (MessageInterface $message) {
            $message->set('run2');
        });
        $fork->dispatch();
        $this->assertSame(2, count($fork->getMessages()));
        $this->assertSame(['run1', 'run2'], array_values($fork->getMessages()));
    }
}
