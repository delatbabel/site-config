<?php

/**
 * Websites model config
 *
 * @link https://github.com/ddpro/admin/blob/master/docs/model-configuration.md
 */

return [

    'title' => 'Websites',

    'single' => 'website',

    'model'       => '\Delatbabel\SiteConfig\Models\Website',

    /**
     * The display columns
     */
    'columns'     => [
        'id'          => [
            'title' => 'ID',
        ],
        'name'        => [
            'title' => 'Name',
        ],
        'http_host'   => [
            'title' => 'HTTP Hostname',
        ],
        'environment' => [
            'title' => 'Environment',
        ],
    ],

    /**
     * The filter set
     */
    'filters'     => [
        'id'          => [
            'title' => 'ID',
        ],
        'name'        => [
            'title' => 'Name',
        ],
        'http_host'   => [
            'title' => 'HTTP Hostname',
        ],
        'environment' => [
            'title' => 'Environment',
        ],
    ],

    /**
     * The editable fields
     */
    'edit_fields' => [
        'name'        => [
            'title' => 'Name',
            'type'  => 'text',
        ],
        'http_host'   => [
            'title' => 'HTTP Hostname',
            'type'  => 'text',
        ],
        'environment' => [
            'title' => 'Environment',
            'type'  => 'text',
        ],
    ],

];
