<?php
/**
 * This file is part of the GotenbergBundle package.
 *
 * (c) Digital Garden <developers@digitalgarden.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DigitalGarden\GotenbergBundle\Generator;

use SplFileInfo;

/**
 * PDF file generator interface.
 */
interface PdfFileGeneratorInterface
{
    /**
     * Generate a PDF file from HTML.
     *
     * @param string $html The html.
     * @param string $output The output file.
     *
     * @return SplFileInfo
     */
    public function html(string $html, string $output): SplFileInfo;

    /**
     * Generate a PDF file from an HTML file.
     *
     * @param string $file The HTML file.
     * @param string $output The output file.
     *
     * @return SplFileInfo
     */
    public function htmlFile(string $file, string $output): SplFileInfo;

    /**
     * Merge several PDF files into one.
     *
     * @param string $output   The output file.
     * @param string ...$paths The paths to the PDF files to merge.
     *
     * @return SplFileInfo
     */
    public function merge(string $output, string ...$paths): SplFileInfo;

    /**
     * Generate a PDF file from a template.
     *
     * @param string $template The template.
     * @param string $output The output file.
     * @param array $context The context.
     *
     * @return SplFileInfo
     */
    public function template(string $template, string $output, array $context = []): SplFileInfo;

    /**
     * Generate a PDF file from url.
     *
     * @param string $url The url.
     * @param string $output The output file.
     *
     * @return SplFileInfo
     */
    public function url(string $url, string $output): SplFileInfo;
}