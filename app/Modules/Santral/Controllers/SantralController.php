<?php
namespace App\Modules\Santral\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Santral\Services\CdrProviderInterface;
use Illuminate\Support\Facades\Auth;

class SantralController extends Controller
{
    protected $cdrProvider;

    public function __construct(CdrProviderInterface $cdrProvider)
    {
        $this->cdrProvider = $cdrProvider;
    }

    public function getCdr(Request $request)
    {
        // Token kontrolü (Flutter / API)
        $token = $request->header('Authorization') ?? $request->input('token');
        $isApi = $token === 'Bearer ' . env('SANTRAL_API_TOKEN', 'secret123');

        // Web guard kontrolü
        if (!$isApi) {
            if (!Auth::guard('isletmeyonetim')->check() && !Auth::guard('satisortakligi')->check()) {
                return redirect('/isletmeyonetim/girisyap');
            }
        }

        $params = $request->only(['tarih1','tarih2','dahililer','operatorKanali','trunk','limit','offset']);
        $cdrs = $this->cdrProvider->getCdrs($params);

        return $isApi ? response()->json($cdrs) : view('isletmeadmin.santral', compact('cdrs'));
    }
}
