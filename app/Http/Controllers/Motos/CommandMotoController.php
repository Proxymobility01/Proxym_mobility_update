<?php

namespace App\Http\Controllers\Motos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CommandMotoService;
use Illuminate\Support\Facades\Log;

class CommandMotoController extends Controller
{
    public function __construct(private CommandMotoService $svc) {}

    public function state(int $id)
    {
        try {
            $state = $this->svc->getStateByMotoId($id); // 1,0,-1
            return response()->json(['ok' => true, 'state' => $state]);
        } catch (\Throwable $e) {
            Log::error('State endpoint error', ['moto_id' => $id, 'err' => $e->getMessage()]);
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function toggle(int $id)
    {
        try {
            $state = $this->svc->toggleByMotoId($id);
            return response()->json(['ok' => true, 'state' => $state]);
        } catch (\Throwable $e) {
            Log::error('Toggle endpoint error', ['moto_id' => $id, 'err' => $e->getMessage()]);
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function set(Request $req, int $id)
    {
        $req->validate(['on' => 'required|boolean']);
        try {
            $state = $this->svc->setStateByMotoId($id, (bool)$req->boolean('on'));
            return response()->json(['ok' => true, 'state' => $state]);
        } catch (\Throwable $e) {
            Log::error('Set endpoint error', ['moto_id' => $id, 'err' => $e->getMessage()]);
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // Diagnostics
    public function probeLogin()
    {
        return response()->json($this->svc->probeLogin());
    }
    public function probeFleet()
    {
        return response()->json($this->svc->probeFleet());
    }
}
