<?php

namespace App\Console\Commands;

use App\Models\Logs;
use App\Models\Orders;
use App\Models\RepAssignShop;
use App\Models\Role;
use App\Models\Routes;
use App\Models\Shops;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Phase 1: populates the new FK columns added alongside the existing
 * string "soft relationships", and reports anything that couldn't be
 * resolved. Nothing here touches the old string columns or deletes data —
 * it's safe to run repeatedly, and the real cutover (dropping the old
 * columns) is a separate, later step for once a report comes back clean.
 */
class BackfillPhase1ForeignKeys extends Command
{
    protected $signature = 'phase1:backfill';

    protected $description = 'Backfill Phase 1 foreign key columns from existing string-based soft relationships and report unresolved rows';

    public function handle(): int
    {
        $this->backfillUserRoles();
        $this->backfillOrderShops();
        $this->backfillShopRoutes();
        $this->backfillLogUsers();
        $this->backfillRepAssignShops();
        $this->backfillCartRemarks();

        return self::SUCCESS;
    }

    private function backfillUserRoles(): void
    {
        $this->info('users.role_id:');
        $unresolved = [];

        User::whereNull('role_id')->each(function (User $user) use (&$unresolved) {
            $role = Role::where('slug', $user->role)->first();
            if ($role) {
                $user->update(['role_id' => $role->id]);
            } else {
                $unresolved[] = "user #{$user->id} ({$user->email}) has role '{$user->role}' which matches no roles.slug";
            }
        });

        $this->report($unresolved);
    }

    private function backfillOrderShops(): void
    {
        $this->info('orders.shop_id:');
        $unresolved = [];

        // shops is small (low hundreds of rows) — preload it once instead
        // of running a lookup query per order. orders can be tens of
        // thousands of rows, so this also chunks and batches the updates
        // instead of issuing one UPDATE per row.
        $shopIdsByBranchCode = Shops::pluck('id', 'branch_code');

        Orders::whereNull('shop_id')
            ->select('id', 'unique_id', 'shop')
            ->chunkById(2000, function ($orders) use ($shopIdsByBranchCode, &$unresolved) {
                $idsByShopId = [];

                foreach ($orders as $order) {
                    $shopId = $shopIdsByBranchCode->get($order->shop);
                    if ($shopId) {
                        $idsByShopId[$shopId][] = $order->id;
                    } else {
                        $unresolved[] = "order #{$order->id} (unique_id {$order->unique_id}) has shop branch_code '{$order->shop}' which matches no shop";
                    }
                }

                foreach ($idsByShopId as $shopId => $orderIds) {
                    Orders::whereIn('id', $orderIds)->update(['shop_id' => $shopId]);
                }
            });

        $this->report($unresolved);
    }

    private function backfillShopRoutes(): void
    {
        $this->info('shops.morning_route_id / evening_route_id:');
        $unresolved = [];

        Shops::where(function ($q) {
            $q->whereNull('morning_route_id')->orWhereNull('evening_route_id');
        })->each(function (Shops $shop) use (&$unresolved) {
            $updates = [];

            if ($shop->morning_route_id === null) {
                $morning = Routes::where('name', $shop->morning_route)->first();
                if ($morning) {
                    $updates['morning_route_id'] = $morning->id;
                } else {
                    $unresolved[] = "shop #{$shop->id} ({$shop->name}) has morning_route '{$shop->morning_route}' which matches no route";
                }
            }

            if ($shop->evening_route_id === null) {
                $evening = Routes::where('name', $shop->evening_route)->first();
                if ($evening) {
                    $updates['evening_route_id'] = $evening->id;
                } else {
                    $unresolved[] = "shop #{$shop->id} ({$shop->name}) has evening_route '{$shop->evening_route}' which matches no route";
                }
            }

            if ($updates) {
                $shop->update($updates);
            }
        });

        $this->report($unresolved);
    }

    private function backfillLogUsers(): void
    {
        $this->info('logs.user_id:');
        $unresolved = [];
        $ambiguousCount = 0;
        $noMatchCount = 0;

        // users is small — preload once, grouped by name, so an ambiguous
        // (non-unique) name is detected without a query per log row. logs
        // can be hundreds of thousands of rows, so this chunks and batches
        // updates the same way backfillOrderShops does.
        $userIdsByName = User::get(['id', 'name'])->groupBy('name')->map->pluck('id');

        Logs::whereNull('user_id')
            ->select('id', 'user')
            ->chunkById(2000, function ($logs) use ($userIdsByName, &$unresolved, &$ambiguousCount, &$noMatchCount) {
                $idsByUserId = [];

                foreach ($logs as $log) {
                    $matches = $userIdsByName->get($log->user);
                    $count = $matches?->count() ?? 0;

                    if ($count === 1) {
                        $idsByUserId[$matches->first()][] = $log->id;
                    } elseif ($count > 1) {
                        $ambiguousCount++;
                        if (count($unresolved) < 20) {
                            $unresolved[] = "log #{$log->id} has user name '{$log->user}' which matches {$count} different users (ambiguous, left unresolved)";
                        }
                    } else {
                        $noMatchCount++;
                        if (count($unresolved) < 20) {
                            $unresolved[] = "log #{$log->id} has user name '{$log->user}' which matches no user";
                        }
                    }
                }

                foreach ($idsByUserId as $userId => $logIds) {
                    Logs::whereIn('id', $logIds)->update(['user_id' => $userId]);
                }
            });

        if ($ambiguousCount || $noMatchCount) {
            $this->warn("  {$ambiguousCount} ambiguous (name matches multiple users), {$noMatchCount} with no matching user. Examples:");
        }
        $this->report($unresolved);
    }

    private function backfillRepAssignShops(): void
    {
        $this->info('rep_assign_shops.rep_id_int / shop_id_int:');
        $unresolved = [];

        RepAssignShop::where(function ($q) {
            $q->whereNull('rep_id_int')->orWhereNull('shop_id_int');
        })->each(function (RepAssignShop $pivot) use (&$unresolved) {
            $updates = [];

            if ($pivot->rep_id_int === null) {
                if (ctype_digit((string) $pivot->rep_id) && DB::table('reps')->where('id', $pivot->rep_id)->exists()) {
                    $updates['rep_id_int'] = (int) $pivot->rep_id;
                } else {
                    $unresolved[] = "rep_assign_shops #{$pivot->id} has rep_id '{$pivot->rep_id}' which matches no rep";
                }
            }

            if ($pivot->shop_id_int === null) {
                if (ctype_digit((string) $pivot->shop_id) && DB::table('shops')->where('id', $pivot->shop_id)->exists()) {
                    $updates['shop_id_int'] = (int) $pivot->shop_id;
                } else {
                    $unresolved[] = "rep_assign_shops #{$pivot->id} has shop_id '{$pivot->shop_id}' which matches no shop";
                }
            }

            if ($updates) {
                $pivot->update($updates);
            }
        });

        $this->report($unresolved);
    }

    private function backfillCartRemarks(): void
    {
        $this->info('carts.remark:');

        $count = DB::table('carts')->whereNull('remark')->whereNotNull('remarke')->update([
            'remark' => DB::raw('remarke'),
        ]);

        $this->line("  copied {$count} row(s) from remarke");
    }

    private function report(array $unresolved): void
    {
        if (empty($unresolved)) {
            $this->line('  all rows resolved');
            return;
        }

        $this->warn('  ' . count($unresolved) . ' row(s) could not be resolved:');
        foreach ($unresolved as $line) {
            $this->line('  - ' . $line);
        }
    }
}
