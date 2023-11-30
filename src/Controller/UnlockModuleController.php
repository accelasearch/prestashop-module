<?php

namespace Accelasearch\Accelasearch\Controller;

use Accelasearch\Accelasearch\Entity\Lock;
use Accelasearch\Accelasearch\Entity\Report;
use Accelasearch\Accelasearch\Logger\RemoteLog;

class UnlockModuleController extends AbstractController implements ControllerInterface
{
    private $module;
    public function __construct(\Module $module)
    {
        $this->module = $module;
    }
    public function handleRequest()
    {
        try {
            $locks = Lock::getExpiredLocks();
            (new Report("Report of expired LOCK", Report::EXPIRED_LOCK))->send();
            foreach ($locks as $lock) {
                $lockObject = new Lock($lock['name']);
                $lockObject->delete();
            }
            $this->success(true);
        } catch (\Exception $e) {
            RemoteLog::write($e->getMessage(), RemoteLog::ERROR, RemoteLog::CONTEXT_GENERAL, __LINE__, __FILE__);
            $this->error($e->getMessage(), 500);
        }
    }
}