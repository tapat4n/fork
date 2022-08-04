<?php

use PHPUnit\Framework\TestCase;
use Tapat4n\Fork\Fork\PcntlFork as Fork;
use Tapat4n\Fork\Message\MessageInterface;

class ForkTest extends TestCase
{
    public function testAddWorker(): void
    {
        $fork = new Fork();
        $ref = new ReflectionClass($fork);
        $this->assertTrue($ref->hasProperty('processes'));
        $processes = $ref->getProperty('processes');
        $processes->setAccessible(true);
        $this->assertCount(0, $processes->getValue($fork));

        $fork->addWorker(function (MessageInterface $message) {
            echo 'ok';
        });
        $this->assertCount(1, $processes->getValue($fork));
    }

    public function testRun(): void
    {
        $fork = new Fork();
        $fork->addWorker(function (MessageInterface $message) {
            $message->set('run1');
            echo 'echo1';
        });
        $fork->addWorker(function (MessageInterface $message) {
            $message->set('run2');
            echo 'echo2';
        });
        $fork->dispatch();
        $this->assertCount(2, $fork->getMessages());
        $this->assertSame(['run1', 'run2'], array_values($fork->getMessages()));
        $this->assertSame(['echo1', 'echo2'], array_values($fork->getOutput()));
    }

    public function testForkFork(): void
    {
        $main_fork = new Fork();
        $main_fork->addWorker(function (MessageInterface $message) {
            $sub_fork_1 = new Fork();
            $sub_fork_1->addWorker(function (MessageInterface $message) {
                $message->set('sub_fork_1_1');
            });
            $sub_fork_1->addWorker(function (MessageInterface $message) {
                $message->set('sub_fork_1_2');
            });
            $sub_fork_1->dispatch();
            $message->set('main_fork_1|' . implode('|', $sub_fork_1->getMessages()));
        });
        $main_fork->addWorker(function (MessageInterface $message) {
            $sub_fork_2 = new Fork();
            $sub_fork_2->addWorker(function (MessageInterface $message) {
                $message->set('sub_fork_2_1');
            });
            $sub_fork_2->addWorker(function (MessageInterface $message) {
                $message->set('sub_fork_2_2');
            });
            $sub_fork_2->dispatch();
            $message->set('main_fork_2|' . implode('|', $sub_fork_2->getMessages()));
        });
        $main_fork->dispatch();
        $this->assertCount(2, $main_fork->getMessages());
        $this->assertSame(
            [
                'main_fork_1|sub_fork_1_1|sub_fork_1_2',
                'main_fork_2|sub_fork_2_1|sub_fork_2_2'
            ],
            array_values($main_fork->getMessages())
        );
    }

    public function testLimit(): void
    {
        $fork = new Fork(1);
        $fork->addWorker(function (MessageInterface $message) {
            $message->set('run1');
            echo 'echo1';
        });
        $fork->addWorker(function (MessageInterface $message) {
            $message->set('run2');
            echo 'echo2';
        });
        $fork->dispatch();
        $this->assertCount(2, $fork->getMessages());
        $this->assertSame(['run1', 'run2'], array_values($fork->getMessages()));
        $this->assertSame(['echo1', 'echo2'], array_values($fork->getOutput()));
    }
}
