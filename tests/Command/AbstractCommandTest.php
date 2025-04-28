<?php
/**
 * This file is a part of the Digital Garden gotenberg bundle.
 */
namespace DigitalGarden\GotenbergBundle\Tests\Command;

use DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator;
use DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\TestContainer;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractCommandTest extends KernelTestCase
{

    /**
     * Container.
     *
     * @var TestContainer|null
     */
    protected ?ContainerInterface $container = null;

    /**
     * Pdf file generator.
     *
     * @var MockObject|PdfFileGenerator|null
     */
    protected MockObject|PdfFileGenerator|null $pdfFileGenerator = null;


    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        static::bootKernel();
        $this->container = static::getContainer();

        $this->pdfFileGenerator = $this->getMockBuilder(PdfFileGeneratorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->container->set('dgarden.gotenberg.pdf_file_generator', $this->pdfFileGenerator);
    }
}