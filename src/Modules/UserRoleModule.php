<?php

namespace Log1x\Poet\Modules;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class UserRoleModule extends AbstractModule
{
    /**
     * The module key.
     *
     * @var string
     */
    protected $key = 'user_role';

    /**
     * Handle the module.
     *
     * @return void
     */
    public function handle()
    {
        $this->config->each(function ($value, $key) {
            /**
             * Avoids slow database query
             * https://developer.wordpress.org/reference/functions/add_role/#comment-3194
             */
            if (wp_roles()->is_role($key)) {
                if ($value === false) {
                    remove_role($key);
                }
                return;
            }

            if ($value === false) {
                return;
            }

            $displayName = $value['display_name'] ?? Str::title($key);
            $capabilities = $value['capabilities'] ?? ['read'];
            if (!is_array($capabilities)) {
                wp_die(esc_html__('Expected capabilities fields to be an array'));
            }

            if (!Arr::isAssoc($capabilities)) {
                $capabilities = $this->enableUserCapabilities($capabilities);
            }

            add_role($key, $displayName, $capabilities);
        });
    }

    /**
     * Converts an array like this ['read', 'edit_posts'] into ['read' => true, 'edit_posts' => true]
     *
     * @param   array<string> $capabilities - List of capabilities to be enabled in role.
     * @return  array<string,true>
     */
    protected function enableUserCapabilities($capabilities)
    {
        return collect($capabilities)
            ->mapWithKeys(function (string $capability, int $key) {
                return [$capability => true];
            })
            ->toArray();
    }
}
