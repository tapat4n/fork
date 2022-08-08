# Fork manager

## What is it?

PHP library for forking process and multitasking

- [Installation](#installation)
- [Example usage](#example)

## Installation
PHP 8.1 is required

The [pcntl](http://php.net/pcntl) extension is required.

The [posix](http://php.net/posix) extension is required.

```bash
$ composer require tapat4n/fork
```

## Example usage
```php
use Tapat4n\Fork\ForkManager;
use Tapat4n\Fork\Message\MessageInterface;

$manager = new ForkManager();
$i = 0;

$manager->addWorker(function (MessageInterface $message) use ($i) {
    $message->set(++$i);
});

$manager->addWorker(function (MessageInterface $message) use ($i) {
    $content = '';
    while ($i < 1000) {
        $content .= $i;
        $i++;
    }
    $message->set($content);
}, true); // set true to detach process

$manager->dispatch();

var_dump($manager->getMessages());
var_dump($manager->getMessagesContent());

```
