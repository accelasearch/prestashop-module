<?php

namespace Accelasearch\Accelasearch\Entity;

use Accelasearch\Accelasearch\Exception\LanguageNotFoundException;

class Language
{
    private $id;
    public $ps;
    public function __construct(int $id)
    {
        if (\Language::getLanguage($id) === false)
            throw new LanguageNotFoundException($id);
        $this->id = $id;
        $this->ps = new \Language($id);
    }
    public function getId(): int
    {
        return $this->id;
    }
}