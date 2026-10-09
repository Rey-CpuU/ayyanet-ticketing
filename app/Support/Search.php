<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Helpers for free-text search: scalar query-string reading (arrays are ignored instead of
 * causing "Array to string conversion") and case-insensitive "contains" matching with LIKE
 * wildcards escaped, portable across SQLite and PostgreSQL.
 */
final class Search
{
    /**
     * A trimmed string query parameter; anything that is not a string (e.g. ?q[]=x) gives ''.
     */
    public static function input(Request $request, string $key, int $limit = 100): string
    {
        $value = $request->query($key);

        return is_string($value) ? Str::limit(trim($value), $limit, '') : '';
    }

    /**
     * Escape the LIKE wildcards (% and _) and the escape character itself.
     */
    public static function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    /**
     * Add one grouped condition "any of the columns contains the term" (case-insensitive, LIKE
     * wildcards escaped). LOWER() + LIKE ... ESCAPE behaves the same on SQLite and PostgreSQL.
     *
     * @param  array<int, string>  $columns
     */
    public static function whereContains(Builder $query, array $columns, string $term, string $boolean = 'and'): Builder
    {
        $pattern = '%'.self::escapeLike(mb_strtolower($term)).'%';
        $grammar = $query->getQuery()->getGrammar();

        return $query->where(function (Builder $group) use ($columns, $pattern, $grammar) {
            foreach ($columns as $column) {
                $group->orWhereRaw('LOWER('.$grammar->wrap($column).') LIKE ? ESCAPE ?', [$pattern, '\\']);
            }
        }, boolean: $boolean);
    }
}
