<?php

namespace App\Service;

use App\Entity\Commande;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PaydunyaService
{
    private const SANDBOX_URL = 'https://app.paydunya.com/sandbox-api/v1';
    private const LIVE_URL    = 'https://app.paydunya.com/api/v1';

    public function __construct(
        private readonly HttpClientInterface $http,
        private readonly string $masterKey,
        private readonly string $privateKey,
        private readonly string $publicKey,
        private readonly string $token,
        private readonly string $mode,
    ) {}

    public function isConfigured(): bool
    {
        return $this->masterKey !== 'your-master-key' && $this->masterKey !== '';
    }

    private function baseUrl(): string
    {
        return $this->mode === 'live' ? self::LIVE_URL : self::SANDBOX_URL;
    }

    private function headers(): array
    {
        return [
            'PAYDUNYA-MASTER-KEY'  => $this->masterKey,
            'PAYDUNYA-PRIVATE-KEY' => $this->privateKey,
            'PAYDUNYA-PUBLIC-KEY'  => $this->publicKey,
            'PAYDUNYA-TOKEN'       => $this->token,
            'Content-Type'         => 'application/json',
        ];
    }

    /**
     * Crée une facture PayDunya et retourne ['token' => '...', 'invoice_url' => '...']
     * ou ['error' => '...'] en cas d'échec.
     */
    public function createInvoice(
        Commande $commande,
        string $returnUrl,
        string $cancelUrl,
        string $callbackUrl,
    ): array {
        try {
            $response = $this->http->request('POST', $this->baseUrl() . '/checkout-invoice/create', [
                'headers' => $this->headers(),
                'json' => [
                    'invoice' => [
                        'total_amount' => (int) $commande->getTotal(),
                        'description'  => 'Commande BeautyAffair #' . $commande->getId(),
                    ],
                    'store' => [
                        'name'           => 'BeautyAffair',
                        'tagline'        => 'Salon de beauté & Boutique',
                        'postal_address' => 'Cotonou, Bénin',
                    ],
                    'actions' => [
                        'cancel_url'   => $cancelUrl,
                        'return_url'   => $returnUrl,
                        'callback_url' => $callbackUrl,
                    ],
                    'custom_data' => [
                        'commande_id' => $commande->getId(),
                    ],
                ],
            ]);

            $data = $response->toArray(false);

            if (($data['response_code'] ?? '') !== '00') {
                return ['error' => $data['response_text'] ?? 'Erreur PayDunya'];
            }

            return [
                'token'       => $data['token'],
                'invoice_url' => $data['invoice_url'],
            ];
        } catch (\Throwable $e) {
            return ['error' => 'Impossible de joindre PayDunya : ' . $e->getMessage()];
        }
    }

    /**
     * Vérifie le statut d'un paiement par son token PayDunya.
     * Retourne 'completed' si payé, 'pending', 'cancelled', ou 'failed'.
     */
    public function verifyPayment(string $token): string
    {
        try {
            $response = $this->http->request(
                'GET',
                $this->baseUrl() . '/checkout-invoice/confirm/' . $token,
                ['headers' => $this->headers()]
            );

            $data = $response->toArray(false);

            return $data['invoice']['status'] ?? 'failed';
        } catch (\Throwable) {
            return 'failed';
        }
    }
}
