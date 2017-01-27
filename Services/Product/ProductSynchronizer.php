<?php

namespace SwAlgolia\Services\Product;

use SwAlgolia\Services\LastIdQuery;
use SwAlgolia\Structs\Backlog;
use SwAlgolia\Structs\ShopIndex;
use SwAlgolia\Subscriber\ORMBacklogSubscriber;
use SwAlgolia\Services\SynchronizerInterface;

class ProductSynchronizer implements SynchronizerInterface
{
    const LIMIT = 100;

    /**
     * @var ProductIndexer
     */
    private $productIndexer;

    /**
     * @var ProductQueryFactoryInterface
     */
    private $queryFactory;

    /**
     * @param ProductQueryFactoryInterface $queryFactory
     * @param ProductIndexer               $productIndexer
     */
    public function __construct(
        ProductQueryFactoryInterface $queryFactory,
        ProductIndexer $productIndexer
    ) {
        $this->productIndexer = $productIndexer;
        $this->queryFactory = $queryFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function synchronize(ShopIndex $shopIndex, $backlogs)
    {
        $numbers = $this->getBacklogNumbers($backlogs);
        $queries = $this->getBacklogQueries($backlogs);

        $this->productIndexer->indexProducts($shopIndex, $numbers);
        foreach ($queries as $query) {
            while ($queryNumbers = $query->fetch()) {
                $this->productIndexer->indexProducts($shopIndex, $queryNumbers);
            }
        }
    }

    /**
     * @param Backlog[] $backlogs
     *
     * @return string[]
     */
    private function getBacklogNumbers($backlogs)
    {
        $numbers = [];
        foreach ($backlogs as $backlog) {
            $payload = $backlog->getPayload();
            switch ($backlog->getEvent()) {
                case ORMBacklogSubscriber::EVENT_ARTICLE_DELETED:
                case ORMBacklogSubscriber::EVENT_ARTICLE_INSERTED:
                case ORMBacklogSubscriber::EVENT_ARTICLE_UPDATED:
                    $query = $this->queryFactory->createProductIdQuery([$payload['id']]);
                    $numbers = array_merge($numbers, $query->fetch());
                    break;

                case ORMBacklogSubscriber::EVENT_VOTE_DELETED:
                case ORMBacklogSubscriber::EVENT_VOTE_INSERTED:
                case ORMBacklogSubscriber::EVENT_VOTE_UPDATED:
                    $query = $this->queryFactory->createProductIdQuery([$payload['articleId']]);
                    $numbers = array_merge($numbers, $query->fetch());
                    break;

                case ORMBacklogSubscriber::EVENT_VARIANT_DELETED:
                case ORMBacklogSubscriber::EVENT_VARIANT_INSERTED:
                case ORMBacklogSubscriber::EVENT_VARIANT_UPDATED:
                    $numbers[] = $payload['number'];
                    break;

                case ORMBacklogSubscriber::EVENT_PRICE_DELETED:
                case ORMBacklogSubscriber::EVENT_PRICE_INSERTED:
                case ORMBacklogSubscriber::EVENT_PRICE_UPDATED:
                    $numbers[] = $payload['number'];
                    break;
            }
        }

        return array_unique(array_filter($numbers));
    }

    /**
     * @param Backlog[] $backlogs
     *
     * @return LastIdQuery[]
     */
    private function getBacklogQueries($backlogs)
    {
        $queries = [];
        foreach ($backlogs as $backlog) {
            $payload = $backlog->getPayload();
            switch ($backlog->getEvent()) {
                case ORMBacklogSubscriber::EVENT_SUPPLIER_DELETED:
                case ORMBacklogSubscriber::EVENT_SUPPLIER_INSERTED:
                case ORMBacklogSubscriber::EVENT_SUPPLIER_UPDATED:
                    $queries[] = $this->queryFactory->createManufacturerQuery([$payload['id']], self::LIMIT);
                    break;
                case ORMBacklogSubscriber::EVENT_TAX_DELETED:
                case ORMBacklogSubscriber::EVENT_TAX_INSERTED:
                case ORMBacklogSubscriber::EVENT_TAX_UPDATED:
                    $queries[] = $this->queryFactory->createTaxQuery([$payload['id']], self::LIMIT);
                    break;
                case ORMBacklogSubscriber::EVENT_UNIT_DELETED:
                case ORMBacklogSubscriber::EVENT_UNIT_INSERTED:
                case ORMBacklogSubscriber::EVENT_UNIT_UPDATED:
                    $queries[] = $this->queryFactory->createUnitIdQuery([$payload['id']], self::LIMIT);
                    break;
                case ORMBacklogSubscriber::EVENT_PROPERTY_GROUP_DELETED:
                case ORMBacklogSubscriber::EVENT_PROPERTY_GROUP_INSERTED:
                case ORMBacklogSubscriber::EVENT_PROPERTY_GROUP_UPDATED:
                    $queries[] = $this->queryFactory->createPropertyGroupQuery([$payload['id']], self::LIMIT);
                    break;
                case ORMBacklogSubscriber::EVENT_PROPERTY_OPTION_DELETED:
                case ORMBacklogSubscriber::EVENT_PROPERTY_OPTION_INSERTED:
                case ORMBacklogSubscriber::EVENT_PROPERTY_OPTION_UPDATED:
                    $queries[] = $this->queryFactory->createPropertyOptionQuery([$payload['id']], self::LIMIT);
                    break;
            }
        }

        return $queries;
    }
}