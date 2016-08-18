<?php

/**
 * Websites model config
 *
 * @link https://github.com/ddpro/admin/blob/master/docs/model-configuration.md
 */

return array(

    'title' => 'Websites',

    'single' => 'website',

    'model' => '\Delatbabel\SiteConfig\Models\Website',

    /**
     * The display columns
     */
    'columns' => array(
        'id',
        'name' => array(
            'title' => 'Name',
        ),
        'http_host' => array(
            'title' => 'HTTP Hostname',
        ),
        'environment' => array(
            'title' => 'Environment',
        ),
    ),

    /**
     * The filter set
     */
    'filters' => array(
        'id',
        'name' => array(
            'title' => 'Name',
        ),
        'http_host' => array(
            'title' => 'HTTP Hostname',
        ),
        'environment' => array(
            'title' => 'Environment',
        ),
    ),

    /**
     * The editable fields
     */
    'edit_fields' => array(
        'name' => array(
            'title' => 'Name',
            'type' => 'text',
        ),
        'http_host' => array(
            'title' => 'HTTP Hostname',
            'type' => 'text',
        ),
        'environment' => array(
            'title' => 'Environment',
            'type' => 'text',
        ),
    ),

);
