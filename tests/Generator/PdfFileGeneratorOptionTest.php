<?php

namespace DigitalGarden\GotenbergBundle\Tests\Generator;

use DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorOptions;
use PHPUnit\Framework\TestCase;

/**
 * PdfFileGeneratorOptions test suite.
 */
class PdfFileGeneratorOptionTest extends TestCase
{
    /**
     * Test options array access.
     *
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorOptions
     *
     * @return void
     */
    public function testArrayAccess(): void
    {
        $options = new PdfFileGeneratorOptions();
        $this->assertFalse($options[PdfFileGeneratorOptions::OPTION_ASYNC]);
        $this->assertFalse(isset($options['test']));
        $options['test'] = 'Test';
        $this->assertTrue(isset($options['test']));
        $this->assertEquals('Test', $options['test']);
        unset($options['test']);
        $this->assertFalse(isset($options['test']));
    }
}