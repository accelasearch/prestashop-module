<?php declare(strict_types=1);
/**
 * PHP version 7.2
 *
 * This source file is subject to the license that is bundled with this package in the file LICENSE.
 */

namespace PhUml\Code\Modifiers;

interface HasVisibility
{
    public function isPublic(): bool;

    public function isPrivate(): bool;

    public function isProtected(): bool;

    public function hasVisibility(Visibility $modifier): bool;
}
