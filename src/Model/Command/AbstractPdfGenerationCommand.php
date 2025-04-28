<?php

namespace DigitalGarden\GotenbergBundle\Model\Command;

use DigitalGarden\GotenbergBundle\Generator\PdfFileGenerator;
use DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorInterface;
use Sensiolabs\GotenbergBundle\Exception\ClientException;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * PDF file generation command.
 */
abstract class AbstractPdfGenerationCommand extends Command
{

    /**
     * Output file argument name.
     */
    const ARGUMENT_OUTPUT_FILE = 'output_file';

    /**
     * Output file.
     *
     * @var string
     */
    protected string $outputFile;

    /**
     * Generate a PDF file.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return SplFileInfo
     */
    abstract protected function generate(InputInterface $input, OutputInterface $output): SplFileInfo;

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
     * {inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $this->generate($input, $output);

        if ($file->isReadable()) {
            $output->writeln(sprintf('The pdf file has been generated at %s', $file->getPathname()));
            return Command::SUCCESS;
        }

        $output->writeln(sprintf('Error while generated the pdf at %s, file not readable.', $file->getPathname()));
        return Command::FAILURE;
    }

    /**
     * Configure the PDF generation command.
     *
     * @return $this
     */
    protected function configurePdfGenerationCommand(): self
    {
        $this->addArgument(
            self::ARGUMENT_OUTPUT_FILE,
            InputArgument::REQUIRED,
            'The output file.',
        );

        return $this;
    }

    /**
     * Get named option values.
     *
     * Example:
     *   $ bin/console dgarden:pdf:template -c name=Template
     *   $this->getNamedOptionValues($input->getOption('name')) # ['name' => 'Template']
     * @param array|string $value The option value
     * @param bool $json If true, the value is json-decoded
     * @return array
     */
    protected function getNamedOptionValues(array|string $value, bool $json = false): array
    {
        if (is_string($value)) {
            $exp = explode('=', $value);
            $exp[1] = $exp[1] ?? null;

            return [
                $exp[0] => $json ? json_decode($exp[1], true) : $exp[1],
            ];
        }

        $values = [];
        array_walk(
            $value,
            function ($value) use (&$values, $json) {
                foreach ($this->getNamedOptionValues($value, $json) as $name => $val) {
                    $values[$name] = $val;
                }
            },
        );

        return $values;
    }

    /**
     * {@inheritDoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->outputFile = $input->getArgument(self::ARGUMENT_OUTPUT_FILE);
    }
}