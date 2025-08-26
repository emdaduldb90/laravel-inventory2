<?php

namespace App\Models\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogsTenantActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName(strtolower(class_basename(static::class)))
            ->logFillable()       // fillable পরিবর্তনগুলো লগ হবে
            ->logOnlyDirty()      // শুধু চেঞ্জড ফিল্ড
            ->dontSubmitEmptyLogs();
    }
}
