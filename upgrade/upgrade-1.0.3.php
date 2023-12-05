<?php

function upgrade_module_1_0_3($module)
{
    Configuration::updateValue('ACCELASEARCH_TEST_UPGRADE', 'OK');
}