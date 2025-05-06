<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use DigitalGarden\GotenbergBundle\Action\AsyncPdfGenerationAction;
use DigitalGarden\GotenbergBundle\Command\HtmlPdfGenerateCommand;
use DigitalGarden\GotenbergBundle\Command\MergePdfCommand;
use DigitalGarden\GotenbergBundle\Command\TemplatePdfGenerateCommand;
use DigitalGarden\GotenbergBundle\Command\UrlPdfGenerateCommand;
use DigitalGarden\GotenbergBundle\Event\Listener\GeneratedPdfCopyListener;
use DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator;
use DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Configure GotenbergBundle container.
 */
return function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    // PDF file generator.
    $services->set('dgarden.gotenberg.pdf_file_generator')
        ->class(PdfFileGenerator::class)
        ->args([
            service('sensiolabs_gotenberg.screenshot'),
            service('sensiolabs_gotenberg.pdf'),
            service('filesystem'),
            service('logger'),
        ]);

    $services->alias('dgarden.gotenberg.generator', 'dgarden.gotenberg.pdf_file_generator');
    $services->alias(PdfFileGeneratorInterface::class, 'dgarden.gotenberg.pdf_file_generator');

    // Controllers / Actions.
    if (class_exists(AbstractController::class)) {
        $services->set('dgarden.gotenberg.action.async_pdf_generation')
            ->class(AsyncPdfGenerationAction::class)
            ->args([
                service('event_dispatcher'),
                service('logger'),
            ])
            ->tag('controller.service_arguments')
            ->tag('container.service_subscriber');
    }

    // Event listeners.
    $services->set('dgarden.gotenberg.event.listener.generated_pdf_copy')
        ->class(GeneratedPdfCopyListener::class)
        ->args([
            param('dgarden.gotenberg.output_path'),
            service('filesystem'),
            service('logger'),
        ])
        ->tag('kernel.event_listener', ['priority' => -10]);

    // Commands.
    $services->set('dgarden.gotenberg.command.html_pdf_generate')
        ->class(HtmlPdfGenerateCommand::class)
        ->tag('console.command', ['command' => 'dgarden:pdf:html'])
        ->args([
            service('dgarden.gotenberg.pdf_file_generator'),
        ]);

    $services->set('dgarden.gotenberg.command.pdf_merge')
        ->class(MergePdfCommand::class)
        ->tag('console.command', ['command' => 'dgarden:pdf:merge'])
        ->args([
            service('dgarden.gotenberg.pdf_file_generator'),
        ]);

    $services->set('dgarden.gotenberg.command.template_pdf_generate')
        ->class(TemplatePdfGenerateCommand::class)
        ->tag('console.command', ['command' => 'dgarden:pdf:template'])
        ->args([
            service('dgarden.gotenberg.pdf_file_generator'),
        ]);

    $services->set('dgarden.gotenberg.command.url_pdf_generate')
        ->class(UrlPdfGenerateCommand::class)
        ->tag('console.command', ['command' => 'dgarden:pdf:url'])
        ->args([
            service('dgarden.gotenberg.pdf_file_generator'),
        ]);
};