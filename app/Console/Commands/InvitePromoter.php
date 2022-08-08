<?php

namespace App\Console\Commands;

use App\Mail\InvitationEmail;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InvitePromoter extends Command
{
	protected $signature = 'invite-promoter {email}';

	protected $description = 'Invites a promoter passing in an email';

	public function handle(): int
	{
		$this->validateEmail();

		$invitation = Invitation::create([
			'code' => Str::uuid(),
			'email' => $this->argument('email'),
		])->send();

		return 0;
	}

	private function validateEmail(): void
	{
		$validator = Validator::make(
			data: ['email' => $this->argument('email')],
			rules: ['email' => ['required', 'email', Rule::unique(User::class), Rule::unique(Invitation::class)]],
		);

		if ($validator->fails()) {
			$this->error($validator->errors()->first());

			exit;
		}
	}
}
