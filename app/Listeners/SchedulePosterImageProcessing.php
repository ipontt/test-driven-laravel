<?php

namespace App\Listeners;

use App\Events\ConcertAdded;
use App\Jobs\ProcessPosterImage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SchedulePosterImageProcessing
{
	public function handle(ConcertAdded $event): void
	{
		ProcessPosterImage::dispatchIf(boolean: $event->concert->poster_image_path, concert: $event->concert);
	}
}
