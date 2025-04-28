<?php
/**
 * This file is a part of the Digital Garden gotenberg bundle.
 */
namespace DigitalGarden\GotenbergBundle\Tests\Generator;

use DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator;
use DigitalGarden\GotenbergBundle\Tests\Kernel;
use Hoa\Iterator\Mock;
use PHPUnit\Framework\MockObject\MockObject;
use Sensiolabs\GotenbergBundle\Builder\GotenbergFileResult;
use Sensiolabs\GotenbergBundle\Builder\Screenshot\AbstractChromiumScreenshotBuilder;
use Sensiolabs\GotenbergBundle\Builder\Screenshot\HtmlScreenshotBuilder;
use Sensiolabs\GotenbergBundle\Builder\Screenshot\ScreenshotBuilderInterface;
use Sensiolabs\GotenbergBundle\GotenbergScreenshotInterface;
use Sensiolabs\GotenbergBundle\Processor\FileProcessor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\TestContainer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * PdfFileGenerator service test suite.
 */
class PdfFileGeneratorTest extends KernelTestCase
{
    /**
     * Gotenberg screenshot builder.
     *
     * @var AbstractChromiumScreenshotBuilder|MockObject|null
     */
    private AbstractChromiumScreenshotBuilder|MockObject|null $builder = null;

    /**
     * Test container.
     *
     * @var TestContainer|null
     */
    private ContainerInterface|null $container = null;

    /**
     * PdfFileGenerator service.
     * 
     * @var PdfFileGenerator
     */
    private ?PdfFileGenerator $pdfFileGenerator = null;

    /**
     * Gotenberg file result.
     *
     * @var GotenbergFileResult|MockObject|null
     */
    private GotenbergFileResult|MockObject|null $result = null;

    /**
     * Gotenberg screenshot service.
     *
     * @var GotenbergScreenshotInterface|MockObject|null
     */
    private GotenbergScreenshotInterface|MockObject|null $screenshot = null;

    /**
     * Test html generation.
     *
     * @covers \DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::configure
     * @covers \DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::loadExtension
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::__construct
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::getProcessor
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::html
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::htmlFile
     *
     * @return void
     */
    public function testHtmlGeneration(): void
    {
        self::bootKernel();

        $this->container = static::getContainer();

        $tmpfile = null;
        $html = '<h1>Test</h1>';
        $output = 'test.pdf';

        $this->screenshot->expects(self::once())->method('html');
        $this->builder->expects(self::once())
            ->method('contentFile')
            ->will($this->returnCallback(
                function ($file) use (&$tmpfile, $html) {
                    $tmpfile = $file;
                    $this->assertStringEqualsFile($tmpfile, $html);
                    return $this->builder;
                }
            ));
        $this->builder->expects(self::once())
            ->method('fileName')
            ->with('test.pdf');
        $this->builder->expects(self::once())->method('processor')->will($this->returnCallback(
            function (FileProcessor $processor) {
                $this->assertEquals('.', (new \ReflectionProperty($processor, 'directory'))->getValue($processor));
            }
        ));
        $this->builder->expects(self::once())
            ->method('generate')
            ->will($this->returnCallback(
                function () use (&$tmpfile, $output, $html) {
                    $this->assertStringEqualsFile($tmpfile, $html);
                    file_put_contents($output, 'test');

                    return $this->result;
                }
            ));
        $this->result->expects(self::once())->method('process');

        $file = $this->pdfFileGenerator->html($html, $output);
        $this->assertEquals($output, $file->getFilename());
        $this->assertEquals('test', $file->openFile()->fread(10));
        unlink($file->getPathname());
    }

    /**
     * Test template generation.
     *
     * @covers \DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::configure
     * @covers \DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::loadExtension
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::__construct
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::getProcessor
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::template
     *
     * @return void
     */
    public function testTemplateGeneration(): void
    {
        $this->screenshot->expects(self::once())->method('html');
        $this->builder->expects(self::once())->method('content')->with('test.html.twig', ['name' => 'John']);
        $this->builder->expects(self::once())->method('fileName')->with('test.pdf');
        $this->builder->expects(self::once())->method('processor')->will($this->returnCallback(
            function (FileProcessor $processor) {
                $this->assertEquals('.', (new \ReflectionProperty($processor, 'directory'))->getValue($processor));
            }
        ));
        $this->builder->expects(self::once())->method('generate')->will($this->returnCallback(
            function () {
                file_put_contents('test.pdf', 'test');

                return $this->result;
            }
        ));
        $this->result->expects(self::once())->method('process');

        $file = $this->pdfFileGenerator->template('test.html.twig', 'test.pdf', ['name' => 'John']);
        $this->assertEquals('test.pdf', $file->getFilename());
        $this->assertEquals('test', $file->openFile()->fread(10));
        unlink($file->getPathname());
    }

    /**
     * Test url generation.
     *
     * @covers \DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::configure
     * @covers \DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::loadExtension
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::__construct
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::getProcessor
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::url
     *
     * @return void
     */
    public function testUrlGeneration(): void
    {
        $this->screenshot->expects(self::once())->method('url');
        $this->builder->expects(self::once())->method('url')->with('https://www.google.fr');
        $this->builder->expects(self::once())->method('fileName')->with('test.pdf');
        $this->builder->expects(self::once())->method('processor')->will($this->returnCallback(
            function (FileProcessor $processor) {
                $this->assertEquals('.', (new \ReflectionProperty($processor, 'directory'))->getValue($processor));
            }
        ));
        $this->builder->expects(self::once())->method('generate')->will($this->returnCallback(
            function () {
                file_put_contents('test.pdf', 'test');

                return $this->result;
            }
        ));
        $this->result->expects(self::once())->method('process');

        $file = $this->pdfFileGenerator->url('https://www.google.fr', 'test.pdf');
        $this->assertEquals('test.pdf', $file->getFilename());
        $this->assertEquals('test', $file->openFile()->fread(10));
        unlink($file->getPathname());
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $this->container = static::getContainer();

        $this->screenshot = static::getMockBuilder(GotenbergScreenshotInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->builder = static::getMockBuilder(AbstractChromiumScreenshotBuilder::class)
            ->addMethods([
                'content',
                'contentFile',
                'url',
            ])
            ->onlyMethods([
                'fileName',
                'generate',
                'getEndpoint',
                'processor',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->result = static::createMock(GotenbergFileResult::class);

        $this->screenshot->method('html')->willReturn($this->builder);
        $this->screenshot->method('url')->willReturn($this->builder);
        $this->builder->method('content')->willReturn($this->builder);
        $this->builder->method('contentFile')->willReturn($this->builder);
        $this->builder->method('fileName')->willReturn($this->builder);
        $this->builder->method('generate')->willReturn($this->result);
        $this->builder->method('processor')->willReturn($this->builder);
        $this->builder->method('url')->willReturn($this->builder);

        $this->container->set(GotenbergScreenshotInterface::class, $this->screenshot);

        $this->pdfFileGenerator = $this->container->get('dgarden.gotenberg.pdf_file_generator');
    }
}