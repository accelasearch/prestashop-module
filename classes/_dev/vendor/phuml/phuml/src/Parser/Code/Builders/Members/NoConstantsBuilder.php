<?php declare(strict_types=1);
/**
 * PHP version 7.2
 *
 * This source file is subject to the license that is bundled with this package in the file LICENSE.
 */

namespace PhUml\Parser\Code\Builders\Members;

final class NoConstantsBuilder implements ConstantsBuilder
{
    public function build(array $classAttributes): array
    {
        return [];
    }
}
