<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;

class Filters extends BaseConfig
{
    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     *
     * @var array
     */
    public $aliases = [
        'csrf'     => CSRF::class,
        'toolbar'  => DebugToolbar::class,
        'honeypot' => Honeypot::class,
        'hasSignedIn' => \App\Filters\HasSignedIn::class,
        'adminPermission' => \App\Filters\AdminPermission::class,
        'cashierPermission' => \App\Filters\CashierPermission::class
    ];

    /**
     * List of filter aliases that are always
     * applied before and after every request.
     *
     * @var array
     */
    public $globals = [
        'before' => [
            // 'honeypot',
            'csrf',
            'hasSignedIn' => ['except' => ['admin', 'kasir', 'admin/*', 'kasir/*', 'sign_out']],
            'adminPermission' => ['except' => ['sign_in', '/', 'sign_out', 'kasir', 'kasir/*']],
            'cashierPermission' => ['except' => ['sign_in', '/', 'sign_out', 'admin', 'admin/*']]
        ],
        'after' => [
            'toolbar' => [
                'except' => [
                    'admin/tampilkan-transaksi-dua-bulan-yang-lalu',
                    'admin/produk/tampilkan-detail/*',
                    'admin/produk/mencari/*'
                ]
            ],
            // 'honeypot',
        ],
    ];

    /**
     * List of filter aliases that works on a
     * particular HTTP method (GET, POST, etc.).
     *
     * Example:
     * 'post' => ['csrf', 'throttle']
     *
     * @var array
     */
    public $methods = [];

    /**
     * List of filter aliases that should run on any
     * before or after URI patterns.
     *
     * Example:
     * 'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
     *
     * @var array
     */
    public $filters = [];
}
