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

namespace Eccube\Repository\Master;

use Doctrine\Persistence\ManagerRegistry as RegistryInterface;
use Eccube\Entity\Master\OrderItemType;
use Eccube\Repository\AbstractRepository;

/**
 * OrderItemTypeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 *
 * @template T of OrderItemType
 *
 * @extends AbstractRepository<T>
 */
class OrderItemTypeRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OrderItemType::class);
    }
}
