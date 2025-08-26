<?php

return [

    'backup' => [
        'name' => env('APP_NAME', 'laravel-inventory'),

        'source' => [
            'files' => [
                'include' => [
                    base_path(),
                ],
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                ],

                // v10+ required keys
                'follow_links' => false,
                'ignore_unreadable_directories' => false,
                'relative_path' => base_path(),
            ],

            'databases' => ['mysql'],
        ],

        'destination' => [
            'filename_prefix' => '',
            'disks' => ['backups'], // config/filesystems.php এ 'backups' ডিস্ক থাকতে হবে
        ],

        'temporary_directory' => storage_path('app/backup-temp'),
    ],

'notifications' => [
    'notifications' => [
        \Spatie\Backup\Notifications\Notifications\BackupHasFailed::class         => ['log', 'mail'],
        \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFound::class => ['log', 'mail'],
        \Spatie\Backup\Notifications\Notifications\CleanupHasFailed::class        => ['log', 'mail'],
        \Spatie\Backup\Notifications\Notifications\BackupWasSuccessful::class     => ['log', 'mail'],
        \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFound::class   => ['log', 'mail'],
        \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessful::class    => ['log', 'mail'],
    ],
    'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,
    'mail' => [
        'to' => env('BACKUP_MAIL_TO', 'no-reply@example.com'),
    ],
],

    // v10+ format: health_checks অ্যারের মধ্যে থ্রেশহোল্ড সেট করুন
    'monitor_backups' => [
        [
            'name'  => env('APP_NAME', 'laravel-inventory'),
            'disks' => ['backups'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,      // সর্বশেষ ব্যাকআপ 1 দিনের বেশি পুরোনো না
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000, // 5GB এর বেশি না
            ],
        ],
    ],

    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
        'default_strategy' => [
            'keep_all_backups_for_days' => 7,
            'keep_daily_backups_for_days' => 16,
            'keep_weekly_backups_for_weeks' => 8,
            'keep_monthly_backups_for_months' => 6,
            'keep_yearly_backups_for_years' => 2,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
    ],

];
