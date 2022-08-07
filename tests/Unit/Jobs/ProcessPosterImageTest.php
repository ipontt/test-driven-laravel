<?php

use App\Jobs\ProcessPosterImage;
use App\Models\Concert;

it('resizes the poster image to 600px wide', function () {
	Storage::fake(disk: 'public');
	Storage::disk('public')->put(
		path: 'posters/example-poster.png',
		contents: file_get_contents(base_path('tests/__fixtures__/full-size-poster.png')),
	);
	$concert = Concert::factory()->unpublished()->create(['poster_image_path' => 'posters/example-poster.png']);

	ProcessPosterImage::dispatch(concert: $concert);

	[$width, $height] = getimagesizefromstring(string: Storage::disk('public')->get(path: 'posters/example-poster.png'));
	expect($width)->toEqual(600);
	expect($height)->toEqual(777);

	$controlImageContents = file_get_contents(base_path('tests/__fixtures__/optimized-poster.png'));
	$resizedImageContents = Storage::disk('public')->get(path: 'posters/example-poster.png');

	expect($resizedImageContents)->toEqual($controlImageContents);
});

it('optimizes the poster image', function () {
	Storage::fake(disk: 'public');
	Storage::disk('public')->put(
		path: 'posters/example-poster.png',
		contents: file_get_contents(base_path('tests/__fixtures__/small-unoptimized-poster.png')),
	);
	$concert = Concert::factory()->unpublished()->create(['poster_image_path' => 'posters/example-poster.png']);

	ProcessPosterImage::dispatch(concert: $concert);

	$originalSize = filesize(base_path('tests/__fixtures__/small-unoptimized-poster.png'));
	$optimizedSize = Storage::disk('public')->size(path: 'posters/example-poster.png');

	expect($optimizedSize)->toBeLessThan($originalSize);

	$controlImageContents = file_get_contents(base_path('tests/__fixtures__/optimized-poster.png'));
	$optimizedImageContents = Storage::disk('public')->get(path: 'posters/example-poster.png');

	expect($optimizedImageContents)->toEqual($controlImageContents);
});
