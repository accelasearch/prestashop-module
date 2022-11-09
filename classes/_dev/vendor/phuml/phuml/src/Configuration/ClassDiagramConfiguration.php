<?php declare(strict_types=1);
/**
 * PHP version 7.2
 *
 * This source file is subject to the license that is bundled with this package in the file LICENSE.
 */

namespace PhUml\Configuration;

use PhUml\Processors\ImageProcessorName;

final class ClassDiagramConfiguration extends DigraphConfiguration
{
    /** @var ImageProcessorName */
    private $imageProcessor;

    /** @param mixed[] $input */
    public function __construct(array $input)
    {
        parent::__construct($input);
        $this->setImageProcessor($input['processor']);
    }

    public function isDotProcessor(): bool
    {
        return $this->imageProcessor->is('dot');
    }

    private function setImageProcessor(?string $imageProcessor): void
    {
        $this->imageProcessor = ImageProcessorName::from($imageProcessor);
    }
}
