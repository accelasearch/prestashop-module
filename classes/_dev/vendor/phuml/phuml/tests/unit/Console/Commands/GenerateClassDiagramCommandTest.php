<?php declare(strict_types=1);
/**
 * PHP version 7.2
 *
 * This source file is subject to the license that is bundled with this package in the file LICENSE.
 */

namespace PhUml\Console\Commands;

use PHPUnit\Framework\TestCase;
use PhUml\Console\PhUmlApplication;
use PhUml\Console\ProgressDisplay;
use PhUml\Fakes\TextInMemoryOutput;
use PhUml\Parser\InvalidDirectory;
use RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

final class GenerateClassDiagramCommandTest extends TestCase
{
    /** @test */
    function it_fails_to_execute_if_the_arguments_are_missing()
    {
        $this->expectException(RuntimeException::class);

        $this->tester->execute([
            'command' => $this->command->getName(),
        ]);
    }

    /** @test */
    function it_fails_to_generate_a_diagram_if_directory_with_classes_does_not_exist()
    {
        $this->expectException(InvalidDirectory::class);

        $this->tester->execute([
            'command' => $this->command->getName(),
            'directory' => 'invalid-directory',
            'output' => $this->diagram,
            '--processor' => 'neato',
        ]);
    }

    /** @test */
    function it_generates_a_class_diagram_without_searching_recursively_for_classes()
    {
        $status = $this->tester->execute([
            'command' => $this->command->getName(),
            'directory' => "{$this->pathToCode}/classes",
            'output' => $this->diagram,
            '--processor' => 'dot',
        ]);

        $this->assertEquals(0, $status);
        $this->assertFileExists($this->diagram);
    }

    /** @test */
    function it_generates_a_class_diagram_searching_for_files_recursively_with_associations()
    {
        $status = $this->tester->execute([
            'command' => $this->command->getName(),
            'directory' => $this->pathToCode,
            'output' => $this->diagram,
            '--recursive' => true,
            '--associations' => true,
            '--processor' => 'neato',
        ]);

        $this->assertEquals(0, $status);
        $this->assertFileExists($this->diagram);
    }

    /** @test */
    function it_generates_a_class_diagram_excluding_private_and_protected_members()
    {
        $status = $this->tester->execute([
            'command' => $this->command->getName(),
            'directory' => $this->pathToCode,
            'output' => $this->diagram,
            '--recursive' => true,
            '--associations' => true,
            '--hide-private' => true,
            '--hide-protected' => true,
            '--processor' => 'neato',
        ]);

        $this->assertEquals(0, $status);
        $this->assertFileExists($this->diagram);
    }

    /** @test */
    function it_generates_a_class_diagram_excluding_attributes_and_methods()
    {
        $status = $this->tester->execute([
            'command' => $this->command->getName(),
            'directory' => $this->pathToCode,
            'output' => $this->diagram,
            '--recursive' => true,
            '--associations' => true,
            '--hide-attributes' => true,
            '--hide-methods' => true,
            '--processor' => 'neato',
        ]);

        $this->assertEquals(0, $status);
        $this->assertFileExists($this->diagram);
    }

    /** @test */
    function it_generates_a_class_diagram_with_only_definition_names()
    {
        $status = $this->tester->execute([
            'command' => $this->command->getName(),
            'directory' => $this->pathToCode,
            'output' => $this->diagram,
            '--recursive' => true,
            '--associations' => true,
            '--hide-attributes' => true,
            '--hide-methods' => true,
            '--hide-empty-blocks' => true,
            '--processor' => 'neato',
        ]);

        $this->assertEquals(0, $status);
        $this->assertFileExists($this->diagram);
    }

    /** @test */
    function it_generates_a_class_diagram_with_a_specific_theme()
    {
        $status = $this->tester->execute([
            'command' => $this->command->getName(),
            'directory' => $this->pathToCode,
            'output' => $this->diagram,
            '--recursive' => true,
            '--associations' => true,
            '--hide-attributes' => true,
            '--hide-methods' => true,
            '--hide-empty-blocks' => true,
            '--processor' => 'neato',
            '--theme' => 'php',
        ]);

        $this->assertEquals(0, $status);
        $this->assertFileExists($this->diagram);
    }

    /** @before */
    function configureCommandTester()
    {
        $application = new PhUmlApplication(new ProgressDisplay(new TextInMemoryOutput()));
        $this->command = $application->find('phuml:diagram');
        $this->tester = new CommandTester($this->command);
        $this->pathToCode = __DIR__ . '/../../../resources/.code';
        $this->diagram = __DIR__ . '/../../../resources/.output/out.png';
        if (file_exists($this->diagram)) {
            unlink($this->diagram);
        }
    }

    /** @var GenerateClassDiagramCommand */
    private $command;

    /** @var CommandTester */
    private $tester;

    /** @var string */
    private $diagram;

    /** @var string */
    private $pathToCode;
}
