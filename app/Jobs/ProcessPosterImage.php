<?php

namespace App\Jobs;

use App\Models\Concert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Intervention\Image\Facades\Image;

class ProcessPosterImage implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function __construct(public readonly Concert $concert) {}

	public function handle(): void
	{
		$imageContents = Storage::disk('public')->get(path: $this->concert->poster_image_path);
		$image = Image::make(data: $imageContents);

		$image->resize(600, null, fn ($constraint) => $constraint->aspectRatio())->limitColors(255)->encode();

		Storage::disk('public')->put(
			path: $this->concert->poster_image_path,
			contents: (string) $image,
		);
	}
}
