<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class FlagableScope implements Scope
{
    /**
     * All of the extensions to be added to the builder.
     *
     * @var array
     */
    protected $extensions = ['FlagSet', 'OrFlagSet', 'FlagNotSet', 'OrFlagNotSet', 'FlagsIn', 'OrFlagsIn'];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    /**
     * Add the flag-set extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addFlagSet(Builder $builder)
    {
        $builder->macro('flagSet', function (Builder $builder, $flag) {
            $builder->withoutGlobalScope($this)
                    ->whereRaw("(`flags` & " . $flag . " != 0)");

            return $builder;
        });
    }

    /**
     * Add the or-flag-set extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addOrFlagSet(Builder $builder)
    {
        $builder->macro('orflagSet', function (Builder $builder, $flag) {
            $builder->withoutGlobalScope($this)
                    ->orWhereRaw("(`flags` & " . $flag . " != 0)");

            return $builder;
        });
    }

    /**
     * Add the flag-not-set extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addFlagNotSet(Builder $builder)
    {
        $builder->macro('flagNotSet', function (Builder $builder, $flag) {
            $builder->withoutGlobalScope($this)
                    ->whereRaw("(`flags` & " . $flag . " = 0)");

            return $builder;
        });
    }

    /**
     * Add the or-flag-not-set extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addOrFlagNotSet(Builder $builder)
    {
        $builder->macro('orflagNotSet', function (Builder $builder, $flag) {
            $builder->withoutGlobalScope($this)
                    ->orWhereRaw("(`flags` & " . $flag . " = 0)");

            return $builder;
        });
    }

    /**
     * Add the flags-in extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addFlagsIn(Builder $builder)
    {
        $builder->macro('flagsIn', function (Builder $builder, $flags) {
            $builder->withoutGlobalScope($this)
                    ->where(function($query) use ($flags) {
                        \collect($flags)->each(function($flag) use ($query) {
                            $query->orWhereRaw("`flags` & ? = ?", [$flag, $flag]);
                        });
                    });

            return $builder;
        });
    }

    /**
     * Add the or-flags-in extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addOrFlagsIn(Builder $builder)
    {
        $builder->macro('orFlagsIn', function (Builder $builder, $flags) {
            $builder->withoutGlobalScope($this)
                    ->orWhere(function($query) use ($flags) {
                        \collect($flags)->each(function($flag) use ($query) {
                            $query->orWhereRaw("`flags` & ? = ?", [$flag, $flag]);
                        });
                    });

            return $builder;
        });
    }

}
