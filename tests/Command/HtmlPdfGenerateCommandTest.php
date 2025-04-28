<?php
/**
 * This file is a part of the Digital Garden gotenberg bundle.
 */

namespace DigitalGarden\GotenbergBundle\Tests\Command;

use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Test HTML generation command suite.
 */
class HtmlPdfGenerateCommandTest extends AbstractCommandTest
{
    /**
     * Test html generate command.
     *
     * @covers \DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::configure
     * @covers \DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::loadExtension
     * @covers \DigitalGarden\GotenbergBundle\Model\Command\AbstractPdfGenerationCommand::configurePdfGenerationCommand
     * @covers \DigitalGarden\GotenbergBundle\Model\Command\AbstractPdfGenerationCommand::initialize
     * @covers \DigitalGarden\GotenbergBundle\Command\HtmlPdfGenerateCommand::__construct
     * @covers \DigitalGarden\GotenbergBundle\Command\HtmlPdfGenerateCommand::configure
     * @covers \DigitalGarden\GotenbergBundle\Command\HtmlPdfGenerateCommand::execute
     * @covers \DigitalGarden\GotenbergBundle\Command\HtmlPdfGenerateCommand::generate
     *
     * @return void
     */
    public function testHtmlFileGenerationCommand(): void
    {
        $command = $this->container->get('dgarden.gotenberg.command.html_pdf_generate');

        $this->pdfFileGenerator->expects($this->once())
            ->method('htmlFile')
            ->with(__FILE__, 'test.pdf')
            ->will($this->returnCallback(function () {
                file_put_contents('test.pdf', 'test');

                return new SplFileInfo('test.pdf');
            }));
        $output = new BufferedOutput();
        $this->assertEquals(Command::SUCCESS, $command->run(new ArrayInput([
            'html' => __FILE__,
            'output_file' => 'test.pdf'
        ]), $output));
        unlink('test.pdf');

        $this->assertEquals("The pdf file has been generated at test.pdf\n", $output->fetch());
    }

    /**
     * Test html generate command.
     *
     * @covers \DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::configure
     * @covers \DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::loadExtension
     * @covers \DigitalGarden\GotenbergBundle\Model\Command\AbstractPdfGenerationCommand::configurePdfGenerationCommand
     * @covers \DigitalGarden\GotenbergBundle\Model\Command\AbstractPdfGenerationCommand::initialize
     * @covers \DigitalGarden\GotenbergBundle\Command\HtmlPdfGenerateCommand::__construct
     * @covers \DigitalGarden\GotenbergBundle\Command\HtmlPdfGenerateCommand::configure
     * @covers \DigitalGarden\GotenbergBundle\Command\HtmlPdfGenerateCommand::execute
     * @covers \DigitalGarden\GotenbergBundle\Command\HtmlPdfGenerateCommand::generate
     *
     * @return void
     */
    public function testHtmlGenerateCommand(): void
    {
        $command = $this->container->get('dgarden.gotenberg.command.html_pdf_generate');

        $this->pdfFileGenerator->expects($this->once())
            ->method('html')
            ->with('<h1>Test</h1>', 'test.pdf')
            ->will($this->returnCallback(function () {
                file_put_contents('test.pdf', 'test');

                return new SplFileInfo('test.pdf');
            }));
        $output = new BufferedOutput();
        $this->assertEquals(Command::SUCCESS, $command->run(new ArrayInput([
            'html' => '<h1>Test</h1>',
            'output_file' => 'test.pdf'
        ]), $output));
        unlink('test.pdf');

        $this->assertEquals("The pdf file has been generated at test.pdf\n", $output->fetch());
    }

    /**
     * Test html generate command with file error.
     *
     * @covers \DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::configure
     * @covers \DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::loadExtension
     * @covers \DigitalGarden\GotenbergBundle\Model\Command\AbstractPdfGenerationCommand::configurePdfGenerationCommand
     * @covers \DigitalGarden\GotenbergBundle\Model\Command\AbstractPdfGenerationCommand::initialize
     * @covers \DigitalGarden\GotenbergBundle\Command\HtmlPdfGenerateCommand::__construct
     * @covers \DigitalGarden\GotenbergBundle\Command\HtmlPdfGenerateCommand::configure
     * @covers \DigitalGarden\GotenbergBundle\Command\HtmlPdfGenerateCommand::execute
     * @covers \DigitalGarden\GotenbergBundle\Command\HtmlPdfGenerateCommand::generate
     *
     * @return void
     */
    public function testHtmlGenerateWithFileErrorCommand(): void
    {
        $command = $this->container->get('dgarden.gotenberg.command.html_pdf_generate');

        if (file_exists('test.pdf')) {
            unlink('test.pdf');
        }

        $this->pdfFileGenerator->expects($this->once())
            ->method('html')
            ->with('<h1>Test</h1>', 'test.pdf')
            ->will($this->returnCallback(function () {
                // File not created.
                return new SplFileInfo('test.pdf');
            }));

        $output = new BufferedOutput();
        $this->assertEquals(Command::FAILURE, $command->run(new ArrayInput([
            'html' => '<h1>Test</h1>',
            'output_file' => 'test.pdf'
        ]), $output));

        $this->assertEquals("Error while generated the pdf at test.pdf, file not readable.\n", $output->fetch());
    }
}