<?php

namespace DigitalGarden\GotenbergBundle\Tests\Action;

use DigitalGarden\GotenbergBundle\Action\AsyncPdfGenerationAction;
use DigitalGarden\GotenbergBundle\Event\PdfGeneratedEvent;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test suite for the async pdf generation action.
 */
class AsyncPdfGenerationActionTest extends WebTestCase
{
    /**
     * Test the async pdf generation success request.
     *
     * @covers \DigitalGarden\GotenbergBundle\Action\AsyncPdfGenerationAction
     * @covers \DigitalGarden\GotenbergBundle\Event\Listener\GeneratedPdfCopyListener
     * @covers \DigitalGarden\GotenbergBundle\Event\PdfGeneratedEvent
     *
     * @return void
     */
    public function testAsyncPdfGeneration(): void
    {
        $client = self::createClient();

        $outdir = realpath(__DIR__ . '/../var/pdf');

        if (is_dir($outdir)) {
            $dir = opendir($outdir);
            while ($file = readdir($dir)) {
                if ($file != "." && $file != "..") {
                    unlink("$outdir/$file");
                }
            }
            closedir($dir);
            rmdir($outdir);
        }

        /** @var EventDispatcher $event_dispatcher */
        $event_dispatcher = self::getContainer()->get('event_dispatcher');

        $router = self::$kernel->getContainer()->get('router');
        $route = $router->getRouteCollection()->get('dgarden_gotenberg_async_pdf_generation');

        $event = null;
        $event_dispatcher->addListener(PdfGeneratedEvent::class, function (PdfGeneratedEvent $e) use (&$event) {
            $event = $e;
        }, 10);

        $client->request(
            'POST',
            $route->getPath(),
            server: [
                'CONTENT_TYPE' => 'application/pdf',
            ],
            content: 'Test',
        );
        $this->assertResponseIsSuccessful();
        $this->assertEquals('{"success":true,"message":null}', $client->getResponse()->getContent());

        $this->assertNotNull($event);
        $this->assertNotNull($event->request);
        $this->assertEquals('application/pdf', $event->request->headers->get('Content-Type'));
        $this->assertEquals('Test', $event->request->getContent());

        $this->assertNotNull($event->getFile());
        $this->assertFileExists($event->getFile()->getRealPath());
        $this->assertFileIsReadable($event->getFile()->getRealPath());
        $this->assertStringEqualsFile($event->getFile()->getRealPath(), 'Test');
        unlink($event->getFile()->getRealPath());
    }

    /**
     * Test the async pdf generation success request.
     *
     * @covers \DigitalGarden\GotenbergBundle\Action\AsyncPdfGenerationAction
     *
     * @return void
     */
    public function testNoEventDispatcher(): void
    {
        $action = new AsyncPdfGenerationAction();
        $response = $action(new Request());

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('{"success":false,"message":"Event dispatcher not set"}', $response->getContent());
    }

    /**
     * Test bad request.
     *
     * @covers \DigitalGarden\GotenbergBundle\Action\AsyncPdfGenerationAction
     *
     * @return void
     */
    public function testInvalidRequest(): void
    {
        $client = self::createClient();
        $router = self::$kernel->getContainer()->get('router');
        $route = $router->getRouteCollection()->get('dgarden_gotenberg_async_pdf_generation');

        $client->request(
            'POST',
            $route->getPath(),
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
            content: 'Test',
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertEquals('{"success":false,"message":"Invalid content type"}', $client->getResponse()->getContent());
    }
}