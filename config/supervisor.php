<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supervisor authorization window
    |--------------------------------------------------------------------------
    |
    | After a valid PIN, sensitive actions remain unlocked for this many
    | minutes. The front-end mirrors this window to avoid repeated prompts.
    |
    */

    'session_minutes' => (int) env('SUPERVISOR_SESSION_MINUTES', 10),

    /*
    |--------------------------------------------------------------------------
    | Testing bypass
    |--------------------------------------------------------------------------
    |
    | Feature tests focus on business behaviour. The dedicated supervisor
    | tests disable this flag to verify the real middleware behaviour.
    |
    */

    'test_bypass' => env('SUPERVISOR_AUTHORIZATION_TEST_BYPASS', false),

    /*
    |--------------------------------------------------------------------------
    | Protected routes
    |--------------------------------------------------------------------------
    |
    | Route name pattern => permission required to trigger the PIN prompt.
    | Routes not listed here keep their normal behaviour.
    |
    | Cash opening and closing are intentionally excluded: opening a shift is
    | routine, and closing already has its own PIN flow when the count differs.
    | Recording stock-count progress is also routine; only starting, finishing
    | and applying a count require a supervisor signature.
    |
    */

    'protected_routes' => [
        'admin.settings.*' => 'settings.manage',
        'admin.users.*' => 'users.manage',
        'admin.customers.*' => 'customers.manage',
        'admin.inventory.*' => 'inventory.adjust',
        'admin.stock-counts.store' => 'inventory.adjust',
        'admin.stock-counts.finish' => 'inventory.adjust',
        'admin.stock-counts.apply' => 'inventory.adjust',
        'admin.sales.void' => 'sales.void',
        'admin.cash.movement' => 'cash.open',
        'admin.prices.refresh' => 'metal_price.override',
        'admin.prices.manual' => 'metal_price.override',
        'admin.prices.purchase_settings.update' => 'settings.manage',
        'admin.purchases.store' => 'purchases.create',
        'admin.purchases.approve' => 'purchases.approve',
        'admin.purchases.reject' => 'purchases.approve',
        'admin.purchases.adjust.store' => 'purchases.approve',
        'admin.layaways.*' => 'layaways.manage',
        'admin.authorizations.approve' => 'products.edit_price',
        'admin.authorizations.reject' => 'products.edit_price',
    ],

];
