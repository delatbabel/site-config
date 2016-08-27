<?php

/**
 * Configs model config
 *
 * @link https://github.com/ddpro/admin/blob/master/docs/model-configuration.md
 */

return [

    'title' => 'Configs',

    'single' => 'config',

    'model' => '\Delatbabel\SiteConfig\Models\Config',

    /**
     * The display columns
     */
    'columns' => [
        'id',
        'environment' => [
            'title' => 'Environment',
        ],
        'group' => [
            'title' => 'Group',
        ],
        'key' => [
            'title' => 'Key',
        ],
    ],

    /**
     * The filter set
     */
    'filters' => [
        'id',
        'environment' => [
            'title' => 'Environment',
        ],
        'group' => [
            'title' => 'Group',
        ],
    ],

    /**
     * The editable fields
     */
    'edit_fields' => [
        'environment' => [
            'title' => 'Environment',
            'type'  => 'text',
        ],
        'website' => [
            'title'              => 'Website',
            'type'               => 'relationship',
            'name_field'         => 'name',
            'options_sort_field' => 'name',
        ],
        'group' => [
            'title' => 'Group',
            'type'  => 'text',
        ],
        'key' => [
            'title' => 'Key',
            'type'  => 'text',
        ],
        'value' => [
            'title' => 'Value',
            'type'  => 'textarea',
        ],
        'type' => [
            'title' => 'Type',
            'type'  => 'text',
        ],
    ],

];
