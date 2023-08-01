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

namespace Cozmot\ChatGpt\Model\Query;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Cozmot\ChatGpt\Helper\Data as HelperData;

class Completions
{
    public const OPENAI_API_COMPLETION_ENDPOINT = 'https://api.openai.com/v1/completions';

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * @param Curl $curl
     * @param Json $json
     * @param HelperData $helper
     */
    public function __construct(
        Curl       $curl,
        Json       $json,
        HelperData $helper
    )
    {
        $this->curl = $curl;
        $this->helper = $helper;
        $this->json = $json;
    }

    /**
     * Get curl object
     *
     * @return Curl
     */
    public function getCurlClient()
    {
        return $this->curl;
    }

    /**
     * Call OpenAI API request
     *
     * @param string $prompt
     * @param string $type
     * @return string
     * @throws QueryException
     */
    public function makeRequest($prompt, $type)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/AI.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        //$logger->info('makeRequest called: ');
        $this->setHeaders();
        $this->getCurlClient()->post(
            self::OPENAI_API_COMPLETION_ENDPOINT,
            $this->getPayload($prompt, $type)
        );
        $logger->info('makeRequest called: ' . $prompt);
        return $this->validateResponse();
    }

    /**
     * Set API header
     *
     * @return void
     * @throws QueryException
     */
    protected function setHeaders()
    {
        $token = $this->helper->getApiSecret();
        if (!$token) {
            throw new QueryException(__('API Secret not found. Please check configuration'));
        }
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->helper->getApiSecret()
        ];
        $this->getCurlClient()->setHeaders($headers);
    }

    /**
     * Get API payload
     *
     * @param string $prompt
     * @param string $type
     * @return string
     */
    protected function getPayload($prompt, $type)
    {
        $maxToken = $this->helper->getMaxToken($type);
        $payload = [
            'model' => 'text-davinci-003',
            'prompt' => sprintf(
                "Create HTML description in %s words for product : %s",
                $maxToken,
                strip_tags($prompt)
            ),
            'n' => 1,
            'max_tokens' => $maxToken,
            'frequency_penalty' => 0,
            'presence_penalty' => 2
        ];
        return $this->json->serialize($payload);
    }

    /**
     * Verify API response
     *
     * @return string string
     * @throws QueryException
     */
    public function validateResponse()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/AI.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('validateResponse called: ');
        if ($this->getCurlClient()->getStatus() == 401) {
            throw new QueryException(__('Unauthorized response. Please check token.'));
        }
        $logger->info('Responsed called: ');
        if ($this->getCurlClient()->getStatus() >= 500) {
            throw new QueryException(__('Server error'));
        }
        $logger->info('Not 500: ');
        $response = $this->json->unserialize($this->getCurlClient()->getBody());
        if (isset($response['code'])) {
            if ($response['code'] === "insufficient_quota") {
                throw new QueryException(__('Qouta Exceed'));
                return $response;
            }
        }
        $logger->info('Response: ' . print_r($response, true));
        if (isset($response['error'])) {
            throw new QueryException(__($response['error']['message'] ?? 'Unknown Error'));
        }
        $logger->info('Not Error: ');
        if (!isset($response['choices'])) {
            throw new QueryException(__('No results found from API response'));
        }

        return trim($response['choices'][0]['text']);
    }
}
