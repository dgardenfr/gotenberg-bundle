<?php
/** This file is part of the GotenbergBundle package.
 *
 * (c) Digital Garden <developers@digitalgarden.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DigitalGarden\GotenbergBundle\Generator;

use DOMDocument;
use Psr\Log\LoggerInterface;
use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;
use Sensiolabs\GotenbergBundle\GotenbergScreenshotInterface;
use Sensiolabs\GotenbergBundle\Processor\FileProcessor;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;

/**
 * PDF file generation helper.
 */
readonly class PdfFileGenerator implements PdfFileGeneratorInterface
{
    /**
     * @param GotenbergScreenshotInterface $screenshot Gotenberg screenshort builder.
     * @param GotenbergPdfInterface $pdf Gotenberg pdf builder.
     * @param Filesystem $filesystem Filesystem.
     * @param LoggerInterface $logger Logger.
     *
     */
    public function __construct(
        private GotenbergScreenshotInterface $screenshot,
        private GotenbergPdfInterface        $pdf,
        private Filesystem                   $filesystem,
        private LoggerInterface              $logger,
    )
    {
    }

    /**
     * {@inheritDoc}
     */
    public function html(string $html, string $output): SplFileInfo
    {
        $tmp = tmpfile();
        fwrite($tmp, $html);
        $info = stream_get_meta_data($tmp);

        $file = $this->htmlFile($info['uri'], $output);
        fclose($tmp);

        return $file;
    }

    /**
     * {@inheritDoc}
     */
    public function htmlFile(string $file, string $output): SplFileInfo
    {
        if (!str_starts_with($file, '/')) {
            $file = getcwd() . "/$file";
        }

        $assets = [];
        if (extension_loaded('dom')) {
            $content = file_get_contents($file);
            $dom = new DOMDocument();
            $dom->load($file);

            $images = $dom->getElementsByTagName('img');
            foreach ($images as $image) {
                $imageSrc = dirname($file) . '/' . $image->getAttribute('src');
                $assets[] = $imageSrc;
            }
        } else {
            $this->logger->warning('The "dom" extension is not loaded. Assets will not be loaded.');
        }

        $screenshot = $this->screenshot->html()
            ->contentFile($file)
            ->fileName(basename($output))
            ->processor($this->getProcessor($output));

        foreach ($assets as $asset) {
            $screenshot->addAsset($asset);
        }

        $screenshot->generate()
            ->process();

        return new SplFileInfo($output);
    }

    /**
     * {@inheritDoc}
     */
    public function merge(string $output, string ...$paths): SplFileInfo
    {
        $paths = array_map(
            fn($f) => str_starts_with($f, '/') ? $f : getcwd() . "/$f",
            $paths
        );

        $this->pdf->merge()
            ->fileName(basename($output))
            ->processor($this->getProcessor($output))
            ->files(...$paths)
            ->generate()
            ->process();
    }

    /**
     * {@inheritDoc}
     */
    public function template(string $template, string $output, array $context = []): SplFileInfo
    {
        $this->screenshot->html()
            ->content($template, $context)
            ->fileName(basename($output))
            ->processor($this->getProcessor($output))
            ->generate()
            ->process();

        return new SplFileInfo($output);
    }

    /**
     * {@inheritDoc}
     */
    public function url(string $url, string $output): SplFileInfo
    {
        $this->screenshot->url()
            ->url($url)
            ->fileName(basename($output))
            ->processor($this->getProcessor($output))
            ->generate()
            ->process();

        return new SplFileInfo($output);
    }

    /**
     * Get a file processor.
     *
     * @param string $output The output file.
     *
     * @return FileProcessor
     */
    private function getProcessor(string $output): FileProcessor
    {
        if (!str_starts_with($output, '/')) {
            $output = getcwd() . "/$output";
        }

        return new FileProcessor(
            $this->filesystem,
            dirname($output),
            $this->logger,
        );
    }
}