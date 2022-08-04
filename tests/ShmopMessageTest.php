<?php

use PHPUnit\Framework\TestCase;
use Tapat4n\Fork\Message\ShmopMessage;

class ShmopMessageTest extends TestCase
{
    public function testWrite(): void
    {
        $string = 'ShmopMessageTest';
        $s_message = new ShmopMessage();
        $s_message->set($string);
        $this->assertSame($string, $s_message->get());
    }

    public function testWriteLength(): void
    {
        $string = str_pad('', ShmopMessage::SIZE + ShmopMessage::SIZE, '_');
        $s_message = new ShmopMessage();
        $s_message->set($string);
        $this->assertNotSame($string, $s_message->get());
    }

    public function testRead(): void
    {
        $s_message = new ShmopMessage();
        $this->assertSame('', $s_message->get());
    }

    public function testDelete(): void
    {
        $s_message = new ShmopMessage();
        $string = 'ShmopMessageTest';
        $s_message->set($string);
        $this->assertSame($string, $s_message->get());
        $this->assertTrue($s_message->remove());
        $this->assertSame('', $s_message->get());
    }
}
