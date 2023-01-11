<?php
namespace Idearia\WP_CLI;

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
     * @param array  $assoc_args Arguments array.
     * @param string $flag       Flag to get the value.
     * @param mixed  $default    Default value for the flag. Default: NULL.
     * @return mixed
     */
    public static function get_flag_value( array $assoc_args, string $flag, mixed $default = null ) : mixed
    {
        return isset( $assoc_args[ $flag ] ) ? $assoc_args[ $flag ] : $default;
    }

    /**
     * Loop through all not deleted sites and run the command on each one.
     * 
     * The callback must take two array arguments: $args and $assoc_args.
     *
     * @param callable $callback
     * @param array $args
     * @param array $assoc_args
     */
    public static function run_on_all_sites( callable $callback, array $args, array $assoc_args ) : void
    {
        // Get all active sites.
        $sites = get_sites( array(
            'deleted'  => 0,
        ) );

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