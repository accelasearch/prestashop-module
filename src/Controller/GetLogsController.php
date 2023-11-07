<?php

namespace Accelasearch\Accelasearch\Controller;

use Accelasearch\Accelasearch\Logger\Log;

class GetLogsController extends AbstractController implements ControllerInterface
{
    public function handleRequest()
    {
        $logs = Log::getLogs();
        $this->success(["logs" => $logs]);
    }
}