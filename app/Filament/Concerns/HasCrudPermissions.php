<?php

namespace App\Filament\Concerns;

trait HasCrudPermissions
{
    protected static function getPermissionPrefix(): string
    {
        // যদি Resource ক্লাসে $permissionPrefix ডিফাইন থাকে, সেটাই নিন
        if (property_exists(static::class, 'permissionPrefix') && !empty(static::$permissionPrefix)) {
            return static::$permissionPrefix;
        }

        // না থাকলে: BrandResource -> brands, WarehouseResource -> warehouses
        return str(class_basename(static::class))
            ->beforeLast('Resource')
            ->kebab()
            ->plural();
    }

    protected static function ability(string $verb): string
    {
        return static::getPermissionPrefix().'.'.$verb;
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user?->can(static::ability('view')) ?? false;
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        return $user?->can(static::ability('create')) ?? false;
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();
        return $user?->can(static::ability('update')) ?? false;
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();
        return $user?->can(static::ability('delete')) ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return static::canDelete(null);
    }
}
