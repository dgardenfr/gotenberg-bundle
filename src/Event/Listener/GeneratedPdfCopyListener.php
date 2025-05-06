<?php

namespace DigitalGarden\GotenbergBundle\Event\Listener;

use DigitalGarden\GotenbergBundle\Event\PdfGeneratedEvent;
use Psr\Log\LoggerInterface;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Event listener copying the PDF file.
 *
 * This can be bypassed by stopping event propagation.
 */
readonly class GeneratedPdfCopyListener
{
    public function __construct(
        private string $path,
        private ?Filesystem $filesystem = null,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function __invoke(PdfGeneratedEvent $event): void
    {
        if (null === $this->filesystem) {
            $this->logger?->warning('No filesystem available.');
            return;
        }

        if (!$this->filesystem->exists($this->path)) {
            $this->filesystem->mkdir($this->path);
        }

        do {
            $filename = uniqid('') . '.pdf';
        } while ($this->filesystem->exists("$this->path/$filename"));

        $this->filesystem->appendToFile("$this->path/$filename", $event->request->getContent());
        $this->logger?->info("PDF saved at $this->path/$filename.");
        $event->setFile(new SplFileInfo("$this->path/$filename"));

    }
}