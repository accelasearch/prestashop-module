<?php

use PHPUnit\Framework\TestCase;
use Accelasearch\Accelasearch\Entity\Lock;

class Db
{
    public function executeS($query)
    {
        return [];
    }
}

class LockTest extends TestCase
{
    private $db;

    public function setUp(): void
    {
        $this->db = $this->createMock(Db::class);
    }

    public function testGetLocks()
    {
        $this->db->method('executeS')
            ->willReturn([
                [
                    "name" => "_ACCELASEARCH_test_lock_LOCK",
                    "value" => "1234567890",
                ],
                [
                    "name" => "_ACCELASEARCH_test_lock2_LOCK",
                    "value" => "1234567890",
                ],
            ]);

        $locks = Lock::getLocks($this->db);

        $this->assertEquals([
            [
                "name" => "test_lock",
                "value" => "1234567890",
            ],
            [
                "name" => "test_lock2",
                "value" => "1234567890",
            ],
        ], $locks);
    }

    public function testGetExpiredLocksWhenExpired()
    {

        $this->db->method('executeS')
            ->willReturn([
                [
                    "name" => "_ACCELASEARCH_test_lock_LOCK",
                    "value" => "1234567890",
                ],
                [
                    "name" => "_ACCELASEARCH_test_lock2_LOCK",
                    "value" => "1234567890",
                ],
            ]);

        $locks = Lock::getExpiredLocks($this->db);

        $this->assertEquals([
            [
                "name" => "test_lock",
                "value" => "1234567890",
            ],
            [
                "name" => "test_lock2",
                "value" => "1234567890",
            ],
        ], $locks);
    }

    public function testGetExpiredLocksWhenNotExpired()
    {
        $this->db->method('executeS')
            ->willReturn([
                [
                    "name" => "_ACCELASEARCH_test_lock_LOCK",
                    "value" => time(),
                ],
                [
                    "name" => "_ACCELASEARCH_test_lock2_LOCK",
                    "value" => time(),
                ],
            ]);

        $locks = Lock::getExpiredLocks($this->db);

        $this->assertEquals([], $locks);
    }
}