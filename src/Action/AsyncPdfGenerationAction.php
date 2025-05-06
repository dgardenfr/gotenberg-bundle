<?php

namespace DigitalGarden\GotenbergBundle\Action;


use DigitalGarden\GotenbergBundle\Event\PdfGeneratedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class AsyncPdfGenerationAction extends AbstractController
{
    public function __construct(
        private ?EventDispatcherInterface $eventDispatcher = null,
        private ?LoggerInterface $logger = null,
    )
    {

    }

    /**
     * Save the pdf file according to configuration.
     *
     * @param Request $request The request sent by gotenberg.
     *
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        if (null === $this->eventDispatcher) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Event dispatcher not set',
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($request->headers->get('Content-Type') !== 'application/pdf') {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid content type',
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->logger?->info('PDF generation received.');

        $this->eventDispatcher->dispatch(
            new PdfGeneratedEvent($request),
        );

        return new JsonResponse([
            'success' => true,
            'message' => null,
        ]);
    }
}