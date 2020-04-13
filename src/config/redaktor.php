<?php

declare(strict_types=1);

return [

    /*
     * Configure the revision name resolver to be used.
     */
    'resolver' => [
        'id' => \DSLabs\LaravelRedaktor\Version\HeaderResolver::class,
        'config' => [
            'name' => 'API-Version',
        ],
    ],

    /*
     * Add here your Revision closures, indexed by revision name.
     */
    'revisions' => [
        //  '2020-04-10' => [
        //      \App\Http\Revisions\AddContentLengthHeader::class,
        // ]
    ],

];