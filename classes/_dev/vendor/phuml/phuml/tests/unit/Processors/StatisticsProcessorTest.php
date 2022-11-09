<?php declare(strict_types=1);
/**
 * PHP version 7.2
 *
 * This source file is subject to the license that is bundled with this package in the file LICENSE.
 */

namespace PhUml\Processors;

use PHPUnit\Framework\TestCase;

final class StatisticsProcessorTest extends TestCase
{
    /** @test */
    function it_has_a_name()
    {
        $processor = new StatisticsProcessor();

        $name = $processor->name();

        $this->assertEquals('Statistics', $name);
    }
}
