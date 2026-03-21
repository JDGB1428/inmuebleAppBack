<?php

namespace App\Events;


use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PropertyCreatedEvent implements ShouldBroadcastNow
{
   use Dispatchable, InteractsWithSockets, SerializesModels;

    public $property;
    public $userId;

    public function __construct($property, $userId)
    {
        $this->property = $property;
        $this->userId = $userId;
    }

    // A QUÉ CANAL PRIVADO SE ENVÍA
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->userId),
        ];
    }

    // EL NOMBRE DEL EVENTO QUE ESCUCHARÁ ANGULAR
    public function broadcastAs(): string
    {
        return 'PropertyCreatedEvent';
    }

    // LOS DATOS QUE LLEGAN A LA CAMPANITA
    public function broadcastWith(): array
    {
        return [
            'property_id' => $this->property->id,
            'title' => '¡Nuevo inmueble disponible!',
            'address' => $this->property->address ?? 'Nueva dirección'
        ];
    }
}
