<?php

namespace Accelasearch\Accelasearch\Tests\Unit\Cron;

use Mockery;
use Configuration;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Accelasearch\Accelasearch\Cron\Operation\FeedGeneration;
use Accelasearch\Accelasearch\Cron\Cron;

class CronTest extends TestCase
{

    public function testFeedGenerationIsExecutableAfterExpire()
    {
        $mock = Mockery::mock(Configuration::class);
        $mock->shouldReceive("get")
            ->andReturnUsing(function ($arg) {
                $valueMap = [
                    "_ACCELASEARCH_LAST_FeedGeneration_UPDATE" => time() - (3600 * 48),
                ];
                return $valueMap[$arg];
            });

        Configuration::setStaticExpectations($mock);
        $feedOperation = new FeedGeneration();
        $this->assertTrue($feedOperation->isOperationToExecute());
    }

    public function testFeedGenerationIsNotExecutableBeforeExpire()
    {
        $mock = Mockery::mock(Configuration::class);
        $mock->shouldReceive("get")
            ->andReturnUsing(function ($arg) {
                $valueMap = [
                    "_ACCELASEARCH_LAST_FeedGeneration_UPDATE" => time() - 60,
                ];
                return $valueMap[$arg];
            });

        Configuration::setStaticExpectations($mock);
        $feedOperation = new FeedGeneration();
        $this->assertFalse($feedOperation->isOperationToExecute());
    }

    public function testCronIsReadyWithWellConfiguredShop()
    {
        $mock = Mockery::mock(Configuration::class);
        $mock->shouldReceive("get")
            ->andReturnUsing(function ($arg) {
                $valueMap = [
                    "_ACCELASEARCH_ONBOARDING" => 3,
                    "_ACCELASEARCH_SHOPS_TO_SYNC" => "[1]"
                ];
                return $valueMap[$arg];
            });
        Configuration::setStaticExpectations($mock);
        $this->assertTrue(Cron::isReady());
    }

    public function testCronIsNotReadyWithNotConfiguredShop()
    {
        $mock = Mockery::mock(Configuration::class);
        $mock->shouldReceive("get")
            ->andReturnUsing(function ($arg) {
                $valueMap = [
                    "_ACCELASEARCH_ONBOARDING" => 2,
                    "_ACCELASEARCH_SHOPS_TO_SYNC" => "[]"
                ];
                return $valueMap[$arg];
            });
        Configuration::setStaticExpectations($mock);
        $this->assertFalse(Cron::isReady());
    }
}