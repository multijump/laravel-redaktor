<?php

declare(strict_types=1);

return [

    /*
     * Configure the revision name resolver to be used.
     */
    'resolver' => [
        'id' => \DSLabs\LaravelRedaktor\Version\CustomHeaderResolver::class,
        'config' => [
            'name' => 'API-Version',
        ],
    ],

    /*
     * Add here your Revision definitions, indexed by its version name.
     */
    'revisions' => [
        //  '2020-04-10' => [
        //      \App\Http\Revisions\AddContentLengthHeader::class,
        // ]
    ],

];
