<?php

namespace App\Service;

use DB;

class DatabaseService
{
    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Get table.
     *
     * @return void
     */
    public function findDecryptedId($tableName, $encryptedId, $returnId = true) {
        try {
            if ($returnId) {
                return DB::table($tableName)->where(DB::raw('md5(id::text)'), $encryptedId)->first()->id;
            } else {
                return DB::table($tableName)->where(DB::raw('md5(id::text)'), $encryptedId)->first();
            }
        } catch (\Throwable $th) {
            return null;
        }
    }
}
