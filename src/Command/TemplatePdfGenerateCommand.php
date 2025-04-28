<?php
/** This file is a part of the Digital Garden gotenberg bundle. */

namespace DigitalGarden\GotenbergBundle\Command;

use DigitalGarden\GotenbergBundle\Model\Command\AbstractPdfGenerationCommand;
use SplFileInfo;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a PDF file from a template.
 */
class TemplatePdfGenerateCommand extends AbstractPdfGenerationCommand
{
    /**
     * Template argument name.
     */
    public const ARGUMENT_TEMPLATE = 'template';

    /**
     * Context option name.
     */
    public const OPTION_CONTEXT = 'context';

    /**
     * JSON encoded context option name.
     */
    public const OPTION_JSON_CONTEXT = 'json-context';

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Generate a pdf from a template.')
            ->setHelp('This command allows you to generate a PDF file from a template.')
            ->setAliases([
                'dgarden:gotenberg:template',
                'dgarden:gotenberg:generate-template',
                'dgarden:pdf:generate-template',
            ])
            ->addArgument(self::ARGUMENT_TEMPLATE, InputArgument::REQUIRED, 'The template to convert to pdf.')
            ->addOption(self::OPTION_CONTEXT, 'c', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'The context to use for the template.')
            ->addOption(self::OPTION_JSON_CONTEXT, 'j', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'The JSON encoded context to use for the template.')
            ->configurePdfGenerationCommand();
    }

    /**
     * {@inheritDoc}
     */
    protected function generate(InputInterface $input, OutputInterface $output): SplFileInfo
    {
        $context = array_merge(
            $this->getNamedOptionValues($input->getOption(self::OPTION_CONTEXT)),
            $this->getNamedOptionValues($input->getOption(self::OPTION_JSON_CONTEXT), true),
        );

        return $this->pdfFileGenerator->template(
            $input->getArgument(self::ARGUMENT_TEMPLATE),
            $this->outputFile,
            array_merge($context, $context),
        );
    }
}