<?php

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\CreatesApplication;

uses(TestCase::class, CreatesApplication::class, RefreshDatabase::class)->in('Feature', 'Unit');
