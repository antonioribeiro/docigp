<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Data\Repositories\EntryTypes as EntryTypesRepository;

class EntryTypes extends Controller
{
    /**
     * Get all data
     *
     * @return array
     */
    public function all()
    {
        return app(EntryTypesRepository::class)
            ->disablePagination()
            ->all();
    }
}
