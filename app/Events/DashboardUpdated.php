<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Facades\Log;

class DashboardUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;

        // ✅ Log dès la construction
        Log::info('📥 DashboardUpdated __construct - Données reçues pour broadcast :', [
            'keys' => array_keys($data),
            'sample' => array_slice($data, 0, 2), // log un échantillon
        ]);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('dashboard'); // doit matcher Echo.private('dashboard')
    }

    public function broadcastAs()
    {
        return 'dashboard.update'; // doit matcher le frontend `.listen('.dashboard.update')`
    }

    public function broadcastWith()
    {
        Log::info('📤 DashboardUpdated broadcastWith - Données envoyées au frontend :', $this->data);

        return ['data' => $this->data]; // ne retourne pas `null`
    }
}
