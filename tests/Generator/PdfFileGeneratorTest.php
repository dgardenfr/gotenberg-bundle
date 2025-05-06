<?php
/**
 * This file is a part of the Digital Garden gotenberg bundle.
 */

namespace DigitalGarden\GotenbergBundle\Tests\Generator;

use DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator;
use DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorOptions;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionProperty;
use Sensiolabs\GotenbergBundle\Builder\GotenbergFileResult;
use Sensiolabs\GotenbergBundle\Builder\Pdf\AbstractPdfBuilder;
use Sensiolabs\GotenbergBundle\Builder\Pdf\HtmlPdfBuilder;
use Sensiolabs\GotenbergBundle\Builder\Pdf\PdfBuilderInterface;
use Sensiolabs\GotenbergBundle\Builder\Screenshot\AbstractChromiumScreenshotBuilder;
use Sensiolabs\GotenbergBundle\Builder\Screenshot\HtmlScreenshotBuilder;
use Sensiolabs\GotenbergBundle\GotenbergPdf;
use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;
use Sensiolabs\GotenbergBundle\GotenbergScreenshotInterface;
use Sensiolabs\GotenbergBundle\Processor\FileProcessor;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\TestContainer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * PdfFileGenerator service test suite.
 */
class PdfFileGeneratorTest extends KernelTestCase
{

    /**
     * Test container.
     *
     * @var TestContainer|null
     */
    private ContainerInterface|null $container = null;

    /**
     * Gotenberg pdf builder.
     *
     * @var MockObject<HtmlPdfBuilder>|null
     */
    private PdfBuilderInterface|MockObject|null $pdfBuilder = null;

    /**
     * Gotenberg pdf service.
     *
     * @var MockObject<GotenbergPdf>|null
     */
    private MockObject|null $pdf = null;

    /**
     * PdfFileGenerator service.
     *
     * @var PdfFileGenerator
     */
    private ?PdfFileGenerator $pdfFileGenerator = null;

    /**
     * Gotenberg file result.
     *
     * @var MockObject<GotenbergFileResult>|null
     */
    private MockObject|null $result = null;

    /**
     * Symfony router.
     *
     * @var MockObject<RouterInterface>|null
     */
    private MockObject|null $router = null;

    /**
     * Gotenberg screenshot service.
     *
     * @var MockObject<GotenbergScreenshotInterface>|null
     */
    private MockObject|null $screenshot = null;

    /**
     * Gotenberg screenshot builder.
     *
     * @var MockObject<HtmlScreenshotBuilder>|null
     */
    private MockObject|null $screenshotBuilder = null;

    /**
     * Test html generation.
     *
     * @covers \DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::configure
     * @covers \DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::loadExtension
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::__construct
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::getProcessor
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::html
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::htmlFile
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::getOptions
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorOptions::__construct
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorOptions::offsetGet
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
        $this->screenshotBuilder->expects(self::once())
            ->method('contentFile')
            ->will($this->returnCallback(
                function ($file) use (&$tmpfile, $html) {
                    $tmpfile = $file;
                    $this->assertStringEqualsFile($tmpfile, $html);
                    return $this->screenshotBuilder;
                }
            ));
        $this->screenshotBuilder->expects(self::once())
            ->method('fileName')
            ->with('test.pdf');
        $this->screenshotBuilder->expects(self::once())->method('processor')->will($this->returnCallback(
            function (FileProcessor $processor) {
                $this->assertEquals(
                    realpath(__DIR__ . '/../..'),
                    (new ReflectionProperty($processor, 'directory'))->getValue($processor)
                );
            }
        ));
        $this->screenshotBuilder->expects(self::once())
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
     * Test html async generation.
     *
     * @covers \DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::configure
     * @covers \DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::loadExtension
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::__construct
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::getProcessor
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::html
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::htmlFile
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::getOptions
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorOptions::__construct
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorOptions::offsetGet
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::async
     *
     * @return void
     */
    public function testHtmlAsyncGeneration(): void
    {
        self::bootKernel();

        $this->container = static::getContainer();

        $tmpfile = null;
        $html = '<h1>Test</h1>';
        $output = 'test.pdf';

        $this->router->expects(self::once())->method('generate')->will($this->returnCallback(function ($route) {
            $this->assertEquals('dgarden_gotenberg_async_pdf_generation', $route);
            return '/_dg/pdf/generate';
        }));
        $this->screenshot->expects(self::once())->method('html');
        $this->screenshotBuilder->expects(self::once())
            ->method('contentFile')
            ->will($this->returnCallback(
                function ($file) use (&$tmpfile, $html) {
                    $tmpfile = $file;
                    $this->assertStringEqualsFile($tmpfile, $html);
                    return $this->screenshotBuilder;
                }
            ));
        $this->screenshotBuilder->expects(self::once())->method('fileName')->with('test.pdf');
        $this->screenshotBuilder->expects(self::once())->method('processor')->will($this->returnCallback(
            function (FileProcessor $processor) {
                $this->assertEquals(
                    realpath(__DIR__ . '/../..'),
                    (new ReflectionProperty($processor, 'directory'))->getValue($processor)
                );
            }
        ));
        $this->screenshotBuilder->expects(self::once())->method('generate')
            ->will($this->returnCallback(
                function () use (&$tmpfile, $output, $html) {
                    $this->assertStringEqualsFile($tmpfile, $html);
                    file_put_contents($output, 'test');

                    return $this->result;
                }
            ));
        $this->screenshotBuilder->expects(self::once())->method('webhookUrl');
        $this->screenshotBuilder->expects(self::once())->method('generateAsync');
        $this->result->expects(self::once())->method('process');

        $file = $this->pdfFileGenerator->html($html, $output, [PdfFileGeneratorOptions::OPTION_ASYNC => true]);
        $this->assertEquals($output, $file->getFilename());
        $this->assertEquals('test', $file->openFile()->fread(10));
        unlink($file->getPathname());
    }


