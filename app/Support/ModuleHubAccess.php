<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

final class ModuleHubAccess
{
    /**
     * @param  array<string, mixed>|null  $hub
     */
    public function canAccessModule(?User $user, string $module, ?array $hub = null): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->isAdministrator($user) || $user->can($this->modulePermission($module))) {
            return true;
        }

        $hub ??= config("module_hubs.{$module}");

        if (! is_array($hub)) {
            return false;
        }

        foreach ($hub['items'] ?? [] as $item) {
            if (is_array($item) && $this->canViewItem($user, $module, $item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public function canViewItem(?User $user, string $module, array $item): bool
    {
        if (! $user || ! (bool) ($item['activo'] ?? true)) {
            return false;
        }

        if ($this->isAdministrator($user)) {
            return true;
        }

        if (! $user->can($this->itemPermission($module, $item))) {
            return false;
        }

        $roles = $item['role'] ?? [];

        if ($roles === [] || $roles === '') {
            return true;
        }

        return $user->hasAnyRole(is_array($roles) ? $roles : [$roles]);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public function itemPermission(string $module, array $item): string
    {
        $explicitPermission = trim((string) ($item['permission'] ?? ''));

        if ($explicitPermission !== '') {
            return $explicitPermission;
        }

        $itemName = trim((string) ($item['nombre'] ?? 'item'));
        $slug = Str::slug($itemName, '_');

        if ($slug === '') {
            $slug = 'item_'.substr(md5($module.'|'.$itemName.'|'.($item['url'] ?? '')), 0, 8);
        }

        return "module.{$module}.item.{$slug}.view";
    }

    public function modulePermission(string $module): string
    {
        return "module.{$module}.view";
    }

    private function isAdministrator(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'admin']);
    }
}
