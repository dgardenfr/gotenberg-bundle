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
 * Test url generation command suite.
 */
class UrlPdfGenerateCommandTest extends AbstractCommandTest
{
    /**
     * Test url generate command.
     * @covers \DigitalGarden\GotenbergBundle\Command\UrlPdfGenerateCommand::configure
     * @covers \DigitalGarden\GotenbergBundle\Command\UrlPdfGenerateCommand::generate
     * @covers \DigitalGarden\GotenbergBundle\Model\Command\AbstractPdfGenerationCommand::getNamedOptionValues
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
    public function testTemplateGenerationCommand(): void
    {
        $command = $this->container->get('dgarden.gotenberg.command.url_pdf_generate');;

        $this->pdfFileGenerator->expects($this->once())
            ->method('url')
            ->with('https://www.google.fr', 'test.pdf')
            ->will($this->returnCallback(function () {
                file_put_contents('test.pdf', 'test');

                return new SplFileInfo('test.pdf');
            }));
        $output = new BufferedOutput();
        $this->assertEquals(Command::SUCCESS, $command->run(new ArrayInput([
            'url' => 'https://www.google.fr',
            'output_file' => 'test.pdf',
        ]), $output));
        unlink('test.pdf');

        $this->assertEquals("The pdf file has been generated at test.pdf\n", $output->fetch());
    }
}