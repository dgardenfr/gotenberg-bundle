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
use Sensiolabs\GotenbergBundle\Builder\AsyncBuilderInterface;
use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;
use Sensiolabs\GotenbergBundle\GotenbergScreenshotInterface;
use Sensiolabs\GotenbergBundle\Processor\FileProcessor;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RouterInterface;

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
     * @param RouterInterface|null $router Symfony router.
     */
    public function __construct(
        private GotenbergScreenshotInterface $screenshot,
        private GotenbergPdfInterface        $pdf,
        private Filesystem                   $filesystem,
        private LoggerInterface              $logger,
        private ?RouterInterface             $router = null,
    )
    {
    }

    /**
     * {@inheritDoc}
     */
    public function html(string $html, string $output, PdfFileGeneratorOptions|array $options = PdfFileGeneratorOptions::DEFAULT): SplFileInfo
    {
        $tmp = tmpfile();
        fwrite($tmp, $html);
        $info = stream_get_meta_data($tmp);

        $file = $this->htmlFile($info['uri'], $output, $options);
        fclose($tmp);

        return $file;
    }

    /**
     * {@inheritDoc}
     */
    public function htmlFile(string $file, string $output, PdfFileGeneratorOptions|array $options = PdfFileGeneratorOptions::DEFAULT): SplFileInfo
    {
        $options = $this->getOptions($options);
        if (!str_starts_with($file, '/')) {
            $file = getcwd() . "/$file";
        }

        $assets = [];
        if (extension_loaded('dom')) {
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

        $options[PdfFileGeneratorOptions::OPTION_ASYNC] && $this->async($screenshot);

        $screenshot->generate()
            ->process();

        return new SplFileInfo($output);
    }

    /**
     * {@inheritDoc}
     */
    public function merge(string $output, string ...$paths): SplFileInfo
    {
        return $this->mergeWithOptions([], $output, ...$paths);
    }

    /**
     * {@inheritDoc}
     */
    public function mergeWithOptions(array|PdfFileGeneratorOptions $options, string $output, string ...$paths): SplFileInfo
    {
        $options = $this->getOptions($options);

        $paths = array_map(
            fn($f) => str_starts_with($f, '/') ? $f : getcwd() . "/$f",
            $paths
        );

        $screenshot = $this->pdf->merge()
            ->fileName(basename($output))
            ->processor($this->getProcessor($output))
            ->files(...$paths);

        $options[PdfFileGeneratorOptions::OPTION_ASYNC] && $this->async($screenshot);

        $screenshot->generate()
            ->process();

        return new SplFileInfo($output);
    }

    /**
     * {@inheritDoc}
     */
    public function template(string $template, string $output, array $context = [], PdfFileGeneratorOptions|array $options = PdfFileGeneratorOptions::DEFAULT): SplFileInfo
    {
        $options = $this->getOptions($options);

        $screenshot = $this->screenshot->html()
            ->content($template, $context)
            ->fileName(basename($output))
            ->processor($this->getProcessor($output));

        $options[PdfFileGeneratorOptions::OPTION_ASYNC] && $this->async($screenshot);

        $screenshot->generate()
            ->process();

        return new SplFileInfo($output);
    }

    /**
     * {@inheritDoc}
     */
    public function url(string $url, string $output, PdfFileGeneratorOptions|array $options = PdfFileGeneratorOptions::DEFAULT): SplFileInfo
    {
        $options = $this->getOptions($options);

        $screenshot = $this->screenshot->url()
            ->url($url)
            ->fileName(basename($output))
            ->processor($this->getProcessor($output));

        $options[PdfFileGeneratorOptions::OPTION_ASYNC] && $this->async($screenshot);

        $screenshot->generate()
            ->process();

        return new SplFileInfo($output);
    }

    /**
     * Configure the PDF screenshot async.
     *
     * @param AsyncBuilderInterface $screenshot The screenshot.
     *
     * @return void
     */
    private function async(AsyncBuilderInterface $screenshot): void
    {
        method_exists($screenshot, 'webhookUrl')
        && $screenshot->webhookUrl($this->router->generate('dgarden_gotenberg_async_pdf_generation'));
        $screenshot->generateAsync();
    }

    /**
     * Get the pdf file generator options from the options parameter.
     *
     * @param array|PdfFileGeneratorOptions $options The options.
     *
     * @return PdfFileGeneratorOptions
     */
    private function getOptions(array|PdfFileGeneratorOptions $options): PdfFileGeneratorOptions
    {
        return is_array($options)
            ? new PdfFileGeneratorOptions($options)
            : $options;
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