<?php

namespace DigitalGarden\GotenbergBundle\Event;

use SplFileInfo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event sent when a PDF is generated.
 */
class PdfGeneratedEvent extends Event
{
    /**
     * Generated file.
     *
     * @var SplFileInfo|null
     */
    private ?SplFileInfo $file = null;

    /**
     * Constructor.
     *
     * @param Request $request Request sent by Gotenberg.
     */
    public function __construct(
        public readonly Request $request,
    )
    {
    }

    /**
     * Get generated file.
     *
     * @return SplFileInfo|null
     */
    public function getFile(): ?SplFileInfo
    {
        return $this->file;
    }

    /**
     * Set generated file.
     *
     * @param SplFileInfo|null $file The generated file.
     */
    public function setFile(?SplFileInfo $file): void
    {
        $this->file = $file;
    }
}