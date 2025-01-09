<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Class Namespace
    |--------------------------------------------------------------------------
    |
    | This value sets the root namespace for all of your Livewire component
    | classes. This value is used when Livewire finds components in your
    | application and when generating new components using Artisan.
    |
    */

    'class_namespace' => 'App\\Http\\Livewire',

    /*
    |--------------------------------------------------------------------------
    | View Path
    |--------------------------------------------------------------------------
    |
    | This value sets the root path for all of your Livewire component views.
    | This value is used when Livewire finds components in your application
    | and when generating new components using Artisan.
    |
    */

    'view_path' => resource_path('views/livewire'),

    /*
    |--------------------------------------------------------------------------
    | Asset URL
    |--------------------------------------------------------------------------
    |
    | This value sets the URL for Livewire assets, such as JavaScript and CSS.
    | By default, Livewire will load its assets from the same domain as your
    | application. However, you may specify a different URL if necessary.
    |
    */

    'asset_url' => null,

    /*
    |--------------------------------------------------------------------------
    | Middleware Group
    |--------------------------------------------------------------------------
    |
    | This value sets the middleware group that Livewire will use for its
    | routes. By default, Livewire uses the "web" middleware group.
    |
    */

    'middleware_group' => 'web',

    /*
    |--------------------------------------------------------------------------
    | Temporary File Uploads
    |--------------------------------------------------------------------------
    |
    | Livewire handles file uploads by storing the uploaded file in a
    | temporary directory until the upload is complete. You can configure
    | the temporary directory and other settings here.
    |
    */

    'temporary_file_upload' => [
        'disk' => null,        // Example: 'local', 's3'              Default: 'default'
        'rules' => null,       // Example: ['file', 'mimes:png,jpg']  Default: ['file', 'mimes:png,jpg', 'max:1024']
        'directory' => null,   // Example: 'tmp'                      Default: 'livewire-tmp'
        'middleware' => null,  // Example: 'throttle:60,1'            Default: 'throttle:60,1'
    ],

    /*
    |--------------------------------------------------------------------------
    | Render On Redirect
    |--------------------------------------------------------------------------
    |
    | This value determines if Livewire should re-render the component when
    | a redirect is triggered. By default, Livewire will re-render the
    | component. You may disable this behavior if necessary.
    |
    */

    'render_on_redirect' => true,

];
