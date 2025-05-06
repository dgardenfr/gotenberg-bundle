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
 * Command generating a PDF from url.
 */
class UrlPdfGenerateCommand extends AbstractPdfGenerationCommand
{
    /**
     * Argument name for the url.
     */
    public const ARGUMENT_URL = 'url';

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Generates a pdf from a url.')
            ->setHelp('This command allows you to generate a pdf from a url.')
            ->setAliases([
                'dgarden:gotenberg:url',
                'dgarden:gotenberg:generate-url',
                'dgarden:pdf:generate-url'
            ])
            ->addArgument(self::ARGUMENT_URL, InputArgument::REQUIRED, 'The url to convert to pdf.')
            ->configurePdfGenerationCommand();
    }

    /**
     * {@inheritDoc}
     */
    protected function generate(InputInterface $input, OutputInterface $output): SplFileInfo
    {
        return $this->pdfFileGenerator->url(
            $input->getArgument(self::ARGUMENT_URL),
            $this->outputFile,
            $this->getOptions($input),
        );
    }
}