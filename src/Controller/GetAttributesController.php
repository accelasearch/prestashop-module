<?php

namespace Accelasearch\Accelasearch\Controller;

use Accelasearch\Accelasearch\Entity\Shop;

class GetAttributesController extends AbstractController implements ControllerInterface
{
    public function handleRequest()
    {
        $languages = \Language::getLanguages();
        $attributes = [];
        foreach ($languages as $language) {
            $id_lang = $language['id_lang'];
            $attributes[] = ["language" => $language, "attributes" => \AttributeGroup::getAttributesGroups($id_lang)];
        }
        $this->success($attributes);
    }
}