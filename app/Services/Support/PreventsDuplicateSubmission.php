<?php

namespace App\Services\Support;

use Illuminate\Support\Facades\Cache;

/**
 * The original debounce guards (Cache::has('order_token') etc.) used one
 * global cache key shared by every user — while one shop was mid-submit,
 * every other shop in the system was blocked from the same action for
 * that window. Scoping the key per actor fixes that without changing the
 * debounce behavior itself.
 */
trait PreventsDuplicateSubmission
{
    protected function guardAgainstDuplicateSubmission(string $key, string $message, int $seconds = 2): void
    {
        $scopedKey = $key . ':' . (auth()->id() ?? 'guest');

        if (Cache::has($scopedKey)) {
            throw new \RuntimeException($message);
        }

        Cache::put($scopedKey, true, $seconds);
    }
}
