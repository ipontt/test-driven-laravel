<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\CreatesApplication;
use Tests\DuskTestCase;

uses(TestCase::class, CreatesApplication::class, RefreshDatabase::class)->in('Feature', 'Unit');
uses(DuskTestCase::class, DatabaseMigrations::class)->in('Browser');
