<?php
/**
 * This file is a part of the Digital Garden gotenberg bundle.
 */

 namespace DigitalGarden\GotenbergBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * Gotenberg bundle test kernel.
 */
final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * {@inheritDoc}
     */
    public function getProjectDir(): string
    {
        return __DIR__;
    }
}
 