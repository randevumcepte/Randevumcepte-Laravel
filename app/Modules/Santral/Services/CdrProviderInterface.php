<?php
namespace App\Modules\Santral\Services;

interface CdrProviderInterface
{
    public function getCdrs(array $params);
}
