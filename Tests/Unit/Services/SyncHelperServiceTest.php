<?php

namespace SwAlgolia\Tests\Unit\Services;

use Doctrine\ORM\EntityManager;
use Shopware\Models\Shop\Shop;

/**
 * Class SyncHelperServiceTest.
 */
class SyncHelperServiceTest extends BaseTest
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Shop
     */
    private $shop;

    /**
     * Set up unit test.
     */
    public function setUp()
    {
        parent::setUp();
        $this->em = Shopware()->Models();

        $repository = Shopware()->Container()->get('models')->getRepository(Shop::class);
        $this->shop = $repository->getActiveById(1);
    }

    /**
     * Test for building the index name.
     */
    public function testBuildIndexName()
    {
        $snycHelperService = Shopware()->Container()->get('sw_algolia.sync_helper_service');
        $indexName = $snycHelperService->buildIndexName($this->shop);

        // Do assertion tests
        $this->assertInstanceOf(Shop::class, $this->shop);
        $this->assertNotEmpty($indexName);
        $this->assertTrue(is_int(strpos($indexName, '_')));
        $this->assertInternalType('string', $indexName, 'Got a '.gettype($indexName).' instead of a string');
    }
}
