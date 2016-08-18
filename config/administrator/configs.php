<?php

/**
 * Configs model config
 *
 * @link https://github.com/ddpro/admin/blob/master/docs/model-configuration.md
 */

return array(

    'title' => 'Configs',

    'single' => 'config',

    'model' => '\Delatbabel\SiteConfig\Models\Config',

    /**
     * The display columns
     */
    'columns' => array(
        'id',
        'environment' => array(
            'title' => 'Environment',
        ),
        'group' => array(
            'title' => 'Group',
        ),
        'key' => array(
            'title' => 'Key',
        ),
    ),

    /**
     * The filter set
     */
    'filters' => array(
        'id',
        'environment' => array(
            'title' => 'Environment',
        ),
        'group' => array(
            'title' => 'Group',
        ),
    ),

    /**
     * The editable fields
     */
    'edit_fields' => array(
        'environment' => array(
            'title' => 'Environment',
            'type' => 'text',
        ),
        'website' => array(
            'title' => 'Website',
            'type' => 'relationship',
            'name_field' => 'name',
            'options_sort_field' => 'name',
        ),
        'group' => array(
            'title' => 'Group',
            'type' => 'text',
        ),
        'key' => array(
            'title' => 'Key',
            'type' => 'text',
        ),
        'value' => array(
            'title' => 'Value',
            'type' => 'textarea',
        ),
        'type' => array(
            'title' => 'Type',
            'type' => 'text',
        ),
    ),

);
