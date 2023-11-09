<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use Accelasearch\Accelasearch\Dispatcher\Dispatcher;


class AccelasearchAdminController extends ModuleAdminController
{

    private $dispatcher;

    public function __construct()
    {
        parent::__construct();
        $this->dispatcher = new Dispatcher($this->module);
    }

    // create a controller to redirect to configure page if not accessed via ajax
    public function initContent()
    {
        if (!$this->ajax) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules') . '&configure=accelasearch');
        }
        header('Content-Type: application/json');
    }

    public function ajaxProcessGetUserInfo()
    {
        $this->dispatcher->handleRequest(__FUNCTION__);
    }

    public function ajaxProcessGetShops()
    {
        $this->dispatcher->handleRequest(__FUNCTION__);
    }

    public function ajaxProcessApikeyVerify()
    {
        $this->dispatcher->handleRequest(__FUNCTION__);
    }

    public function ajaxProcessSetShops()
    {
        $this->dispatcher->handleRequest(__FUNCTION__);
    }

    public function ajaxProcessGetAttributes()
    {
        $this->dispatcher->handleRequest(__FUNCTION__);
    }

    public function ajaxProcessUpdateConfig()
    {
        $this->dispatcher->handleRequest(__FUNCTION__);
    }

    public function ajaxProcessGetCronjobStatus()
    {
        $this->dispatcher->handleRequest(__FUNCTION__);
    }

    public function ajaxProcessGetLogs()
    {
        $this->dispatcher->handleRequest(__FUNCTION__);
    }

    public function ajaxProcessDisconnect()
    {
        $this->dispatcher->handleRequest(__FUNCTION__);
    }

    public function ajaxProcessUpdateModule()
    {
        $this->dispatcher->handleRequest(__FUNCTION__);
    }
}
