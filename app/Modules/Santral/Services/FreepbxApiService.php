<?php
namespace App\Modules\Santral\Services;

use GuzzleHttp\Client;

class FreepbxApiService implements CdrProviderInterface
{
    protected $client;
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('FREEPBX_API_URL', 'https://santral.randevumcepte.com.tr/monitor/api/freepbxapi.php');
        $this->client = new Client(['base_uri' => $this->baseUrl, 'timeout' => 10]);
    }

    public function getCdrs(array $params)
    {
        $response = $this->client->get('', ['query' => $params]);
        return json_decode($response->getBody(), true);
    }
}
