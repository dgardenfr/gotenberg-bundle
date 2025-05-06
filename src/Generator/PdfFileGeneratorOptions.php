<?php

namespace DigitalGarden\GotenbergBundle\Generator;

use ArrayAccess;

/**
 * Pdf file generator options.
 */
class PdfFileGeneratorOptions implements ArrayAccess
{
    /**
     * PDF file generator default options.
     */
    public const DEFAULT = [
        self::OPTION_ASYNC => false,
    ];

    /**
     * Asynchronous options.
     */
    public const OPTION_ASYNC = 'async';

    /**
     * Options.
     *
     * @var array
     */
    private array $options;

    /**
     * Constructor.
     *
     * @param array $options Options.
     */
    public function __construct(
        array $options = self::DEFAULT,
    )
    {
        $this->options = array_merge(self::DEFAULT, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->options);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->options[$offset];
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->options[$offset] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->options[$offset]);
    }
}