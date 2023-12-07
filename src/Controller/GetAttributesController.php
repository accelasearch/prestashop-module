<?php

namespace Accelasearch\Accelasearch\Controller;

use Accelasearch\Accelasearch\Config\Config;
use AttributeGroup;

class GetAttributesController extends AbstractController implements ControllerInterface
{
    public function handleRequest()
    {
        // get default language
        $id_lang = (int) Config::get('PS_LANG_DEFAULT');
        $attributes = AttributeGroup::getAttributesGroups($id_lang);
        $this->success($attributes);
    }
}
