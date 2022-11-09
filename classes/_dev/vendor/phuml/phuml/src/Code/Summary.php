<?php declare(strict_types=1);
/**
 * PHP version 7.2
 *
 * This source file is subject to the license that is bundled with this package in the file LICENSE.
 */

namespace PhUml\Code;

use PhUml\Code\Modifiers\Visibility;

/**
 * It creates a summary of the classes, interfaces, methods, and attributes of a codebase
 *
 * The summary of a `Structure` does not include counts of constants
 */
final class Summary
{
    /** @var int */
    private $interfaceCount;

    /** @var int */
    private $classCount;

    /** @var int */
    private $publicFunctionCount;

    /** @var int */
    private $publicAttributeCount;

    /** @var int */
    private $publicTypedAttributes;

    /** @var int */
    private $protectedFunctionCount;

    /** @var int */
    private $protectedAttributeCount;

    /** @var int */
    private $protectedTypedAttributes;

    /** @var int */
    private $privateFunctionCount;

    /** @var int */
    private $privateAttributeCount;

    /** @var int */
    private $privateTypedAttributes;

    public function __construct()
    {
        $this->interfaceCount = 0;
        $this->classCount = 0;
        $this->publicFunctionCount = 0;
        $this->publicAttributeCount = 0;
        $this->publicTypedAttributes = 0;
        $this->protectedFunctionCount = 0;
        $this->protectedAttributeCount = 0;
        $this->protectedTypedAttributes = 0;
        $this->privateFunctionCount = 0;
        $this->privateAttributeCount = 0;
        $this->privateTypedAttributes = 0;
    }

    public function from(Codebase $codebase): void
    {
        foreach ($codebase->definitions() as $definition) {
            if ($definition instanceof InterfaceDefinition) {
                $this->interfaceCount++;
            }
            if ($definition instanceof ClassDefinition) {
                $this->classCount++;
                $this->attributesSummary($definition);
            }
            $this->methodsSummary($definition);
        }
    }

    private function attributesSummary(ClassDefinition $definition): void
    {
        // Attributes count
        $this->publicAttributeCount += $definition->countAttributesByVisibility(Visibility::public());
        $this->protectedAttributeCount += $definition->countAttributesByVisibility(Visibility::protected());
        $this->privateAttributeCount += $definition->countAttributesByVisibility(Visibility::private());

        // Typed attributes count
        $this->publicTypedAttributes += $definition->countTypedAttributesByVisibility(Visibility::public());
        $this->protectedTypedAttributes += $definition->countTypedAttributesByVisibility(Visibility::protected());
        $this->privateTypedAttributes += $definition->countTypedAttributesByVisibility(Visibility::private());
    }

    private function methodsSummary(Definition $definition): void
    {
        $this->publicFunctionCount += $definition->countMethodsByVisibility(Visibility::public());
        $this->protectedFunctionCount += $definition->countMethodsByVisibility(Visibility::protected());
        $this->privateFunctionCount += $definition->countMethodsByVisibility(Visibility::private());
    }

    public function interfaceCount(): int
    {
        return $this->interfaceCount;
    }

    public function classCount(): int
    {
        return $this->classCount;
    }

    public function publicFunctionCount(): int
    {
        return $this->publicFunctionCount;
    }

    public function publicAttributeCount(): int
    {
        return $this->publicAttributeCount;
    }

    public function publicTypedAttributes(): int
    {
        return $this->publicTypedAttributes;
    }

    public function protectedFunctionCount(): int
    {
        return $this->protectedFunctionCount;
    }

    public function protectedAttributeCount(): int
    {
        return $this->protectedAttributeCount;
    }

    public function protectedTypedAttributes(): int
    {
        return $this->protectedTypedAttributes;
    }

    public function privateFunctionCount(): int
    {
        return $this->privateFunctionCount;
    }

    public function privateAttributeCount(): int
    {
        return $this->privateAttributeCount;
    }

    public function privateTypedAttributes(): int
    {
        return $this->privateTypedAttributes;
    }

    public function functionCount(): int
    {
        return $this->publicFunctionCount + $this->protectedFunctionCount + $this->privateFunctionCount;
    }

    public function attributeCount(): int
    {
        return $this->publicAttributeCount + $this->protectedAttributeCount + $this->privateAttributeCount;
    }

    public function typedAttributeCount(): int
    {
        return $this->publicTypedAttributes + $this->protectedTypedAttributes + $this->privateTypedAttributes;
    }

    public function attributesPerClass(): float
    {
        if ($this->classCount === 0) {
            return 0;
        }

        return round($this->attributeCount() / $this->classCount, 2);
    }

    public function functionsPerClass(): float
    {
        if ($this->classCount === 0) {
            return 0;
        }

        return round($this->functionCount() / $this->classCount, 2);
    }
}
