<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

use function str_starts_with;
use function tap;

trait ResolvesUUIDFields
{
	public function resolveRouteBinding($value, $field = null)
	{
		try {
			return $this->resolveRouteBindingQuery($this, $value, $field)->first();
		} catch (QueryException $e) {
			$uuid_syntax_error = str_starts_with(
				haystack: $e->getMessage(),
				needle: 'SQLSTATE[22P02]: Invalid text representation: 7 ERROR:  invalid input syntax for type uuid:'
			);

			if ($uuid_syntax_error) {
				throw tap(new ModelNotFoundException)->setModel(model: self::class, ids: $e->getBindings());
			}

			throw $e;
		}
	}
}