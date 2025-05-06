<?php

namespace DigitalGarden\GotenbergBundle\Tests\Event\Listener;

use DigitalGarden\GotenbergBundle\Event\Listener\GeneratedPdfCopyListener;
use DigitalGarden\GotenbergBundle\Event\PdfGeneratedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * GeneratedPdfCopyListener test suite.
 *
 * Most parts of the listener are indirectly tested by AsyncPdfGenerationActionTest.
 */
class GeneratedPdfCopyListenerTest extends KernelTestCase
{
    /**
     * Test when it is not a filesystem the event listener only log it.
     *
     * @covers \DigitalGarden\GotenbergBundle\Event\Listener\GeneratedPdfCopyListener
     * @covers \DigitalGarden\GotenbergBundle\Event\PdfGeneratedEvent::__construct
     *
     * @return void
     */
    public function testFilesystemNotSetWarning(): void
    {
        $logger = self::createMock(LoggerInterface::class);
        $listener = new GeneratedPdfCopyListener('/var/acme/pdf/files', logger: $logger);

        $logger->expects(self::once())->method('warning')->with('No filesystem available.');
        $listener(new PdfGeneratedEvent(new Request()));
    }
}