    /**
     * Test html file generation.
     *
     * @covers \DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::configure
     * @covers \DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::loadExtension
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::__construct
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::getProcessor
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::htmlFile
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::getOptions
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorOptions::__construct
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorOptions::offsetGet
     *
     * @return void
     */
    public function testHtmlFileGeneration(): void
    {
        $output = 'test.pdf';
        $tmpfile = null;

        $this->screenshot->expects(self::once())->method('html');
        $this->screenshotBuilder->expects(self::once())
            ->method('contentFile')
            ->will($this->returnCallback(
                function ($file) use (&$tmpfile) {
                    $tmpfile = $file;
                    $this->assertStringEqualsFile($tmpfile, '<img src="test.jpg" />');
                    return $this->screenshotBuilder;
                }
            ));
        $this->screenshotBuilder->expects(self::once())
            ->method('fileName')
            ->with('test.pdf');
        $this->screenshotBuilder->expects(self::once())->method('processor')->will($this->returnCallback(
            function (FileProcessor $processor) {
                $this->assertEquals(
                    realpath(__DIR__ . '/../..'),
                    (new ReflectionProperty($processor, 'directory'))->getValue($processor)
                );
            }
        ));
        $this->screenshotBuilder->expects(self::once())
            ->method('generate')
            ->will($this->returnCallback(
                function () use (&$tmpfile, $output) {
                    $this->assertStringEqualsFile($tmpfile, '<img src="test.jpg" />');
                    file_put_contents($output, 'test');

                    return $this->result;
                }
            ));
        $this->result->expects(self::once())->method('process');

        file_put_contents('test.html', '<img src="test.jpg" />');
        file_put_contents('test.jpg', 'test');

        $this->screenshotBuilder->expects(self::once())
            ->method('addAsset')
            ->with((new SplFileInfo('test.jpg'))->getRealPath());
        $file = $this->pdfFileGenerator->htmlFile('test.html', $output);
        @unlink('test.html');
        @unlink('test.jpg');
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
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::getOptions
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorOptions::__construct
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorOptions::offsetGet
     *
     * @return void
     */
    public function testTemplateGeneration(): void
    {
        $this->screenshot->expects(self::once())->method('html');
        $this->screenshotBuilder->expects(self::once())->method('content')->with('test.html.twig', ['name' => 'John']);
        $this->screenshotBuilder->expects(self::once())->method('fileName')->with('test.pdf');
        $this->screenshotBuilder->expects(self::once())->method('processor')->will($this->returnCallback(
            function (FileProcessor $processor) {
                $this->assertEquals(
                    realpath(__DIR__ . '/../..'),
                    (new ReflectionProperty($processor, 'directory'))->getValue($processor)
                );
            }
        ));
        $this->screenshotBuilder->expects(self::once())->method('generate')->will($this->returnCallback(
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
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::getOptions
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorOptions::__construct
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorOptions::offsetGet
     *
     * @return void
     */
    public function testUrlGeneration(): void
    {
        $this->screenshot->expects(self::once())->method('url');
        $this->screenshotBuilder->expects(self::once())->method('url')->with('https://www.google.fr');
        $this->screenshotBuilder->expects(self::once())->method('fileName')->with('test.pdf');
        $this->screenshotBuilder->expects(self::once())->method('processor')->will($this->returnCallback(
            function (FileProcessor $processor) {
                $this->assertEquals(
                    realpath(__DIR__ . '/../..'),
                    (new ReflectionProperty($processor, 'directory'))->getValue($processor)
                );
            }
        ));
        $this->screenshotBuilder->expects(self::once())->method('generate')->will($this->returnCallback(
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
     * Test merge generation.
     *
     * @covers \DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::configure
     * @covers \DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::loadExtension
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::__construct
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::getProcessor
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::merge
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::mergeWithOptions
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator::getOptions
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorOptions::__construct
     * @covers \DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorOptions::offsetGet
     *
     * @return void
     */
    public function testMergeGeneration(): void
    {
        /** @var PdfFileGenerator $generator */
        $generator = $this->pdfFileGenerator;

        !is_dir('var') && mkdir('var', 0777, true);
        touch('var/file1.pdf');
        touch('var/file2.pdf');
        $info1 = new SplFileInfo('var/file1.pdf');
        $info2 = new SplFileInfo('var/file2.pdf');

        $this->pdf->expects(self::once())->method('merge');
        $this->pdfBuilder->expects(self::once())->method('fileName')->with('test.pdf');
        $this->pdfBuilder->expects(self::once())->method('processor')->will($this->returnCallback(
            function (FileProcessor $processor) {
                $this->assertEquals(
                    realpath(__DIR__ . '/../../var'),
                    (new ReflectionProperty($processor, 'directory'))->getValue($processor)
                );
            }
        ));
        $this->pdfBuilder->expects(self::once())->method('generate')->will($this->returnCallback(
            function () {
                file_put_contents('var/test.pdf', 'test');

                return $this->result;
            }
        ));
        $this->pdfBuilder->expects(self::once())->method('files')->with($info1->getRealPath(), $info2->getRealPath());

        $file = $generator->merge('var/test.pdf', 'var/file1.pdf', 'var/file2.pdf');
        @unlink('var/file1.pdf');
        @unlink('var/file2.pdf');
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

        $this->router = static::getMockBuilder(RouterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->screenshot = static::getMockBuilder(GotenbergScreenshotInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->result = static::createMock(GotenbergFileResult::class);
        $this->pdf = static::getMockBuilder(GotenbergPdfInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->screenshotBuilder = static::getMockBuilder(AbstractChromiumScreenshotBuilder::class)
            ->addMethods([
                'content',
                'contentFile',
                'url',
            ])
            ->onlyMethods([
                'addAsset',
                'fileName',
                'generate',
                'generateAsync',
                'getEndpoint',
                'processor',
                'webhookUrl',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->pdfBuilder = static::getMockBuilder(AbstractPdfBuilder::class)
            ->addMethods([
                'files',
            ])
            ->onlyMethods([
                'fileName',
                'generate',
                'generateAsync',
                'getEndpoint',
                'processor',
                'setConfigurations',
                'webhookUrl',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->screenshot->method('html')->willReturn($this->screenshotBuilder);
        $this->screenshot->method('url')->willReturn($this->screenshotBuilder);
        $this->pdf->method('merge')->willReturn($this->pdfBuilder);

        $this->screenshotBuilder->method('addAsset')->willReturn($this->screenshotBuilder);
        $this->screenshotBuilder->method('content')->willReturn($this->screenshotBuilder);
        $this->screenshotBuilder->method('contentFile')->willReturn($this->screenshotBuilder);
        $this->screenshotBuilder->method('fileName')->willReturn($this->screenshotBuilder);
        $this->screenshotBuilder->method('generate')->willReturn($this->result);
        $this->screenshotBuilder->method('getEndpoint')->willReturn('/forms/chromium/screenshot/html');
        $this->screenshotBuilder->method('processor')->willReturn($this->screenshotBuilder);
        $this->screenshotBuilder->method('url')->willReturn($this->screenshotBuilder);
        $this->screenshotBuilder->method('webhookUrl')->willReturn($this->screenshotBuilder);

        $this->pdfBuilder->method('fileName')->willReturn($this->pdfBuilder);
        $this->pdfBuilder->method('processor')->willReturn($this->pdfBuilder);
        $this->pdfBuilder->method('files')->willReturn($this->pdfBuilder);
        $this->pdfBuilder->method('generate')->willReturn($this->result);
        $this->pdfBuilder->method('processor')->willReturn($this->pdfBuilder);
        $this->pdfBuilder->method('webhookUrl')->willReturn($this->pdfBuilder);

        $this->container->set(GotenbergScreenshotInterface::class, $this->screenshot);
        $this->container->set(GotenbergPdfInterface::class, $this->pdf);
        $this->container->set('router', $this->router);

        $this->pdfFileGenerator = $this->container->get('dgarden.gotenberg.pdf_file_generator');
    }
}