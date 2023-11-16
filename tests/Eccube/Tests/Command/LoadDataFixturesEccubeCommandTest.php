<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eccube\Tests\Command;

use Eccube\Command\GenerateDummyDataCommand;
use Eccube\Command\LoadDataFixturesEccubeCommand;
use Eccube\Tests\EccubeTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class LoadDataFixturesEccubeCommandTest extends EccubeTestCase
{

    public function testExecute()
    {
        $this->markAsRisky();
        $this->markTestSkipped("データベース初期化コマンドのためスキップ");
        $commandArg = [];

        /** @var LoadDataFixturesEccubeCommand $command */
        $command = static::getContainer()->get(LoadDataFixturesEccubeCommand::class);
        $CommandTester = new CommandTester($command);
        $result = $CommandTester->execute($commandArg);

        $this->expected = 0;
        $this->actual = $result;
        $this->verify();

    }
}
