<?php declare(strict_types=1);
/**
 * PHP version 7.2
 *
 * This source file is subject to the license that is bundled with this package in the file LICENSE.
 */

namespace PhUml\Console;

use PhUml\Generators\ProcessorProgressDisplay;
use PhUml\Processors\Processor;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * It provides visual feedback to the use about the progress of the current command
 *
 * @see ProcessorProgressDisplay for more details about the things that are reported by this display
 */
final class ProgressDisplay implements ProcessorProgressDisplay
{
    /** @var OutputInterface */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function start(): void
    {
        $this->display('Running... (This may take some time)');
    }

    public function runningParser(): void
    {
        $this->display('Parsing codebase structure');
    }

    public function runningProcessor(Processor $processor): void
    {
        $this->display("Running '{$processor->name()}' processor");
    }

    public function savingResult(): void
    {
        $this->display('Writing generated data to disk');
    }

    private function display(string $message): void
    {
        $this->output->writeln("<info>[|]</info> $message");
    }
}
