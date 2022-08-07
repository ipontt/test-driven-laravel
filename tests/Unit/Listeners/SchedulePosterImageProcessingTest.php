<?php

use App\Events\ConcertAdded;
use App\Jobs\ProcessPosterImage;
use App\Listeners\SchedulePosterImageProcessing;
use App\Models\Concert;
use Illuminate\Support\Facades\Queue;

it('queues a job to process a poster image if a poster image is present', function () {
	Queue::fake();
	$concert = Concert::factory()->unpublished()->create(['poster_image_path' => 'posters/example-poster.png']);

	ConcertAdded::dispatch($concert);

	Queue::assertPushed(ProcessPosterImage::class, function (ProcessPosterImage $job) use ($concert) {
		return $job->concert->is($concert);
	});
});

it('does not queue a job to process a poster image if a poster image is not present', function () {
	Queue::fake();
	$concert = Concert::factory()->unpublished()->create(['poster_image_path' => null]);

	ConcertAdded::dispatch($concert);

	Queue::assertNothingPushed();
});
