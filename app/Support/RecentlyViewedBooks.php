<?php

namespace App\Support;

use Illuminate\Support\Facades\Session;

/**
 * Session-based "recently viewed books" history. Anonymous, per-browser —
 * intentionally not a database table since it's disposable UI convenience
 * data, not something staff or reports need to query.
 */
class RecentlyViewedBooks
{
    protected const SESSION_KEY = 'recently_viewed_books';

    protected const MAX_ITEMS = 8;

    public static function remember(int $bookId): void
    {
        $ids = self::ids();

        $ids = array_values(array_filter($ids, fn (int $id) => $id !== $bookId));
        array_unshift($ids, $bookId);

        Session::put(self::SESSION_KEY, array_slice($ids, 0, self::MAX_ITEMS));
    }

    /**
     * @return array<int, int>
     */
    public static function ids(): array
    {
        /** @var array<int, int> $ids */
        $ids = Session::get(self::SESSION_KEY, []);

        return $ids;
    }
}
