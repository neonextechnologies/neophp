<?php

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testBasicTest()
    {
        $this->assertTrue(true);
    }

    public function testApplicationInstance()
    {
        $this->assertInstanceOf(
            \NeoPhp\Core\Application::class,
            app()
        );
    }
}
