<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasCompanyScope
{
    protected static function bootHasCompanyScope(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            if (auth()->check() && ($company = auth()->user()->company)) {
                $builder->where(
                    (new static)->qualifyColumn('company_id'),
                    $company->id
                );
            }
        });
    }
}
