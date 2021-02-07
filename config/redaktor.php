<?php

declare(strict_types=1);

return [

    /*
     * Configure your versioning strategies.
     */
    'strategies' => [
        [
            'id' => \DSLabs\LaravelRedaktor\Version\CustomHeaderStrategy::class,
            'config' => [
                'name' => 'API-Version',
            ],
        ],
    ],

    /*
     * Add here your Revision definitions, indexed by its version name.
     */
    'revisions' => [
        //  '2020-04-10' => [
        //      \App\Http\Revisions\AddContentLengthHeader::class,
        // ],
    ],

];
