<?php
/*
 * Production settings for shared hosting (DirectAdmin etc.).
 *
 * bin/build-release.sh copies this file to config/app_local.php in the upload
 * package. Fill in the block below before uploading, or edit
 * config/app_local.php afterwards with DirectAdmin's File Manager.
 */

// ============================ EDIT THESE ============================

// Your site's address: scheme and domain, e.g. 'https://www.example.com'.
// Leave out any subfolder; it is detected automatically. www and non-www both work.
$siteUrl = 'https://www.example.com';

// Your existing MySQL database (the same one the old app used).
$database = [
    'host' => 'localhost',
    'username' => 'CHANGE_ME',
    'password' => 'CHANGE_ME',
    'database' => 'CHANGE_ME',
    // 'utf8mb4' for a database created with config/schema/install.sql.
    // Use 'latin1' only for a database carried over from the old CakePHP 2 app
    // (it stored UTF-8 bytes in latin1 tables).
    'encoding' => 'utf8mb4',
];

// ====================================================================

use function Cake\Core\env;

return [
    'debug' => false,

    'App' => [
        'fullBaseUrl' => $siteUrl,
        // Your local timezone (https://www.php.net/manual/en/timezones.php).
        'defaultTimezone' => 'Asia/Singapore',
    ],

    'Security' => [
        'salt' => '__SALT__',
    ],

    'Datasources' => [
        'default' => $database + [
            'url' => env('DATABASE_URL', null),
        ],
    ],
];
