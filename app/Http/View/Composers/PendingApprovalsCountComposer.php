<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Models\CarProfile;

class PendingApprovalsCountComposer
{
    public function compose(View $view)
    {
        $pendingApprovalsCount = CarProfile::where('car_status', 'pending_sold')
            ->orWhere(function ($query) {
                $query->where('car_status', 'booked')
                    ->where('is_booked', 0);
            })
            ->count();

        $view->with('pendingApprovalsCount', $pendingApprovalsCount);
    }
}
