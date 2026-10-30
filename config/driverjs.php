<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Driver.js Version
    |--------------------------------------------------------------------------
    |
    | This value determines which version of Driver.js to load from the CDN.
    | You may change this to pin to a specific version for stability, or use
    | "latest" to always get the newest release. When using npm/vite, this
    | setting is ignored since you manage the version in package.json.
    |
    */

    'version' => env('DRIVERJS_VERSION', '1.4.0'),

    /*
    |--------------------------------------------------------------------------
    | Asset Source
    |--------------------------------------------------------------------------
    |
    | This option controls how the Driver.js CSS and JavaScript assets are
    | loaded. Supported values: "cdn", "npm", "custom".
    |
    | - "cdn": Loads assets from jsDelivr CDN (no build step required).
    | - "npm":  Expects you to install driver.js via npm/pnpm/yarn and
    |           bundle it with Vite/Mix. No CDN scripts will be injected.
    | - "custom": You manage asset inclusion yourself. The package will not
    |             inject any scripts or stylesheets.
    |
    */

    'asset_source' => env('DRIVERJS_ASSET_SOURCE', 'npm'),

    /*
    |--------------------------------------------------------------------------
    | CDN URL Overrides
    |--------------------------------------------------------------------------
    |
    | If you want to use a custom CDN or self-hosted files, you can override
    | the default CDN URLs here. When set to null, the package will use the
    | default jsDelivr URLs based on the configured version above.
    |
    */

    'cdn_js_url' => env('DRIVERJS_CDN_JS_URL', null),
    'cdn_css_url' => env('DRIVERJS_CDN_CSS_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Default Driver Configuration
    |--------------------------------------------------------------------------
    |
    | These options define the global defaults for every Driver.js instance
    | created through the package. You can override any of these options
    | at runtime using the fluent builder methods.
    |
    | See https://driverjs.com/docs/configuration for full documentation.
    |
    */

    'defaults' => [

        // Animate the tour transitions between steps.
        'animate' => true,

        // Overlay color (any valid CSS color).
        'overlay_color' => '#000',

        // Overlay opacity (0.0 to 1.0).
        'overlay_opacity' => 0.7,

        // Smooth scroll to the highlighted element.
        'smooth_scroll' => false,

        // Allow closing the tour by clicking the overlay or pressing Escape.
        'allow_close' => true,

        // Action on overlay click: "close", "nextStep", or a custom callback.
        'overlay_click_behavior' => 'close',

        // Padding (px) between the highlighted element and the stage cutout.
        'stage_padding' => 10,

        // Border radius (px) of the stage cutout.
        'stage_radius' => 5,

        // Enable keyboard navigation (Escape, Arrow keys).
        'allow_keyboard_control' => true,

        // Disable interaction with the currently highlighted element.
        'disable_active_interaction' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Popover Configuration
    |--------------------------------------------------------------------------
    |
    | These options control the appearance and behaviour of the popover
    | that appears alongside each highlighted element. Values set here
    | serve as defaults and can be overridden on a per-step basis.
    |
    */

    'popover' => [

        // Custom CSS class added to the popover wrapper element.
        'class' => 'bugambilia-tour',

        // Distance (px) between the popover and the highlighted element.
        'offset' => 10,

        // Which navigation buttons to display.
        // Options: "next", "previous", "close"
        'show_buttons' => ['next', 'previous', 'close'],

        // Which navigation buttons to visually disable (greyed out).
        'disable_buttons' => [],

        // Show the "X of Y" progress indicator in the popover footer.
        'show_progress' => true,

        // Progress text template. {{current}} and {{total}} are replaced.
        'progress_text' => 'Paso {{current}} de {{total}}',

        // Button label texts.
        'next_btn_text' => 'Siguiente &rarr;',
        'prev_btn_text' => '&larr; Anterior',
        'done_btn_text' => 'Entendido',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tour Storage
    |--------------------------------------------------------------------------
    |
    | When a user completes a tour, the package can remember this so the
    | tour is not shown again on subsequent visits. Choose the storage
    | driver and key prefix used to persist this information.
    |
    | Supported storage drivers: "session", "cache", "database", "null"
    |
    | - "session": Uses Laravel's session (cleared when browser closes).
    | - "cache":   Uses Laravel's cache (persists across sessions).
    | - "database": Stores in a database table (requires migration).
    | - "null":    Disables tour completion tracking entirely.
    |
    */

    'storage' => [

        'driver' => env('DRIVERJS_STORAGE_DRIVER', 'database'),

        'key_prefix' => 'driverjs_tour_',

        // Cache store to use when driver is "cache".
        'cache_store' => env('DRIVERJS_CACHE_STORE', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware to apply to the package's internal HTTP routes, such as the
    | tour-completion tracking endpoint (POST /driverjs/tour/completed).
    |
    | When using the "database" storage driver you should add the "auth"
    | middleware here so only authenticated users can record completions.
    |
    | Accepts a string or an array of middleware names.
    | Example: 'web', ['web', 'auth'], null (no extra middleware)
    |
    */

    'route_middleware' => env('DRIVERJS_ROUTE_MIDDLEWARE', ['web', 'auth']),

];
