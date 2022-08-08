# Fork manager
[![Latest Stable Version](http://poser.pugx.org/tapat4n/fork/v)](https://packagist.org/packages/tapat4n/fork)
[![Total Downloads](http://poser.pugx.org/tapat4n/fork/downloads)](https://packagist.org/packages/tapat4n/fork)
[![Latest Unstable Version](http://poser.pugx.org/tapat4n/fork/v/unstable)](https://packagist.org/packages/tapat4n/fork)
[![License](http://poser.pugx.org/tapat4n/fork/license)](https://packagist.org/packages/tapat4n/fork)
[![PHP Version Require](http://poser.pugx.org/tapat4n/fork/require/php)](https://packagist.org/packages/tapat4n/fork)

## What is it?

PHP library for forking process and multitasking

- [Installation](#installation)
- [Example usage](#example-usage)

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
