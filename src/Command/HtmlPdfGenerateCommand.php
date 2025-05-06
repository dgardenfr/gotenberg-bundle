<?php
/**
 * This file is a part of the Digital Garden gotenberg bundle.
 */

namespace DigitalGarden\GotenbergBundle\Command;

use DigitalGarden\GotenbergBundle\Model\Command\AbstractPdfGenerationCommand;
use SplFileInfo;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a PDF file from HTML or an HTML file.
 */
class HtmlPdfGenerateCommand extends AbstractPdfGenerationCommand
{
    public const ARGUMENT_HTML = 'html';

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Generates a pdf from an HTML or an HTML file.')
            ->setHelp('This command allows you to generate a pdf from an HTML or an HTML file.')
            ->setAliases([
                'dgarden:gotenberg:html',
                'dgarden:gotenberg:generate-html',
                'dgarden:pdf:generate-html',
            ])
            ->addArgument(self::ARGUMENT_HTML, InputArgument::REQUIRED, 'The HTML or HTML file to convert to pdf.')
            ->configurePdfGenerationCommand();
    }

    /**
     * {@inheritDoc}
     */
    protected function generate(InputInterface $input, OutputInterface $output): SplFileInfo
    {
        $html = $input->getArgument(self::ARGUMENT_HTML);

        if (file_exists($html)) {
            return $this->pdfFileGenerator->htmlFile($html, $this->outputFile, $this->getOptions($input));
        }

        return $this->pdfFileGenerator->html($html, $this->outputFile);
    }
}