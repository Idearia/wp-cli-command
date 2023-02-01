<?php
namespace Idearia\WP_CLI;

use WP_CLI;

abstract class Utils
{
    /**
     * Check if WP-CLI is running
     *
     * @return bool
     */
    public static function is_cli_running(): bool
    {
        return defined( 'WP_CLI' ) && WP_CLI;
    }

    /**
     * Return the flag value or, if it's not set, the $default value.
     *
     * Because flags can be negated (e.g. --no-quiet to negate --quiet), this
     * function provides a safer alternative to using
     * `isset( $assoc_args['quiet'] )` or similar.
     *
     * @access public
     * @category Input
     *
     * @param array<string,mixed> $assoc_args Arguments array.
     * @param string $flag Flag to get the value.
     * @param mixed $default Default value for the flag. Default: NULL.
     * @return mixed
     */
    public static function get_flag_value( array $assoc_args, string $flag, mixed $default = null )
    {
        return isset( $assoc_args[ $flag ] ) ? $assoc_args[ $flag ] : $default;
    }

    /**
     * Loop through all not deleted sites and run the command on each one.
     * 
     * The callback must take two array arguments: $args and $assoc_args.
     *
     * @param callable $callback
     * @param array<mixed> $args
     * @param array<string,mixed> $assoc_args
     * @param array<string,mixed> $site_query Optional. Query arguments for get_sites(). Default: ['deleted' => 0, 'number' => PHP_INT_MAX]
     */
    public static function run_on_all_sites(
        callable $callback,
        array $args,
        array $assoc_args,
        array $site_query = ['deleted' => 0, 'number' => PHP_INT_MAX]
    ) : void
    {
        // Get all active sites.
        $sites = get_sites( $site_query );

        if ( is_int( $sites ) ) {
            WP_CLI::error( "Wrong site query. Did you pass 'count' as a query var?" );
            die(); // Just in case.
        }

        // Loop through all sites.
        foreach ( $sites as $site ) {
            // Switch to the site.
            switch_to_blog( $site->blog_id );

            // Run callback.
            call_user_func( $callback, $args, $assoc_args );

            // Restore the site.
            restore_current_blog();
        }
    }
}