<?php

namespace App\Concerns;

use Illuminate\Support\Facades\DB;
use Throwable;

trait ModelTransaction {

    protected function performTransaction(string $method, ...$args)
    {
        DB::beginTransaction();
        try {
            $this->$method(...$args);
            DB::commit();
        } catch (Throwable $t) {
            DB::rollBack();
            throw $t;
        }
    }

}
