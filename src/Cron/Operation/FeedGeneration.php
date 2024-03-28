<?php

namespace Accelasearch\Accelasearch\Cron\Operation;

use Accelasearch\Accelasearch\Command\FeedFacade;
use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Logger\RemoteLog;

class FeedGeneration extends OperationAbstract
{
    public function execute()
    {

        $this->lock();

        $shops = Config::getShopsToSync();
        @\Context::getContext()->controller->controller_type = 'front';

        foreach ($shops as $shop) {
            try {
                FeedFacade::generateByIdShopAndIdLang((int) $shop->id_shop, (int) $shop->id_lang);
            } catch (\Throwable $e) {
                $this->unlock();
                RemoteLog::write($e->getMessage(), RemoteLog::CRITICAL, RemoteLog::CONTEXT_PRODUCT_FEED_CREATION);
            }
        }

        $this->updateExecutionTime();
        $this->unlock();
        echo "Done.";
    }

    public function getUpdateTiming(): int
    {
        return 60 * 60;
    }
}