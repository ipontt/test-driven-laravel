<?php

use App\Models\Invitation;

it('has been used if it\'s associated with an user', function () {
    $unused = Invitation::factory()->create();
    $used = Invitation::factory()->used()->create();

    expect($unused)
        ->user_id->toBeNull()
        ->hasBeenUsed()->toBeFalse();

    expect($used)
        ->user_id->not->toBeNull()
        ->hasBeenUsed()->toBeTrue();
});
