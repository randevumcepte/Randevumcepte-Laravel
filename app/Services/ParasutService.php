<?php

namespace App\Services;

use App\SmsPaketSiparisi;
use Illuminate\Support\Facades\Log;

/**
 * Paraşüt e-Arşiv fatura entegrasyonu (İnteraktif Duyuru Paketi satışları için).
 *
 * Kimlik bilgileri config/parasut.php (prod .env) üzerinden okunur. Bilgiler eksikse
 * aktifMi() false döner ve servis sessizce no-op olur (ödeme akışı etkilenmez).
 *
 * NOT: Kimlik bilgileri sağlandıktan sonra (yarın), test_mode ödeme ile uçtan uca
 * doğrulanmalı; Paraşüt payload alanları gerçek hesapla teyit edilmelidir.
 */
class ParasutService
{
    protected $cfg;

    public function __construct()
    {
        $this->cfg = config('parasut');
    }

    /** Kimlik bilgileri tam mı? Değilse fatura kesimi atlanır. */
    public function aktifMi()
    {
        foreach (['client_id', 'client_secret', 'username', 'password', 'company_id'] as $k) {
            if (empty($this->cfg[$k])) return false;
        }
        return true;
    }

    /** OAuth2 (password grant) ile access token alır. Başarısızsa null. */
    protected function token()
    {
        $post = http_build_query([
            'grant_type'    => 'password',
            'client_id'     => $this->cfg['client_id'],
            'client_secret' => $this->cfg['client_secret'],
            'username'      => $this->cfg['username'],
            'password'      => $this->cfg['password'],
        ]);

        $res = $this->httpPost($this->cfg['token_url'], $post, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);

        if (isset($res['access_token'])) return $res['access_token'];
        Log::error('Parasut token alinamadi: '.json_encode($res));
        return null;
    }

    /**
     * Bir duyuru paketi siparişi için e-Arşiv fatura keser.
     * Sipariş ödenmiş (durum=1) ve henüz faturalanmamış olmalıdır.
     */
    public function duyuruPaketiFaturasiKes(SmsPaketSiparisi $siparis)
    {
        if ($siparis->fatura_durumu == 1) return; // zaten kesilmiş
        if (!$this->aktifMi()) {
            Log::info('Parasut pasif (kimlik bilgisi yok) — fatura atlandi. Siparis: '.$siparis->merchant_oid);
            return;
        }

        $token = $this->token();
        if (!$token) {
            $siparis->fatura_durumu = 2;
            $siparis->save();
            return;
        }

        // Fiyatlar KDV dahil → matrah = tutar / (1 + kdv/100)
        $kdv    = (int) $this->cfg['kdv_orani'];
        $tutar  = (float) $siparis->tutar;
        $matrah = round($tutar / (1 + $kdv / 100), 2);

        $base  = rtrim($this->cfg['base_url'], '/').'/'.$this->cfg['company_id'];
        $hdr   = [
            'Authorization: Bearer '.$token,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        try {
            // 1) Müşteri (contact) oluştur
            $contactPayload = [
                'data' => [
                    'type' => 'contacts',
                    'attributes' => [
                        'name'         => $siparis->fatura_unvan ?: ('Salon #'.$siparis->salon_id),
                        'account_type' => 'customer',
                        'tax_number'   => $siparis->fatura_vkn,
                        'tax_office'   => $siparis->fatura_vergi_dairesi,
                    ],
                ],
            ];
            $contact = $this->httpPost($base.'/contacts', json_encode($contactPayload), $hdr);
            $contactId = $contact['data']['id'] ?? null;
            if (!$contactId) {
                throw new \Exception('contact olusturulamadi: '.json_encode($contact));
            }

            // 2) Satış faturası oluştur (inline ürün satırı)
            $invoicePayload = [
                'data' => [
                    'type' => 'sales_invoices',
                    'attributes' => [
                        'item_type'   => 'invoice',
                        'description' => $this->cfg['urun_adi'].' - '.$siparis->sms_adet.' SMS',
                        'issue_date'  => date('Y-m-d'),
                        'currency'    => 'TRL',
                    ],
                    'relationships' => [
                        'contact' => ['data' => ['type' => 'contacts', 'id' => $contactId]],
                        'details' => [
                            'data' => [[
                                'type' => 'sales_invoice_details',
                                'attributes' => [
                                    'quantity'   => 1,
                                    'unit_price' => $matrah,
                                    'vat_rate'   => $kdv,
                                    'description'=> $this->cfg['urun_adi'].' - '.$siparis->sms_adet.' SMS',
                                ],
                            ]],
                        ],
                    ],
                ],
            ];
            $invoice = $this->httpPost($base.'/sales_invoices', json_encode($invoicePayload), $hdr);
            $invoiceId = $invoice['data']['id'] ?? null;
            if (!$invoiceId) {
                throw new \Exception('sales_invoice olusturulamadi: '.json_encode($invoice));
            }

            // 3) e-Arşiv'e dönüştür
            $eArsivPayload = [
                'data' => [
                    'type' => 'e_archives',
                    'relationships' => [
                        'sales_invoice' => ['data' => ['type' => 'sales_invoices', 'id' => $invoiceId]],
                    ],
                ],
            ];
            $earsiv = $this->httpPost($base.'/sales_invoices/'.$invoiceId.'/e_archives', json_encode($eArsivPayload), $hdr);

            $siparis->fatura_durumu = 1;
            $siparis->fatura_no  = $invoiceId;
            $siparis->fatura_url = 'https://uygulama.parasut.com/'.$this->cfg['company_id'].'/satislar/'.$invoiceId;
            $siparis->save();
            Log::info('Parasut e-Arsiv fatura kesildi. Siparis: '.$siparis->merchant_oid.' Fatura: '.$invoiceId);

        } catch (\Throwable $e) {
            $siparis->fatura_durumu = 2;
            $siparis->save();
            Log::error('Parasut fatura kesimi hatasi ('.$siparis->merchant_oid.'): '.$e->getMessage());
        }
    }

    /** Basit cURL POST yardımcısı; JSON yanıtı diziye çevirir. */
    protected function httpPost($url, $body, array $headers)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        $res = curl_exec($ch);
        if (curl_errno($ch)) {
            $err = curl_error($ch);
            curl_close($ch);
            Log::error('Parasut baglanti hatasi: '.$err);
            return [];
        }
        curl_close($ch);
        return json_decode($res, true) ?: [];
    }
}
