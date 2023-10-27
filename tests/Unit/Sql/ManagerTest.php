<?php

namespace Tests\Unit\Sql;

use PHPUnit\Framework\TestCase;

use Accelasearch\Accelasearch\Sql\Manager;

class ManagerTest extends TestCase
{
    public function testGetSql()
    {
        $manager = new Manager();

        $installSql = $manager->getSql(Manager::SQL_INSTALL);
        $this->assertNotFalse($installSql);
        $this->assertNotEmpty($installSql);

        $uninstallSql = $manager->getSql(Manager::SQL_UNINSTALL);
        $this->assertNotFalse($uninstallSql);
        $this->assertNotEmpty($uninstallSql);
    }
}
