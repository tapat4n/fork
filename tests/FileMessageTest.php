<?php

use PHPUnit\Framework\TestCase;
use Tapat4n\Fork\Message\FileMessage;

class FileMessageTest extends TestCase
{
    public function testWrite(): void
    {
        $string = 'FileMessageTest';
        $s_message = new FileMessage();
        $s_message->set($string);
        $this->assertSame($string, $s_message->get());
    }

    public function testWriteLength(): void
    {
        $string = str_pad('', FileMessage::SIZE + FileMessage::SIZE, '_');
        $s_message = new FileMessage();
        $s_message->set($string);
        $this->assertNotSame($string, $s_message->get());
    }

    public function testRead(): void
    {
        $s_message = new FileMessage();
        $this->assertSame('', $s_message->get());
    }

    public function testDelete(): void
    {
        $s_message = new FileMessage();
        $string = 'FileMessageTest';
        $s_message->set($string);
        $this->assertSame($string, $s_message->get());
        $this->assertTrue($s_message->remove());
        $this->assertSame('', $s_message->get());
    }
}
