<?php

namespace App\View\Components\Backstage;

use App\Models\Concert;
use Illuminate\View\Component;
use Illuminate\View\View;

use function view;

class ConcertCard extends Component
{
	public function __construct(public Concert $concert) {}

	public function render(): View
	{
		return view('components.backstage.concert-card');
	}
}
