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

namespace Eccube\Tests\Web;

use Eccube\Controller\AbstractController;
use Eccube\Entity\BaseInfo;
use Eccube\Entity\Page;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\PageRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AbstractControllerTest extends AbstractWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }
    public function testSetLoginTargetPath()
    {
        $client = $this->client;
        $mock = new AbstractControllerMock();
        /** @var Session $session */
        $session = $this->client->getContainer()->get('session');
        $mock->setSession($session);
        $mock->setLoginTargetPath($this->generateUrl('mypage', [], UrlGeneratorInterface::ABSOLUTE_URL),"aaaa");
        $this->assertNotNull($session);
    }
}
class AbstractControllerMock extends AbstractController{

}
