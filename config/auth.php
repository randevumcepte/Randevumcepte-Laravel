<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session", "token"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        
        'api' => [
            'driver' => 'passport',
            'provider' => 'users',
        ],
         
        'sistemyonetim' => [
            'driver' => 'session',
            'provider' => 'sistemyoneticileri',

        ],
         
        'sistemyonetim-api' => [
            'driver' => 'passport',
            'provider' => 'sistemyoneticileri',
        ],
        'isletmeyonetim' => [
            'driver' => 'session',
            'provider' => 'isletmeyetkilileri',
        ],
       
        'isletmeyonetim-api' => [
             'driver' => 'passport',
            'provider' => 'isletmeyetkilileri',
        ],
         'satisortakligi' => [
            'driver' => 'session',
            'provider' => 'satisortakligi',
        ],
       
        'satisortakligi-api' => [
             'driver' => 'passport',
            'provider' => 'satisortakligi',
        ],
        
       
    ], 
    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\User::class,
        ],
         'isletmeyetkilileri' => [
            'driver' => 'eloquent',
            'model' => App\IsletmeYetkilileri::class,
            
        ],
        'sistemyoneticileri' => [
            'driver' => 'eloquent',
            'model' => App\SistemYoneticileri::class,
        ],
         'satisortakligi' => [
            'driver' => 'eloquent',
            'model' => App\SatisOrtakligiModel\SatisOrtaklari::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expire time is the number of minutes that the reset token should be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    */

    'passwords' => [
       'users' => [
             'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
        ],
        'isletmeyetkilileri' =>[
             'provider' => 'isletmeyetkilileri',
            'table' => 'password_resets',
            'expire' => 60,
        ],
        'sistemyoneticileri' => [
            'provider' => 'sistemyoneticileri',
            'table' => 'password_resets',
            'expire' => 60
        ],
         'satisortaklari' => [
            'provider' => 'satisortaklari',
            'table' => 'password_resets',
            'expire' => 60
        ],
    ],

];
