<?php

namespace Tapat4n\Fork;

use Closure;
use Exception;
use ReflectionFunction;
use RuntimeException;
use Tapat4n\Fork\Fork\PcntlProcess;
use Tapat4n\Fork\Fork\ProcessForkInterface;
use Tapat4n\Fork\Message\MessageInterface;
use Tapat4n\Fork\Message\ShmopMessage;
use Tapat4n\Fork\Worker\CallbackWorker;
use Tapat4n\Fork\Worker\WorkerInterface;

final class ForkManager
{
    private bool $isParent = true;

    private string $forkClass;

    private string $messageClass;

    /**
     * @var WorkerInterface[]
     */
    private array $workers = [];

    /**
     * @var ProcessForkInterface[]
     */
    private array $forks = [];

    /**
     * @var MessageInterface[]
     */
    private array $messages = [];

    /**
     * @var MessageInterface[]
     */
    private array $output_messages = [];

    private KeyGenerator $keyGenerator;

    public function __construct(
        string $forkClass = PcntlProcess::class,
        string $messageClass = ShmopMessage::class,
    )
    {
        $this->forkClass = $forkClass;
        $this->messageClass = $messageClass;
        $this->keyGenerator = new KeyGenerator();
    }

    public function __destruct()
    {
        if ($this->isParent) {
            foreach ($this->forks as $fork) {
                $fork->destroy();
            }
        }
    }

    /**
     * @throws Exception
     */
    public function addWorker(WorkerInterface|Closure $worker): void
    {
        if (is_callable($worker)) {
            $this->validateClosure($worker);
            $worker = new CallbackWorker($worker);
        }
        $id = $this->keyGenerator->generateId();
        $this->workers[$id] = $worker;
        $this->messages[$id] = $this->createMessage();
        $this->output_messages[$id] = $this->createMessage();

        $this->forks[$id] = $this->createForkClass(
            $worker,
            $this->messages[$id],
            $this->output_messages[$id],
            function (ProcessForkInterface $fork) {
                if ($fork->isSub()) {
                    $this->isParent = false;
                }
            }
        );
    }

    public function dispatch(): void
    {
        foreach ($this->forks as $fork) {
            $fork->run();
        }
        foreach ($this->forks as $fork) {
            if ($fork->isRunned()) {
                $fork->wait();
            }
        }
    }

    /**
     * @return MessageInterface[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return MessageInterface[]
     */
    public function getOutputMessages(): array
    {
        return $this->output_messages;
    }

    /**
     * @throws \ReflectionException
     */
    private function validateClosure(Closure $worker)
    {
        $variables = (new ReflectionFunction($worker))->getClosureUsedVariables();
        foreach ($variables as $name => $variable) {
            if (spl_object_id($variable) === spl_object_id($this)) {
                throw new RuntimeException('Parameter $' . $name . ' can`t equal $this');
            }
        }
    }

    private function createMessage(): MessageInterface
    {
        if (!class_exists($this->messageClass)) {
            throw new RuntimeException('Class ' . $this->messageClass . ' not exists');
        }
        $message = new $this->messageClass();
        if (!$message instanceof MessageInterface) {
            throw new RuntimeException('Property $messageClass must be instance of ' . MessageInterface::class);
        }
        return $message;
    }

    private function createForkClass(
        WorkerInterface  $worker,
        MessageInterface $message,
        MessageInterface $outputMessage,
        callable|null    $on_forked = null,
    ): ProcessForkInterface
    {
        if (!class_exists($this->forkClass)) {
            throw new RuntimeException('Class ' . $this->forkClass . ' not exists');
        }
        $fork = new $this->forkClass(
            $worker,
            $message,
            $outputMessage,
            $on_forked,
        );
        if (!$fork instanceof ProcessForkInterface) {
            throw new RuntimeException('Property $forkClass must be instance of ' . ProcessForkInterface::class);
        }
        return $fork;
    }
}
