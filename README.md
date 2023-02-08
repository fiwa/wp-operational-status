# WP Operational Status

WP Operational Status is a plugin that logs the operational status of one or more websites.

> **⚠ Important**  
>  This plugin cannot be activated network wide.

Install and Activate
------

### Installing From Github

To install the plugin from Github, you can download the latest [release](https://github.com/fiwa/wp-operational-status/releases) zip file, upload the Zip file to your WordPress install, and activate the plugin.

Usage
------

### Monitors

Add monitors in the WP Operational Status theme options page.

### Theme functions

#### `wpos_get_monitors`

##### Parameters

**number_of_posts** *int Optional*
 
 Default: `10`

```php
$monitors = wpos_get_monitors(
  array(
    'number_of_posts' => '15,
  ),
)
```

### Filters

#### `wpos_current_user_capability`

Default: `manage_options`

Filter the user capability needed to access the plugin settings page.

```php
apply_filters( 'wpos_current_user_capability', $wpos_current_user_capability );
```

##### Example

```php
add_filter( 'wpos_current_user_capability', function( $current_user_capability ) {
    return 'edit_posts';
});
```

#### `wpos_cron_schedule`

Default: `hourly`

Filter the cron schedule.

```php
apply_filters( 'wpos_cron_schedule', $wpos_cron_schedule );
```

##### Example

```php
add_filter( 'wpos_cron_schedule', function( $cron_schedule ) {
    return 'daily';
});
