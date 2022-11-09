<?php declare(strict_types=1);
/**
 * PHP version 7.2
 *
 * This source file is subject to the license that is bundled with this package in the file LICENSE.
 */

namespace PhUml\Parser\Code\Builders\Members;

use PhpParser\Node\Const_;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PHPUnit\Framework\TestCase;
use PhUml\Parser\Code\Builders\Filters\PrivateVisibilityFilter;
use PhUml\Parser\Code\Builders\Filters\ProtectedVisibilityFilter;

final class FilteredConstantsBuilderTest extends TestCase
{
    /** @test */
    function it_excludes_private_constants()
    {
        $constants = [
            new ClassConst([new Const_('INTEGER', new LNumber(1))]),
            new ClassConst([new Const_('FLOAT', new DNumber(1.5))], Class_::MODIFIER_PRIVATE),
            new ClassConst([new Const_('STRING', new String_('test'))], Class_::MODIFIER_PROTECTED),
        ];
        $builder = new FilteredConstantsBuilder(
            new VisibilityBuilder(),
            new VisibilityFilters([new PrivateVisibilityFilter()])
        );

        $constants = $builder->build($constants);

        $this->assertCount(2, $constants);
        $this->assertEquals('+INTEGER: int', (string) $constants[0]);
        $this->assertEquals('#STRING: string', (string) $constants[2]); // filters preserve original index
    }

    /** @test */
    function it_excludes_protected_constants()
    {
        $constants = [
            new ClassConst([new Const_('INTEGER', new LNumber(1))]),
            new ClassConst([new Const_('FLOAT', new DNumber(1.5))], Class_::MODIFIER_PRIVATE),
            new ClassConst([new Const_('STRING', new String_('test'))], Class_::MODIFIER_PROTECTED),
        ];
        $builder = new FilteredConstantsBuilder(
            new VisibilityBuilder(),
            new VisibilityFilters([new ProtectedVisibilityFilter()])
        );

        $constants = $builder->build($constants);

        $this->assertCount(2, $constants);
        $this->assertEquals('+INTEGER: int', (string) $constants[0]);
        $this->assertEquals('-FLOAT: float', (string) $constants[1]);
    }

    /** @test */
    function it_parses_a_class_constants()
    {
        $constants = [
            new ClassConst([new Const_('INTEGER', new LNumber(1))]),
            new ClassConst([new Const_('FLOAT', new DNumber(1.5))], Class_::MODIFIER_PRIVATE),
            new ClassConst([new Const_('STRING', new String_('test'))], Class_::MODIFIER_PROTECTED),
            new ClassConst([new Const_('BOOLEAN', new ConstFetch(new Name(['false'])))]),
        ];
        $builder = new FilteredConstantsBuilder(new VisibilityBuilder(), new VisibilityFilters());

        $constants = $builder->build($constants);

        $this->assertCount(4, $constants);
        $this->assertEquals('+INTEGER: int', (string) $constants[0]);
        $this->assertEquals('-FLOAT: float', (string) $constants[1]);
        $this->assertEquals('#STRING: string', (string) $constants[2]);
        $this->assertEquals('+BOOLEAN: bool', (string) $constants[3]);
    }

    /** @test */
    function it_does_not_extracts_types_for_expressions()
    {
        // const GREETING = 'My sentence' . PHP_EOL;
        $constants = [
            new ClassConst([new Const_(
                'GREETING',
                new Concat(
                    new String_('My sentence'),
                    new ConstFetch(new Name('PHP_EOL'))
                )
            )]),
        ];
        $builder = new FilteredConstantsBuilder(new VisibilityBuilder(), new VisibilityFilters());

        $rawConstants = $builder->build($constants);

        $this->assertCount(1, $rawConstants);
        $this->assertEquals('+GREETING', (string) $rawConstants[0]);
    }
}
