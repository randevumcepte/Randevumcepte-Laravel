<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Bildirimler;


class BildirimController extends Controller
{
	public function fcmTokenAl($firebasePath)
    {
        $jsonPath = storage_path($firebasePath);
        $json = json_decode(file_get_contents($jsonPath), true);
    
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];
    
        $now = time();
        $claim = [
            'iss'   => $json['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => $json['token_uri'],
            'iat'   => $now,
            'exp'   => $now + 3600
        ];
    
        $base64url = function ($data) {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        };
    
        $jwtHeader = $base64url(json_encode($header));
        $jwtClaim  = $base64url(json_encode($claim));
    
        $data = $jwtHeader . '.' . $jwtClaim;
    
        $privateKey = $json['private_key'];
        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
    
        $jwt = $data . '.' . $base64url($signature);
    
        $client = new Client();
        $response = $client->post($json['token_uri'], [
            'form_params' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt
            ]
        ]);
    
        if ($response->getStatusCode() === 200) {
            $body = json_decode($response->getBody()->getContents(), true);
            return $body['access_token'];
        }
    
        throw new \Exception('Firebase access token alınamadı: ' . $response->getBody()->getContents());
    }
    public function bildirimKaydet($userId,$salonId,$baslik,$mesajlar,$url,$img,$butonlar,$olcumId,$ilacId,$adetId,$randevuId)
    {
        $bildirim = new Bildirimler();
        if (is_string($butonlar)) {
            $butonlar = json_decode($butonlar, true);
        }
        $bildirim->salon_id = $salonId;
        $bildirim->user_id = $userId;
        $bildirim->baslik = $baslik;
        $bildirim->aciklama = $mesajlar;
        $bildirim->url =$url;
        $bildirim->img_src=$img;
        $bildirim->tarih_saat = date('Y-m-d H:i:s');
        $bildirim->okundu = false;
        $bildirim->butonlar = json_encode($butonlar,JSON_UNESCAPED_UNICODE);
        $bildirim->olcum_id = $olcumId;
        $bildirim->ilac_id= $ilacId;
        $bildirim->adet_duzeni_id= $adetId;
        $bildirim->randevu_id = $randevuId;
        $bildirim->save();
    }
       public function bildirimGonder(
            $firebaseJsonFile,
            $deviceToken,
            $title,
            $body,
           
            $data,
            $salonId,
            $userId,
            $bildirimImg,
            $bildirimUrl,
            $olcumId,
            $ilacId,
            $adetId,
            $randevuId
        ) {
            try {

                // access token alırken kendi methodunu kullan
                $accessToken = $this->fcmTokenAl($firebaseJsonFile);

                $projectId = json_decode(file_get_contents(storage_path($firebaseJsonFile)), true)['project_id'];
                $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
               

                     $message = [
        'message' => [
            'token' => $deviceToken,

            'data' => [
                'title' => $title,
                'body' => $body,
                'category' => $data['category'] ?? '',
                'buttons' => $data['buttons'] ?? '',
                'userInfo'=> $data['userInfo'] ?? '',
                'olcum'   => $data['olcum'] ?? '',
                'sound'   => 'ring', // ÖNEMLİ
            ],

            // Android custom sound
            'android' => [
                'priority' => 'HIGH',
               
            ],

            // iOS custom sound
            'apns' => [
                'headers' => [
                    'apns-priority' => '10'
                ],
                'payload' => [
                    'aps' => [
                        'sound' => 'ring.mp3',
                        'badge' => 1,
                        'category' => $data['category'] ?? ''
                    ]
                ]
            ]
        ]
    ];


                Log::info(json_encode($message));
                $client = new Client();
                $response = $client->post($url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type'  => 'application/json'
                    ],
                    'json' => $message
                ]);

                if ($response->getStatusCode() !== 200) {
                    Log::error('FCM gönderim HTTP kodu: ' . $response->getStatusCode() . ' - ' . $response->getBody()->getContents());
                    throw new \Exception('FCM mesaj gönderilemedi: ' . $response->getBody()->getContents());
                }
                self::bildirimKaydet($userId,$salonId,$title,$body,$bildirimUrl,$bildirimImg,$data['buttons'],$olcumId,$ilacId,$adetId,$randevuId);
                return json_decode($response->getBody()->getContents(), true);
            } catch (\Throwable $e) {
                Log::error('bildirimGonder hata: ' . $e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine());
                throw $e;
            }
        }
}