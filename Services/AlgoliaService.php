<?php

namespace SwAlgolia\Services;

use AlgoliaSearch\Client as AlgoliaClient;
use AlgoliaSearch\Index;
use Shopware\Components;
use Shopware\Models\Shop\Shop;

/**
 * Class AlgoliaService.
 */
class AlgoliaService
{
    /**
     * @var Components\Logger
     */
    private $logger;

    /**
     * @var null|array
     */
    private $pluginConfig;

    /**
     * AlgoliaService constructor.
     *
     * @param Components\Logger $logger
     */
    public function __construct(Components\Logger $logger)
    {
        // Init Logger
        $this->logger = $logger;

        // Grab the plugin config
        $this->pluginConfig = Shopware()->Container()->get('shopware.plugin.cached_config_reader')->getByPluginName('SwAlgolia');
    }

    /**
     * Short test method for this and that.
     */
    public function test()
    {
        // @TODO remove this function on release

        // Init the API client
        $client = new AlgoliaClient($this->pluginConfig['algolia-application-id'], $this->pluginConfig['algolia-admin-api-key']);
        $client->setConnectTimeout($this->pluginConfig['algolia-connection-timeout']);

        $index = $client->initIndex('test-master');
        $response = $index->setSettings([
            'replicas' => ['test-replica-1', 'test-replica-2'],
        ]);

        // Wait for the task to be completed (to make sure replica indices are ready)
        $index->waitTask($response['taskID']);

        // Configure the replica indices
        $client->initIndex('test-replica-1')->setSettings([
            'ranking' => ['asc(price)', 'typo', 'geo', 'words', 'proximity', 'attribute', 'exact', 'custom'],
        ]);

        $client->initIndex('test-replica-1')->setSettings([
            'ranking' => ['desc(price)', 'typo', 'geo', 'words', 'proximity', 'attribute', 'exact', 'custom'],
        ]);

        $index->addObject(['key' => 'value']);
    }

    /**
     * Push a data array to an Algolia index.
     *
     * @param Shop  $shop
     * @param array $data
     * @param $indexName
     *
     * @return bool
     */
    public function push(Shop $shop, array $data, $indexName)
    {
        // Init the API client
        $client = new AlgoliaClient($this->pluginConfig['algolia-application-id'], $this->pluginConfig['algolia-admin-api-key']);
        $client->setConnectTimeout($this->pluginConfig['algolia-connection-timeout']);

        // Init the index
        $index = $client->initIndex($indexName);

        // Send data to algolia
        try {
            $response = $index->addObjects($data);
            $this->logger->addDebug('Successfully pushed elements {objectIds} for ShopId {shopId} to Algolia with TaskID {taskId}.', ['objectIds' => implode(', ', $response['objectIDs']), 'shopId' => $shop->getId(), 'taskId' => $response['taskID']]);

            return true;
        } catch (\Exception $e) {
            $this->logger->addError('Error when pushing elements to Algolia: {errorMessage}', ['errorMessage' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Initialize index.
     *
     * @param $indexName
     *
     * @return Index|bool
     */
    public function initIndex($indexName)
    {
        // Init the API client
        $client = new AlgoliaClient($this->pluginConfig['algolia-application-id'], $this->pluginConfig['algolia-admin-api-key']);
        $client->setConnectTimeout($this->pluginConfig['algolia-connection-timeout']);

        // Init the index
        try {
            $index = $client->initIndex($indexName);
            $this->logger->addDebug('Successfully initialized index {indexName}.', ['indexName' => $indexName]);

            return $index;
        } catch (\Exception $e) {
            $this->logger->addDebug('Error while initializing index {indexName}: {errorMessage}.', ['indexName' => $indexName, 'errorMessage' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Pushes the index settings from plugin configuration to the Algolia index.
     *
     * @param array      $settings
     * @param Index|null $index
     * @param null       $indexName
     *
     * @throws \Exception
     *
     * @return bool|mixed
     */
    public function pushIndexSettings(array $settings, Index $index = null, $indexName = null)
    {
        if (!$index && !$indexName) {
            throw new \Exception('Either index or indexname has to be specified.');
        }

        // Get the index if only the index name is passed
        if (!$index) {
            $client = new AlgoliaClient($this->pluginConfig['algolia-application-id'], $this->pluginConfig['algolia-admin-api-key']);
            $client->setConnectTimeout($this->pluginConfig['algolia-connection-timeout']);
            $index = $client->initIndex($indexName);
        }

        // Push index settings to algolia
        try {
            $response = $index->setSettings($settings);
            $this->logger->addDebug('Successfully pushed index settings for index {indexName} to Algolia with TaskID {taskId}.', ['indexName' => $index->indexName, 'taskId' => $response['taskID']]);

            return $response;
        } catch (\Exception $e) {
            $this->logger->addError('Error while pushing index settings to Algolia: {errorMessage}', ['errorMessage' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Deletes an Algolia index and it´s replica indices by name.
     *
     * @param $indexName
     *
     * @return bool
     */
    public function deleteIndex($indexName)
    {
        $client = new AlgoliaClient($this->pluginConfig['algolia-application-id'], $this->pluginConfig['algolia-admin-api-key']);
        $client->setConnectTimeout($this->pluginConfig['algolia-connection-timeout']);

        // Delete index
        try {
            $client->deleteIndex($indexName);
            $this->logger->addDebug('Successfully deleted index {indexName}.', ['indexName' => $indexName]);

            return true;
        } catch (\Exception $e) {
            $this->logger->addError('Error when trying to delete the index {indexName}: {errorMessage}', ['indexName' => $indexName, 'errorMessage' => $e->getMessage()]);

            return false;
        }
    }
}
