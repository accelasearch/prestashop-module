<?php declare(strict_types=1);
/**
 * PHP version 7.2
 *
 * This source file is subject to the license that is bundled with this package in the file LICENSE.
 */

namespace PhUml\Parser\Code\Builders\Names;

use PhpParser\Node;
use PhpParser\Node\Stmt\TraitUse;
use PhUml\Code\Name as TraitName;

trait TraitNamesBuilder
{
    /**
     * @param Node[] $nodes
     * @return TraitName[]
     */
    protected function buildTraits(array $nodes): array
    {
        $useStatements = array_filter($nodes, static function (Node $node): bool {
            return $node instanceof TraitUse;
        });

        if (count($useStatements) === 0) {
            return [];
        }

        $traits = [];
        /** @var TraitUse $use */
        foreach ($useStatements as $use) {
            $traits = $this->traitNames($use, $traits);
        }

        return $traits;
    }

    /**
     * @param TraitName[] $traits
     * @return TraitName[]
     */
    private function traitNames(TraitUse $use, array $traits): array
    {
        foreach ($use->traits as $name) {
            $traits[] = TraitName::from($name->getLast());
        }
        return $traits;
    }
}
