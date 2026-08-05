<?php
/**
 * PlugnPay Remote API client for Smart Screens admin ops (mark / void / return).
 *
 * Checkout itself is hosted at /pay/; this client is used only for post-order
 * capture, void, and refund from Zen Cart admin.
 *
 * @copyright Copyright (c) PlugnPay Technologies
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @see https://docs.plugnpay.com/docs/integration-specifications-documents/remote-api-integration-specification/
 */

declare(strict_types=1);

class PnPSs2Api
{
    public const ENDPOINT = 'https://pay1.plugnpay.com/payment/pnpremote.cgi';

    private string $publisherName;
    private string $publisherPassword;
    private ?PnPSs2Logger $logger;
    private string $lastRawResponse = '';
    private int $commErrNo = 0;
    private string $commError = '';
    /** @var array<string, mixed> */
    private array $commInfo = [];

    public function __construct(string $publisherName, string $publisherPassword, ?PnPSs2Logger $logger = null)
    {
        $this->publisherName = $publisherName;
        $this->publisherPassword = $publisherPassword;
        $this->logger = $logger;
    }

    public function getLastRawResponse(): string
    {
        return $this->lastRawResponse;
    }

    public function getCommError(): string
    {
        return $this->commError;
    }

    public function getCommErrNo(): int
    {
        return $this->commErrNo;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCommInfo(): array
    {
        return $this->commInfo;
    }

    /**
     * Mark (capture / settle) a prior authorization.
     *
     * @return array<string, string>
     */
    public function mark(string $orderId, string $amount): array
    {
        return $this->request([
            'mode' => 'mark',
            'orderID' => $orderId,
            'card-amount' => $amount,
        ]);
    }

    /**
     * Void the most recent operation for an orderID.
     *
     * @return array<string, string>
     */
    public function void(string $orderId, string $amount): array
    {
        return $this->request([
            'mode' => 'void',
            'orderID' => $orderId,
            'card-amount' => $amount,
            'txn-type' => 'auth',
        ]);
    }

    /**
     * Return (refund) against a prior authorization.
     *
     * @return array<string, string>
     */
    public function returnFunds(string $orderId, string $amount): array
    {
        return $this->request([
            'mode' => 'return',
            'orderID' => $orderId,
            'card-amount' => $amount,
        ]);
    }

    /**
     * Query transaction history for an orderID.
     *
     * @return array<string, string>
     */
    public function query(string $orderId): array
    {
        return $this->request([
            'mode' => 'query_trans',
            'orderID' => $orderId,
        ]);
    }

    public function isApproved(array $response): bool
    {
        $final = strtolower((string)($response['FinalStatus'] ?? ''));
        $success = strtolower((string)($response['success'] ?? ''));

        return $final === 'success' || $success === 'yes';
    }

    /**
     * @param array<string, string|int|float> $fields
     * @return array<string, string>
     */
    public function request(array $fields): array
    {
        if (!function_exists('curl_init')) {
            $this->commErrNo = -1;
            $this->commError = 'PHP cURL extension is not available';
            $this->lastRawResponse = '';
            if ($this->logger) {
                $this->logger->log('cURL missing', ['error' => $this->commError]);
            }
            return [
                'FinalStatus' => 'problem',
                'success' => 'no',
                'MErrMsg' => $this->commError,
            ];
        }

        $payload = array_merge([
            'publisher-name' => $this->publisherName,
            'publisher-password' => $this->publisherPassword,
            'client' => 'zencart_ss2',
        ], $fields);

        foreach ($payload as $key => $value) {
            if ($value === null || $value === '') {
                unset($payload[$key]);
            }
        }

        $body = http_build_query($payload);

        if ($this->logger) {
            $this->logger->log('Request to PlugnPay Remote API', [
                'endpoint' => self::ENDPOINT,
                'fields' => $payload,
            ]);
        }

        $ch = curl_init(self::ENDPOINT);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);

        $raw = curl_exec($ch);
        $this->commErrNo = (int)curl_errno($ch);
        $this->commError = (string)curl_error($ch);
        $this->commInfo = curl_getinfo($ch) ?: [];
        curl_close($ch);

        $this->lastRawResponse = is_string($raw) ? $raw : '';

        if ($this->lastRawResponse === '' || $this->commErrNo !== 0) {
            $response = [
                'FinalStatus' => 'problem',
                'success' => 'no',
                'MErrMsg' => $this->commError !== ''
                    ? $this->commError
                    : 'Empty response from PlugnPay (check cURL connectivity / firewall)',
            ];
            if ($this->logger) {
                $this->logger->log('Communication failure', [
                    'errno' => $this->commErrNo,
                    'error' => $this->commError,
                    'info' => $this->commInfo,
                ]);
            }
            return $response;
        }

        $response = $this->parseResponse($this->lastRawResponse);

        if ($this->logger) {
            $this->logger->log('Response from PlugnPay Remote API', [
                'FinalStatus' => $response['FinalStatus'] ?? '',
                'success' => $response['success'] ?? '',
                'orderID' => $response['orderID'] ?? ($response['orderid'] ?? ''),
                'auth-code' => $response['auth-code'] ?? ($response['auth_code'] ?? ''),
                'resp-code' => $response['resp-code'] ?? ($response['resp_code'] ?? ''),
                'MErrMsg' => $response['MErrMsg'] ?? '',
            ]);
        }

        return $response;
    }

    /**
     * @return array<string, string>
     */
    private function parseResponse(string $raw): array
    {
        $parsed = [];
        parse_str($raw, $parsed);

        $out = [];
        foreach ($parsed as $key => $value) {
            if (is_array($value)) {
                $out[(string)$key] = implode(',', $value);
            } else {
                $out[(string)$key] = (string)$value;
            }
        }

        return $out;
    }
}
