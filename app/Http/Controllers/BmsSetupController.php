<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // ✅ Add this
use App\Models\BatteriesValide;

class BmsSetupController extends Controller
{
    public function index()
    {
        $batteries = BatteriesValide::select('id', 'mac_id')->get();
        return view('bms.setup', compact('batteries'));
    }

    public function send(Request $request)
    {
        // ✅ Step 1: Validate
        $validated = $request->validate([
            'batteries' => 'required|array|min:2',
            'params' => 'required|array',
        ]);

        Log::info('🔵 Validation passed', $validated);

        // ✅ Step 2: Get token (MDS)
        Log::info('🔵 Attempting to login to BMS API...');
        $loginResponse = Http::get('http://api.mtbms.com/api.php/ibms/loginSystem', [
            'LoginName'     => 'D13',
            'LoginPassword' => 'QC123456',
            'LoginType'     => 'ENTERPRISE',
            'language'      => 'cn',
            'ISMD5'         => 0,
            'timeZone'      => '+08',
            'apply'         => 'APP',
        ]);

        Log::info('🟢 Login response received', $loginResponse->json());

        $mds = $loginResponse['mds'] ?? null;

        if (!$mds) {
            Log::error('❌ Login to BMS API failed, no MDS received.');
            return redirect()->back()->with('response', [
                'errorDescribe' => '❌ Login to BMS API failed. No token received.',
            ]);
        }

        // ✅ Step 3: Send BMS parameters
        $numbers = implode(';', $validated['batteries']);
        $bmsparam = json_encode($validated['params']);

        Log::info('🟡 Sending BMS parameters', [
            'numbers' => $numbers,
            'bmsparam' => $bmsparam,
            'mds' => $mds
        ]);

        $response = Http::post('http://api.mtbms.com/api.php/ibms/getDateFunc', [
            'method'   => 'SendBmsCommands',
            'numbers'  => $numbers,
            'bmsparam' => $bmsparam,
            'mds'      => $mds,
        ]);

        Log::info('🟢 BMS command response received', $response->json());

        if (!$response->successful()) {
            Log::error('❌ Error contacting BMS server', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return redirect()->back()->with('response', [
                'errorDescribe' => '❌ Failed to contact BMS server.',
            ]);
        }

        return redirect()->back()->with('response', $response->json());
    }
}
