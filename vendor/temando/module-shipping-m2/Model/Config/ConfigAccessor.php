<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Model\Config;

use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Config Accessor
 *
 * @package Temando\Shipping\Model
 * @author  Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ConfigAccessor
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ConfigTypeInterface
     */
    private $systemConfigType;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ConfigAccessor constructor.
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     * @param ConfigTypeInterface $systemConfigType
     * @param LoggerInterface $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        ConfigTypeInterface $systemConfigType,
        LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->systemConfigType = $systemConfigType;
        $this->logger = $logger;
    }

    /**
     * Save config value to storage.
     *
     * @param string $path
     * @param string $value
     * @param mixed $scopeId
     * @return void
     */
    public function saveConfigValue($path, $value, $scopeId = 0)
    {
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

        if ($scopeId) {
            try {
                $scope = ScopeInterface::SCOPE_STORES;
                $scopeId = $this->storeManager->getStore($scopeId)->getId();
            } catch (NoSuchEntityException $exception) {
                $this->logger->warning(__('No store found for scope ID %1', $scopeId), ['exception' => $exception]);
                return;
            }
        }

        $this->configWriter->save($path, $value, $scope, $scopeId);
        $this->systemConfigType->clean();
    }

    /**
     * Delete config value from storage.
     *
     * @param string $path
     * @param mixed $scopeId
     * @return void
     */
    public function deleteConfigValue($path, $scopeId = 0)
    {
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

        if ($scopeId) {
            try {
                $scope = ScopeInterface::SCOPE_STORES;
                $scopeId = $this->storeManager->getStore($scopeId)->getId();
            } catch (NoSuchEntityException $exception) {
                $this->logger->warning(__('No store found for scope ID %1', $scopeId), ['exception' => $exception]);
                return;
            }
        }

        $this->configWriter->delete($path, $scope, $scopeId);
        $this->systemConfigType->clean();
    }

    /**
     * Read config value from storage.
     *
     * @param string $path
     * @param int $scopeId
     * @return mixed
     */
    public function getConfigValue($path, $scopeId = null)
    {
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        if ($scopeId) {
            $scope = ScopeInterface::SCOPE_STORE;
        }

        return $this->scopeConfig->getValue($path, $scope, $scopeId);
    }
}
