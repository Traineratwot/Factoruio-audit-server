<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Laravel\Scout\Searchable;
use RuntimeException;
use Throwable;

class ScoutSearchHelper
{
    /**
     * Apply Scout search to an Eloquent Builder, preserving ordering from search results.
     *
     * @param  Builder  $query  Original Eloquent builder
     * @param  string|null  $search  Search term
     * @param  callable|null  $fallback  Fallback callback for non-searchable models or empty results: function(Builder $query): Builder
     * @param  bool  $logging  Enable logging
     *
     * @throws InvalidArgumentException If $fallback is null and model is not Searchable
     */
    public static function apply(
        Builder $query,
        ?string $search,
        ?callable $fallback = null,
        bool $logging = false,
    ): Builder {
        $queryOriginal = clone $query;

        try {
            $model = $query->getModel();
            /** @var class-string<Model&Searchable> $modelClass */
            $modelClass = get_class($model);

            self::log($logging, 'info', 'Scout search started', [
                'model' => $modelClass,
            ]);

            if (! self::usesTrait($modelClass, Searchable::class)) {
                self::log($logging, 'info', 'Model is not Searchable', [
                    'model' => $modelClass,
                ]);

                if ($fallback === null) {
                    throw new InvalidArgumentException(
                        "Model [{$modelClass}] does not use Searchable trait and no fallback callback provided."
                    );
                }

                return $fallback($queryOriginal) ?? $queryOriginal;
            }

            if (blank($search)) {
                self::log($logging, 'info', 'Search is blank, returning original query');

                if ($fallback !== null) {
                    return $fallback($query) ?? $query;
                }

                return $query;
            }

            $searchLimit = (int) config('scout.limit.max.filament', 20);
            $primaryKeyName = $model->getKeyName();

            self::log($logging, 'info', 'Search parameters', [
                'search_term' => $search,
                'search_limit' => $searchLimit,
                'primary_key' => $primaryKeyName,
                'model_class' => $modelClass,
            ]);

            /** @var Collection<int, Model> $results */
            $results = $modelClass::search($search)
                ->query(function (Builder $q) use ($primaryKeyName) {
                    $q->select($primaryKeyName);
                })
                ->take($searchLimit)
                ->get();

            $keysArray = $results
                ->pluck($primaryKeyName)
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->values()
                ->all();

            self::log($logging, 'info', 'Search results', [
                'count' => count($keysArray),
            ]);

            if (empty($keysArray)) {
                if ($fallback !== null) {
                    return $fallback($query) ?? $query;
                }

                throw new RuntimeException('Scout returned empty results');
            }

            $caseStatement = 'CASE '.$primaryKeyName;
            foreach ($keysArray as $index => $key) {
                $caseStatement .= ' WHEN '.(int) $key.' THEN '.$index;
            }
            $caseStatement .= ' END';

            return $query
                ->whereIn($primaryKeyName, $keysArray)
                ->orderByRaw($caseStatement);
        } catch (InvalidArgumentException $e) {
            self::log($logging, 'error', 'Scout search failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        } catch (Throwable $e) {
            self::log($logging, 'error', 'Scout search failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($fallback !== null) {
                return $fallback($queryOriginal) ?? $queryOriginal;
            }

            return $queryOriginal;
        }
    }

    private static function log(bool $enabled, string $level, string $message, array $context = []): void
    {
        if (! $enabled) {
            return;
        }

        Log::channel('scout')->{$level}($message, $context);
    }

    private static function usesTrait(string $className, string $traitName): bool
    {
        do {
            $traits = class_uses($className);

            if (in_array($traitName, $traits, true)) {
                return true;
            }
        } while ($className = get_parent_class($className));

        return false;
    }
}
