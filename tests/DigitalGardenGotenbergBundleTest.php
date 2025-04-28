<?php

namespace DigitalGarden\GotenbergBundle\Tests;

use DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle;
use DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * DigitalGardenGotenbergBundle test suite.
 */
class DigitalGardenGotenbergBundleTest extends TestCase
{
    /**
     * Test the bundle.
     *
     * @covers DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::configure
     * @covers DigitalGarden\GotenbergBundle\DigitalGardenGotenbergBundle::loadExtension
     *
     * @return void
     */
    public function testBundle(): void
    {
        $bundle = new DigitalGardenGotenbergBundle();
        $tree = new ArrayNodeDefinition('dgarden');
        $services = [
            'dgarden.gotenberg.pdf_file_generator',
            'dgarden.gotenberg.command.html_pdf_generate',
            'dgarden.gotenberg.command.template_pdf_generate',
            'dgarden.gotenberg.command.url_pdf_generate',
            'dgarden.gotenberg.generator',
            PdfFileGeneratorInterface::class,
        ];

        $definition = self::getMockBuilder(DefinitionConfigurator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['rootNode'])
            ->getMock();
        $definition
            ->method('rootNode')
            ->will(self::returnValue($tree));

        $bundle->configure($definition);
        $this->assertCount(1, $tree->getChildNodeDefinitions());
        $this->assertArrayHasKey('gotenberg', $tree->getChildNodeDefinitions());
        $gotenberg = $tree->getChildNodeDefinitions()['gotenberg'];
        $this->assertCount(0, $gotenberg->getChildNodeDefinitions());

        $builder = new ContainerBuilder();
        $locator = new FileLocator(__DIR__);
        $loader = new PhpFileLoader($builder, $locator);
        $xmlLoader = new XmlFileLoader($builder, $locator);
        $loader->setResolver(
            new LoaderResolver([
                $xmlLoader,
            ])
        );
        $instances = [];
        $configurator = new ContainerConfigurator(
            $builder,
            $loader,
            $instances,
            __DIR__ . '/config',
            'test',
            'test'
        );

        $builder->setParameter('kernel.debug', false);
        (new FrameworkBundle())->build($builder);

        array_walk($services, fn($service) => $this->assertNotContains($service, $builder->getServiceIds()));
        $bundle->loadExtension([], $configurator, $builder);
        array_walk($services, fn($service) => $this->assertContains($service, $builder->getServiceIds()));
    }
}