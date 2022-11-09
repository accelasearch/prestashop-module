<?php declare(strict_types=1);
/**
 * PHP version 7.2
 *
 * This source file is subject to the license that is bundled with this package in the file LICENSE.
 */

namespace PhUml\Configuration;

use PhUml\Generators\DotFileGenerator;

final class DotFileBuilder extends DigraphBuilder
{
    public function __construct(DigraphConfiguration $configuration)
    {
        parent::__construct();
        $this->configuration = $configuration;
    }

    public function dotFileGenerator(): DotFileGenerator
    {
        return new DotFileGenerator($this->codeParser(), $this->digraphProcessor());
    }
}
