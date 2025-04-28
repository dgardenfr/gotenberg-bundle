<?php
/**
 * This file is a part of the Digital Garden gotenberg bundle.
 */

namespace DigitalGarden\GotenbergBundle\Command;

use DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorInterface;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a PDF file from HTML or an HTML file.
 */
class MergePdfCommand extends Command
{
    public const ARGUMENT_FILES = 'files';

    /**
     * @param PdfFileGeneratorInterface $pdfFileGenerator Pdf file generator.
     */
    public function __construct(
        protected readonly PdfFileGeneratorInterface $pdfFileGenerator,
    )
    {
        parent::__construct();
    }

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
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $files = $input->getArgument(self::ARGUMENT_FILES);
        $outputFile = array_pop($files);

        $file = $this->pdfFileGenerator->merge($outputFile, ...$files);

        if ($file->isReadable()) {
            $output->writeln(sprintf('The pdf file has been generated at %s', $file->getPathname()));
            return Command::SUCCESS;
        }

        $output->writeln(sprintf('Error while generated the pdf at %s, file not readable.', $file->getPathname()));
        return Command::FAILURE;
    }
}