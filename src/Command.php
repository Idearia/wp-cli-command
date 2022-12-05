<?php
namespace Idearia\WP_CLI;

use WP_CLI;

/**
 * A command to be launched from WP CLI
 */
abstract class Command
{
    /**
     * Text shown when the command is invoked with the
     * wrong arguments
     */
    protected static string $usage = 'Wrong arguments';

    /**
     * Register the command with WP-CLI
     *
     * @param string $command CLI command handled by this class
     */
    public static function init( string $command ): void
    {
        if ( ! static::isCliRunning() ) {
            return;
        }

        static::register( $command );
    }

    /**
     * Register WP_CLI command using WP_CLI::add_command()
     */
    protected static function register( string $command ): void
    {
        // Register the command
        WP_CLI::add_command( $command, static::class );
        // Validate arguments
        WP_CLI::add_hook(
            'before_run_command',
            function( array $args, array $assoc_args, array $options ) use ( $command )
            {
                // Temp vars
                $args_string = join( " ", $args );
                $command_tokens = explode( " ", $command );
                $actual_args = array_slice( $args, count( $command_tokens ) );
                // Ignore commands defined elsewhere
                if ( strpos( $args_string, $command ) !== 0 ) {
                    return;
                }
                // Do nothing if args are valid
                WP_CLI::debug("Starting custom validation", "idearia");
                if ( static::validate( $actual_args, $assoc_args, $options ) ) {
                    return;
                }
                // Exit if args are not valid
                WP_CLI::debug("Custom validation failed", "idearia");
                WP_CLI::error( static::$usage );
            }
        );
    }

    /**
     * Check if we are running from WP-CLI
     */
    protected static function isCliRunning(): bool
    {
        return defined( 'WP_CLI' ) && WP_CLI;
    }

    /**
     * Custom validation for the command's arguments; returning
     * false will skip command execution.
     * 
     * This method is for custom validation such as type
     * checking and dynamic validation. For basic validation,
     * use PHPDoc annotations as described in the commands
     * cookbook.
     *
     * Please note that:
     * - The method will run *before* PHPDoc validation.
     * - If the class has sub-commands, $args will contain the
     *   sub-command bing run in the first position.
     *
     * @param string[]             $args
     * @param array<string,string> $assoc_args
     * @param array<string,mixed>  $options
     * @return bool
     */
    public static function validate( array $args, array $assoc_args, array $options ): bool
    {
        WP_CLI::debug("Will skip custom validation", "idearia");
        return true;
    }
}
