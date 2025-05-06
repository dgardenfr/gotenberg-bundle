<?php
/**
 * This file is a part of the Digital Garden gotenberg bundle.
 */

namespace DigitalGarden\GotenbergBundle\Command;

use Composer\Console\Input\InputOption;
use DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorInterface;
use DigitalGarden\GotenbergBundle\Model\Command\AbstractPdfGenerationCommand;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a PDF file from HTML or an HTML file.
 */
class MergePdfCommand extends AbstractPdfGenerationCommand
{
    public const ARGUMENT_FILES = 'files';

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Generates a pdf from an HTML or an HTML file.')
            ->setHelp('This command allows you to generate a pdf from an HTML or an HTML file.')
            ->setAliases([
                'dgarden:gotenberg:merge',
                'dgarden:gotenberg:merge-pdf',
                'dgarden:pdf:merge-pdf',
            ])
            ->addArgument(
                self::ARGUMENT_FILES,
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'The files to merge. The last file given is used as the output file.'
            );

        $this->addOption(
            self::OPTION_ASYNC,
            null,
            InputOption::VALUE_NONE,
            'Generate the file asynchronously.',
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function generate(InputInterface $input, OutputInterface $output): SplFileInfo
    {
        $files = $input->getArgument(self::ARGUMENT_FILES);
        $outputFile = array_pop($files);

        return $this->pdfFileGenerator->mergeWithOptions($this->getOptions($input), $outputFile, ...$files);
    }

    /**
     * {@inheritDoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {

    }
}