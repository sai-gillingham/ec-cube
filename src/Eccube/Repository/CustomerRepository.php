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

namespace Eccube\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry as RegistryInterface;
use Eccube\Common\EccubeConfig;
use Eccube\Doctrine\Query\Queries;
use Eccube\Entity\Customer;
use Eccube\Entity\Master\CustomerStatus;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Entity\Master\Pref;
use Eccube\Entity\Master\Sex;
use Eccube\Util\StringUtil;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

/**
 * CustomerRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CustomerRepository extends AbstractRepository
{
    /**
     * @var Queries
     */
    protected $queries;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var EncoderFactoryInterface
     */
    protected $encoderFactory;

    public const COLUMNS = [
        'customer_id' => 'c.id', 'name' => 'c.name01',
    ];

    /**
     * CustomerRepository constructor.
     *
     * @param RegistryInterface $registry
     * @param Queries $queries
     * @param EntityManagerInterface $entityManager
     * @param OrderRepository $orderRepository
     * @param EncoderFactoryInterface $encoderFactory
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(
        RegistryInterface $registry,
        Queries $queries,
        EntityManagerInterface $entityManager,
        OrderRepository $orderRepository,
        EncoderFactoryInterface $encoderFactory,
        EccubeConfig $eccubeConfig
    ) {
        parent::__construct($registry, Customer::class);

        $this->queries = $queries;
        $this->entityManager = $entityManager;
        $this->orderRepository = $orderRepository;
        $this->encoderFactory = $encoderFactory;
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * @return Customer
     */
    public function newCustomer()
    {
        $CustomerStatus = $this->getEntityManager()
            ->find(CustomerStatus::class, CustomerStatus::PROVISIONAL);

        $Customer = new \Eccube\Entity\Customer();
        $Customer
            ->setStatus($CustomerStatus);

        return $Customer;
    }

    /**
     * @param array{
     *         multi?:string,
     *         pref?:Pref,
     *         sex?:Sex[]|ArrayCollection,
     *         birth_month?:string|int,
     *         birth_start?:\DateTime,
     *         birth_end?:\DateTime,
     *         phone_number?:string,
     *         buy_total_start?:string|int,
     *         buy_total_end?:string|int,
     *         buy_times_start?:string|int,
     *         buy_times_end?:string|int,
     *         create_datetime_start?:\DateTime,
     *         create_datetime_end?:\DateTime,
     *         create_date_start?:\DateTime,
     *         create_date_end?:\DateTime,
     *         update_datetime_start?:\DateTime,
     *         update_datetime_end?:\DateTime,
     *         update_date_start?:\DateTime,
     *         update_date_end?:\DateTime,
     *         last_buy_datetime_start?:\DateTime,
     *         last_buy_datetime_end?:\DateTime,
     *         last_buy_start?:\DateTime,
     *         last_buy_end?:\DateTime,
     *         customer_status?:CustomerStatus[]|ArrayCollection,
     *         buy_product_name?:string,
     *         sortkey?:string,
     *         sorttype?:string
     *     } $searchData
     * @return QueryBuilder
     * @throws Exception
     */
    public function getQueryBuilderBySearchData($searchData)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c');

        if (isset($searchData['multi']) && StringUtil::isNotBlank($searchData['multi'])) {
            // スペース除去
            $clean_key_multi = preg_replace('/\s+|[　]+/u', '', $searchData['multi']);
            $id = preg_match('/^\d{0,10}$/', $clean_key_multi) ? $clean_key_multi : null;
            if ($id && $id > '2147483647' && $this->isPostgreSQL()) {
                $id = null;
            }
            $qb
                ->andWhere("c.id = :customer_id OR CONCAT(c.name01, c.name02) LIKE :name OR CONCAT(COALESCE(c.kana01, ''), COALESCE(c.kana02, '')) LIKE :kana OR c.email LIKE :email")
                ->setParameter('customer_id', $id)
                ->setParameter('name', '%'.$clean_key_multi.'%')
                ->setParameter('kana', '%'.$clean_key_multi.'%')
                ->setParameter('email', '%'.$clean_key_multi.'%');
        }

        // Pref
        if (!empty($searchData['pref'])) {
            $qb
                ->andWhere('c.Pref = :pref')
                ->setParameter('pref', $searchData['pref']->getId());
        }

        // sex
        if (!empty($searchData['sex']) && count($searchData['sex']) > 0) {
            $sexs = [];
            foreach ($searchData['sex'] as $sex) {
                $sexs[] = $sex->getId();
            }

            $qb
                ->andWhere($qb->expr()->in('c.Sex', ':sexs'))
                ->setParameter('sexs', $sexs);
        }

        if (!empty($searchData['birth_month'])) {
            $qb
                ->andWhere('EXTRACT(MONTH FROM c.birth) = :birth_month')
                ->setParameter('birth_month', $searchData['birth_month']);
        }

        // birth
        if (!empty($searchData['birth_start'])) {
            $qb
                ->andWhere('c.birth >= :birth_start')
                ->setParameter('birth_start', $searchData['birth_start']);
        }
        if (!empty($searchData['birth_end'])) {
            $date = clone $searchData['birth_end'];
            $date->modify('+1 days');
            $qb
                ->andWhere('c.birth < :birth_end')
                ->setParameter('birth_end', $date);
        }

        // tel
        if (isset($searchData['phone_number']) && StringUtil::isNotBlank($searchData['phone_number'])) {
            $tel = preg_replace('/[^0-9]/', '', $searchData['phone_number']);
            $qb
                ->andWhere('c.phone_number LIKE :phone_number')
                ->setParameter('phone_number', '%'.$tel.'%');
        }

        // buy_total
        if (isset($searchData['buy_total_start']) && StringUtil::isNotBlank($searchData['buy_total_start'])) {
            $qb
                ->andWhere('c.buy_total >= :buy_total_start')
                ->setParameter('buy_total_start', $searchData['buy_total_start']);
        }
        if (isset($searchData['buy_total_end']) && StringUtil::isNotBlank($searchData['buy_total_end'])) {
            $qb
                ->andWhere('c.buy_total <= :buy_total_end')
                ->setParameter('buy_total_end', $searchData['buy_total_end']);
        }

        // buy_times
        if (isset($searchData['buy_times_start']) && StringUtil::isNotBlank($searchData['buy_times_start'])) {
            $qb
                ->andWhere('c.buy_times >= :buy_times_start')
                ->setParameter('buy_times_start', $searchData['buy_times_start']);
        }
        if (isset($searchData['buy_times_end']) && StringUtil::isNotBlank($searchData['buy_times_end'])) {
            $qb
                ->andWhere('c.buy_times <= :buy_times_end')
                ->setParameter('buy_times_end', $searchData['buy_times_end']);
        }

        // create_date
        if (!empty($searchData['create_datetime_start'])) {
            $date = $searchData['create_datetime_start'];
            $qb
                ->andWhere('c.create_date >= :create_date_start')
                ->setParameter('create_date_start', $date);
        } elseif (!empty($searchData['create_date_start']) && $searchData['create_date_start']) {
            $qb
                ->andWhere('c.create_date >= :create_date_start')
                ->setParameter('create_date_start', $searchData['create_date_start']);
        }

        if (!empty($searchData['create_datetime_end'])) {
            $date = $searchData['create_datetime_end'];
            $qb
                ->andWhere('c.create_date < :create_date_end')
                ->setParameter('create_date_end', $date);
        } elseif (!empty($searchData['create_date_end']) && $searchData['create_date_end']) {
            $date = clone $searchData['create_date_end'];
            $date->modify('+1 days');
            $qb
                ->andWhere('c.create_date < :create_date_end')
                ->setParameter('create_date_end', $date);
        }

        // update_date
        if (!empty($searchData['update_datetime_start'])) {
            $date = $searchData['update_datetime_start'];
            $qb
                ->andWhere('c.update_date >= :update_date_start')
                ->setParameter('update_date_start', $date);
        } elseif (!empty($searchData['update_date_start']) && $searchData['update_date_start']) {
            $qb
                ->andWhere('c.update_date >= :update_date_start')
                ->setParameter('update_date_start', $searchData['update_date_start']);
        }

        if (!empty($searchData['update_datetime_end'])) {
            $date = $searchData['update_datetime_end'];
            $qb
                ->andWhere('c.update_date < :update_date_end')
                ->setParameter('update_date_end', $date);
        } elseif (!empty($searchData['update_date_end']) && $searchData['update_date_end']) {
            $date = clone $searchData['update_date_end'];
            $date->modify('+1 days');
            $qb
                ->andWhere('c.update_date < :update_date_end')
                ->setParameter('update_date_end', $date);
        }

        // last_buy
        if (!empty($searchData['last_buy_datetime_start'])) {
            $date = $searchData['last_buy_datetime_start'];
            $qb
                ->andWhere('c.last_buy_date >= :last_buy_start')
                ->setParameter('last_buy_start', $date);
        } elseif (!empty($searchData['last_buy_start']) && $searchData['last_buy_start']) {
            $qb
                ->andWhere('c.last_buy_date >= :last_buy_start')
                ->setParameter('last_buy_start', $searchData['last_buy_start']);
        }

        if (!empty($searchData['last_buy_datetime_end'])) {
            $date = $searchData['last_buy_datetime_end'];
            $qb
                ->andWhere('c.last_buy_date < :last_buy_end')
                ->setParameter('last_buy_end', $date);
        } elseif (!empty($searchData['last_buy_end']) && $searchData['last_buy_end']) {
            $date = clone $searchData['last_buy_end'];
            $date->modify('+1 days');
            $qb
                ->andWhere('c.last_buy_date < :last_buy_end')
                ->setParameter('last_buy_end', $date);
        }

        // status
        if (!empty($searchData['customer_status']) && count($searchData['customer_status']) > 0) {
            $qb
                ->andWhere($qb->expr()->in('c.Status', ':statuses'))
                ->setParameter('statuses', $searchData['customer_status']);
        }

        // buy_product_name
        if (isset($searchData['buy_product_name']) && StringUtil::isNotBlank($searchData['buy_product_name'])) {
            $qb
                ->leftJoin('c.Orders', 'o')
                ->leftJoin('o.OrderItems', 'oi')
                ->andWhere('oi.product_name LIKE :buy_product_name')
                ->andWhere($qb->expr()->notIn('o.OrderStatus', ':order_status'))
                ->setParameter('buy_product_name', '%'.$searchData['buy_product_name'].'%')
                ->setParameter('order_status', [OrderStatus::PROCESSING, OrderStatus::PENDING]);
        }

        // Order By
        if (isset($searchData['sortkey']) && !empty($searchData['sortkey'])) {
            $sortOrder = (isset($searchData['sorttype']) && $searchData['sorttype'] == 'a') ? 'ASC' : 'DESC';
            $qb->orderBy(self::COLUMNS[$searchData['sortkey']], $sortOrder);
            $qb->addOrderBy('c.update_date', 'DESC');
            $qb->addOrderBy('c.id', 'DESC');
        } else {
            $qb->orderBy('c.update_date', 'DESC');
            $qb->addOrderBy('c.id', 'DESC');
        }

        return $this->queries->customize(QueryKey::CUSTOMER_SEARCH, $qb, $searchData);
    }

    /**
     * ユニークなシークレットキーを返す.
     *
     * @return string
     */
    public function getUniqueSecretKey()
    {
        do {
            $key = StringUtil::random(32);
            $Customer = $this->findOneBy(['secret_key' => $key]);
        } while ($Customer);

        return $key;
    }

    /**
     * ユニークなパスワードリセットキーを返す
     *
     * @return string
     */
    public function getUniqueResetKey()
    {
        do {
            $key = StringUtil::random(32);
            $Customer = $this->findOneBy(['reset_key' => $key]);
        } while ($Customer);

        return $key;
    }

    /**
     * 仮会員をシークレットキーで検索する.
     *
     * @param string $secretKey
     *
     * @return Customer|null 見つからない場合はnullを返す.
     */
    public function getProvisionalCustomerBySecretKey($secretKey)
    {
        return $this->findOneBy([
            'secret_key' => $secretKey,
            'Status' => CustomerStatus::PROVISIONAL,
        ]);
    }

    /**
     * 本会員をemailで検索する.
     *
     * @param string $email
     *
     * @return Customer|null 見つからない場合はnullを返す.
     */
    public function getRegularCustomerByEmail($email)
    {
        return $this->findOneBy([
            'email' => $email,
            'Status' => CustomerStatus::REGULAR,
        ]);
    }

    /**
     * 本会員をリセットキー、またはリセットキーとメールアドレスで検索する.
     *
     * @param string $resetKey
     * @param string|null $email
     *
     * @return Customer|null 見つからない場合はnullを返す.
     */
    public function getRegularCustomerByResetKey($resetKey, $email = null)
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.reset_key = :reset_key AND c.Status = :status AND c.reset_expire >= :reset_expire')
            ->setParameter('reset_key', $resetKey)
            ->setParameter('status', CustomerStatus::REGULAR)
            ->setParameter('reset_expire', new \DateTime());

        if ($email) {
            $qb
                ->andWhere('c.email = :email')
                ->setParameter('email', $email);
        }

        return $qb->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * リセット用パスワードを生成する.
     *
     * @return string
     */
    public function getResetPassword()
    {
        return StringUtil::random(8);
    }

    /**
     * 仮会員, 本会員の会員を返す.
     * Eccube\Entity\CustomerのUniqueEntityバリデーションで使用しています.
     *
     * @param array<string, mixed> $criteria
     *
     * @return array<int, Customer>
     */
    public function getNonWithdrawingCustomers(array $criteria = [])
    {
        $criteria['Status'] = [
            CustomerStatus::PROVISIONAL,
            CustomerStatus::REGULAR,
        ];

        return $this->findBy($criteria);
    }
}
