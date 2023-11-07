<?php

namespace Accelasearch\Accelasearch\Controller;

class GetAttributesController extends AbstractController implements ControllerInterface
{
    public function handleRequest()
    {
        // get default language
        $id_lang = \Configuration::get('PS_LANG_DEFAULT');
        $attributes = \AttributeGroup::getAttributesGroups($id_lang);
        $this->success($attributes);
    }
}