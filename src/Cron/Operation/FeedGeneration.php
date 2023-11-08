<?php

namespace Accelasearch\Accelasearch\Cron\Operation;

use Accelasearch\Accelasearch\Command\FeedFacade;
use Accelasearch\Accelasearch\Config\Config;

class FeedGeneration extends OperationAbstract
{
    public function execute()
    {
        $this->lock();

        $shops = Config::getShopsToSync();
        @\Context::getContext()->controller->controller_type = 'front';

        foreach ($shops as $shop) {
            FeedFacade::generateByIdShopAndIdLang((int) $shop->id_shop, (int) $shop->id_lang);
        }

        $this->updateExecutionTime();
        $this->unlock();
        echo "Done.";
    }

    public function getUpdateTiming(): int
    {
        return 60 * 15;
    }
}