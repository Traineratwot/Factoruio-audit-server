<?php

namespace App\Helpers;

use Throwable;
use RuntimeException;
use InvalidArgumentException;
use Laravel\Scout\Searchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ScoutSearchHelper
{
    /**
     * Применяет поиск Scout к Eloquent Builder.
     *
     * @param Builder $query Исходный Eloquent builder
     * @param string|null $search Поисковая строка
     * @param callable|null $fallback Fallback-колбэк стандартного поиска: function(Builder $query): Builder
     * @param bool $logging Включить логирование
     *
     * @throws InvalidArgumentException Если $fallback = null и модель не Searchable
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
            /**
             * @var Model&Searchable&string $modelClass
             */
            $modelClass = get_class($model);

            self::log($logging, 'info', 'Scout search started', [
                'model'     => $modelClass,
                'timestamp' => now(),
            ]);

            if (!self::usesTrait($modelClass, Searchable::class)) {
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

            self::log($logging, 'info', 'Model has Searchable trait', [
                'model' => $modelClass,
            ]);

            if (blank($search)) {
                self::log($logging, 'info', 'Search is blank, returning original query');

                if ($fallback !== null) {
                    return $fallback($query) ?? $query;
                }

                return $query;
            }

            $searchLimit = (int) config('scout.limit.max.filament', 10);
            $primaryKeyName = $model->getKeyName();

            self::log($logging, 'info', 'Search parameters', [
                'search_term'  => $search,
                'search_limit' => $searchLimit,
                'primary_key'  => $primaryKeyName,
                'model_class'  => $modelClass,
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
                'found_keys' => $keysArray,
                'count'      => count($keysArray),
            ]);

            if (empty($keysArray)) {
                if ($fallback !== null) {
                    return $fallback($query) ?? $query;
                }
                throw new RuntimeException('Scout returned empty keys');
            }

            $caseStatement = 'CASE ' . $primaryKeyName;
            foreach ($keysArray as $index => $key) {
                $caseStatement .= ' WHEN ' . (int) $key . ' THEN ' . $index;
            }
            $caseStatement .= ' END';

            self::log($logging, 'info', 'Order statement built', [
                'case_statement' => $caseStatement,
            ]);

            return $query
                ->whereIn($primaryKeyName, $keysArray)
                ->orderByRaw($caseStatement);
        } catch (InvalidArgumentException $e) {
            self::log($logging, 'error', 'Scout search failed', [
                'error' => $e->getMessage(),
                'code'  => $e->getCode(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        } catch (Throwable $e) {
            self::log($logging, 'error', 'Scout search failed 2', [
                'error' => $e->getMessage(),
                'code'  => $e->getCode(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
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
        if (!$enabled) {
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
