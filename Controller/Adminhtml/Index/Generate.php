<?php
/**
 * Cozmot
 *
 * NOTICE OF LICENSE
 * This source file is subject to the cozmot.com license that is
 * available through the world-wide-web at this URL:
 * https://cozmot.com/end-user-license-agreement
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Commerce
 * @package     Module
 * @copyright   Copyright (c) Cozmot (https://cozmot.com/)
 * @license     https://cozmot.com/end-user-license-agreement
 *
 */

namespace Cozmot\ChatGpt\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Cozmot\ChatGpt\Helper\Data as HelperData;
use Cozmot\ChatGpt\Model\Query\completions;
use Cozmot\ChatGpt\Model\Query\QueryException;

class Generate extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Cozmot_ChatGpt::generate';

    /**
     * @var JsonFactory
     */
    protected $resultJson;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var completions
     */
    protected $queryCompletion;

    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * Generate constructor.
     *
     * @param Action\Context $context
     * @param JsonFactory $resultJson
     * @param ProductRepositoryInterface $productRepository
     * @param completions $queryCompletion
     * @param HelperData $helper
     */
    public function __construct(
        Action\Context             $context,
        JsonFactory                $resultJson,
        ProductRepositoryInterface $productRepository,
        completions                $queryCompletion,
        HelperData                 $helper
    )
    {
        $this->resultJson = $resultJson;
        $this->productRepository = $productRepository;
        $this->queryCompletion = $queryCompletion;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Generate Content
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/AI.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        //$logger->info('test');

        $response = ['error' => true, 'data' => 'unknown'];
        $isEnabled = $this->helper->isEnabled();
        if ($isEnabled) {
            try {
                $sku = $this->getRequest()->getParam('sku', false);
                if ($sku) {

                    $product = $this->productRepository->get($sku);
                    $attribute = $this->helper->getProductAttribute();
                    $queryValue = $product->getData($attribute);
                    $logger->info('Query sent: ' . print_r($queryValue, true));
                    if ($queryValue) {
                        $type = $this->getRequest()->getParam('type');
                        $data = $this->queryCompletion->makeRequest($queryValue, $type);
                        $logger->info('Query Data: ' . print_r($data, true));
                        $response = ['error' => false, 'data' => $data];
                    }
                }
            } catch (QueryException $e) {
                $response = ['error' => true, 'data' => $e->getMessage()];
            } catch (\Exception $e) {
                $response = ['error' => true, 'data' => $e->getMessage()];
            }
        }

        $resultJson = $this->resultJson->create();
        return $resultJson->setData($response);
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
