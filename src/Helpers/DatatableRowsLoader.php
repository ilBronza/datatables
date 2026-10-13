<?php

namespace IlBronza\Datatables\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Loads table rows through an optional query provider or a legacy collection provider.
 */
class DatatableRowsLoader
{
    public static function get(callable $elementsProvider, ?string $modelClass = null, ?array $rowIds = null, ?callable $queryProvider = null) : Collection
    {
        if ($rowIds === [])
            return collect();

        if ($queryProvider)
        {
            $query = $queryProvider();

            if (! $query instanceof Builder && ! $query instanceof Relation)
                throw new InvalidArgumentException('getIndexQuery() must return an Eloquent builder or relation.');

            return static::resolve($query, $rowIds);
        }

        if ($rowIds === null || ! $modelClass || ! is_subclass_of($modelClass, Model::class))
            return static::resolve($elementsProvider(), $rowIds);

        // Boot the model before recording its scopes, so its normal scopes survive cleanup.
        new $modelClass();
        $scopeName = static::class;
        $previousScope = $modelClass::getAllGlobalScopes()[$modelClass][$scopeName] ?? null;
        $active = true;

        $modelClass::addGlobalScope($scopeName, function (Builder $query) use ($rowIds, &$active)
        {
            if ($active)
                $query->whereKey($rowIds);
        });

        try
        {
            return static::resolve($elementsProvider(), $rowIds);
        }
        finally
        {
            // A builder retained by the provider must not keep a live row filter either.
            $active = false;
            $scopes = $modelClass::getAllGlobalScopes();
            unset($scopes[$modelClass][$scopeName]);

            if ($previousScope !== null)
                $scopes[$modelClass][$scopeName] = $previousScope;

            $modelClass::setAllGlobalScopes($scopes);
        }
    }

    private static function resolve($elements, ?array $rowIds) : Collection
    {
        if ($elements instanceof Builder || $elements instanceof Relation)
        {
            $elements = clone $elements;

            if ($rowIds !== null)
            {
                // Applying this as a builder scope also groups existing OR conditions.
                $elements->withGlobalScope(static::class, function (Builder $query) use ($rowIds)
                {
                    $query->whereKey($rowIds);
                });
            }

            $elements = $elements->get();
        }

        if (! $elements instanceof Collection)
            throw new InvalidArgumentException('The datatable elements provider must return a collection, Eloquent builder or relation.');

        // Keep collection providers (including cached or scope-free providers) compatible.
        if ($rowIds !== null && ($firstElement = $elements->first()))
            $elements = $elements->whereIn($firstElement->getKeyName(), $rowIds);

        return $rowIds === null ? $elements : $elements->values();
    }
}
