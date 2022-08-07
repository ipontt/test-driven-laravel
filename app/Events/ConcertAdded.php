<?php

namespace App\Events;

use App\Models\Concert;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConcertAdded
{
	use Dispatchable;
	use InteractsWithSockets;
	use SerializesModels;

	public function __construct(public readonly Concert $concert) {}
}
