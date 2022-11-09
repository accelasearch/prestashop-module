<?php declare(strict_types=1);
/**
 * PHP version 7.2
 *
 * This source file is subject to the license that is bundled with this package in the file LICENSE.
 */

namespace PhUml\Parser\Code\Builders\Members;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PhUml\Code\Attributes\Attribute;
use PhUml\Code\Attributes\AttributeDocBlock;
use PhUml\Code\Variables\Variable;
use PhUml\Parser\Code\Builders\Filters\PrivateVisibilityFilter;
use PhUml\Parser\Code\Builders\Filters\ProtectedVisibilityFilter;

/**
 * It builds an array of `Attributes` for a `ClassDefinition` or a `TraitDefinition`
 *
 * It applies one or more `VisibilityFilter`s
 *
 * @see PrivateVisibilityFilter
 * @see ProtectedVisibilityFilter
 */
final class FilteredAttributesBuilder implements AttributesBuilder
{
    /** @var VisibilityBuilder */
    private $visibilityBuilder;

    /** @var VisibilityFilters */
    private $visibilityFilters;

    public function __construct(VisibilityBuilder $visibilityBuilder, VisibilityFilters $filters)
    {
        $this->visibilityBuilder = $visibilityBuilder;
        $this->visibilityFilters = $filters;
    }

    /**
     * @param Node[] $parsedAttributes
     * @return Attribute[]
     */
    public function build(array $parsedAttributes): array
    {
        $attributes = array_filter($parsedAttributes, static function ($attribute): bool {
            return $attribute instanceof Property;
        });

        return array_map(function (Property $attribute): Attribute {
            $name = "\${$attribute->props[0]->name}";
            $visibility = $this->visibilityBuilder->build($attribute);
            $comment = $attribute->getDocComment() === null ? null : $attribute->getDocComment()->getText();
            $docBlock = AttributeDocBlock::from($comment);
            $variable = new Variable($name, $docBlock->extractType());

            return new Attribute($variable, $visibility, $attribute->isStatic());
        }, $this->visibilityFilters->apply($attributes));
    }
}
