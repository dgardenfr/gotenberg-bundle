<?php

namespace Command;

use DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorOptions;
use DigitalGarden\GotenbergBundle\Tests\Command\AbstractCommandTest;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class MergePdfCommandTest extends AbstractCommandTest
{
    /**
     * Test template generate command.
     * @covers \DigitalGarden\GotenbergBundle\Command\TemplatePdfGenerateCommand::configure
     * @covers \DigitalGarden\GotenbergBundle\Command\TemplatePdfGenerateCommand::generate
     * @covers \DigitalGarden\GotenbergBundle\Model\Command\AbstractPdfGenerationCommand::getNamedOptionValues
     * @covers \DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::configure
     * @covers \DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::loadExtension
     * @covers \DigitalGarden\GotenbergBundle\Model\Command\AbstractPdfGenerationCommand::configurePdfGenerationCommand
     * @covers \DigitalGarden\GotenbergBundle\Model\Command\AbstractPdfGenerationCommand::initialize
     * @covers \DigitalGarden\GotenbergBundle\Command\MergePdfCommand::__construct
     * @covers \DigitalGarden\GotenbergBundle\Command\MergePdfCommand::configure
     * @covers \DigitalGarden\GotenbergBundle\Command\MergePdfCommand::execute
     * @covers \DigitalGarden\GotenbergBundle\Command\MergePdfCommand::generate
     * @covers \DigitalGarden\GotenbergBundle\Command\MergePdfCommand::initialize
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorOptions::__construct
     * @covers \DigitalGarden\GotenbergBundle\Model\Command\AbstractPdfGenerationCommand::getOptions
     *
     * @return void
     */
    public function testMergeGenerationCommand(): void
    {
        $command = $this->container->get('dgarden.gotenberg.command.pdf_merge');;

        $this->pdfFileGenerator->expects($this->once())
            ->method('mergeWithOptions')
            ->with(new PdfFileGeneratorOptions(),'test.pdf', 'file1.pdf', 'file2.pdf')
            ->will($this->returnCallback(function () {
                file_put_contents('test.pdf', 'test');

                return new SplFileInfo('test.pdf');
            }));
        $output = new BufferedOutput();

        $result = $command->run(new ArrayInput([
            'files' => ['file1.pdf', 'file2.pdf', 'test.pdf']
        ]), $output);
        @unlink('test.pdf');

        $stdout = $output->fetch();
        $this->assertEquals(Command::SUCCESS, $result, $stdout);
        $this->assertEquals("The pdf file has been generated at test.pdf\n", $stdout);
    }
}