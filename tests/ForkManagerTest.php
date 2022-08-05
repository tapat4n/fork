<?php

use PHPUnit\Framework\TestCase;
use Tapat4n\Fork\ForkManager;
use Tapat4n\Fork\Message\MessageInterface;

class ForkManagerTest extends TestCase
{
    public function testAddWorker(): void
    {
        $fork = new ForkManager();
        $ref = new ReflectionClass($fork);
        $this->assertTrue($ref->hasProperty('forks'));
        $processes = $ref->getProperty('forks');
        $processes->setAccessible(true);
        $this->assertCount(0, $processes->getValue($fork));

        $fork->addWorker(function (MessageInterface $message) {
            echo 'ok';
        });
        $this->assertCount(1, $processes->getValue($fork));
    }

    public function testRun(): void
    {
        $fork = new ForkManager();
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
        $this->assertSame(['run1', 'run2'], array_values($fork->getMessagesContent()));
        $this->assertSame(['echo1', 'echo2'], array_values($fork->getOutputMessagesContent()));
    }

    public function testForkFork(): void
    {
        $main_fork = new ForkManager();
        $main_fork->addWorker(function (MessageInterface $message) {
            $sub_fork_1 = new ForkManager();
            $sub_fork_1->addWorker(function (MessageInterface $message) {
                $message->set('sub_fork_1_1');
            });
            $sub_fork_1->addWorker(function (MessageInterface $message) {
                $message->set('sub_fork_1_2');
            });
            $sub_fork_1->dispatch();
            $message->set('main_fork_1|' . implode('|', $sub_fork_1->getMessagesContent()));
        });
        $main_fork->addWorker(function (MessageInterface $message) {
            $sub_fork_2 = new ForkManager();
            $sub_fork_2->addWorker(function (MessageInterface $message) {
                $message->set('sub_fork_2_1');
            });
            $sub_fork_2->addWorker(function (MessageInterface $message) {
                $message->set('sub_fork_2_2');
            });
            $sub_fork_2->dispatch();
            $message->set('main_fork_2|' . implode('|', $sub_fork_2->getMessagesContent()));
        });
        $main_fork->dispatch();
        $this->assertCount(2, $main_fork->getMessages());
        $this->assertSame(
            [
                'main_fork_1|sub_fork_1_1|sub_fork_1_2',
                'main_fork_2|sub_fork_2_1|sub_fork_2_2'
            ],
            array_values($main_fork->getMessagesContent())
        );
    }

    public function testLimit(): void
    {
        $fork = new ForkManager();
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
        $this->assertSame(['run1', 'run2'], array_values($fork->getMessagesContent()));
        $this->assertSame(['echo1', 'echo2'], array_values($fork->getOutputMessagesContent()));
    }
}
