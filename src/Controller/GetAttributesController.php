<?php

namespace Accelasearch\Accelasearch\Controller;

use Configuration;
use AttributeGroup;

class GetAttributesController extends AbstractController implements ControllerInterface
{
    public function handleRequest()
    {
        // get default language
        $id_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $attributes = AttributeGroup::getAttributesGroups($id_lang);
        $this->success($attributes);
    }
}