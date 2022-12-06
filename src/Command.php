<?php
namespace Idearia\WP_CLI;

use WP_CLI;

/**
 * Register new WP CLI commands.
 *
 * Override methods to setup hooks and custom validation.
 * List of available hooks in README.
 */
abstract class Command
{
    /**
     * Text shown when the command is invoked with the
     * wrong arguments
     */
    protected static string $usage = 'Wrong arguments';

    /**
     * Number of times before_invoke is run
     */
    protected static int $count_before_invoke = 0;

    /**
     * Number of times after_invoke is run
     */
    protected static int $count_after_invoke = 0;

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
        WP_CLI::add_command(
            $command,
            static::class,
            [
                'before_invoke' => [ static::class, '_before_invoke' ],
                'after_invoke' => [ static::class, '_after_invoke' ]
            ]
        );

        // Allow to do stuff just before the command is executed
        WP_CLI::add_hook(
            'before_run_command',
            function( array $args, array $assoc_args, array $options ) use ( $command )
            {
                // The before_run_command hook in WP-CLI is run for all commands.
                // Here we restrict the scope to the commands defined in this class,
                // using the fact that $all_args includes the command being invoked
                $args_string = join( " ", $args );
                $command_tokens = explode( " ", $command );
                $actual_args = array_slice( $args, count( $command_tokens ) );
                // Ignore commands defined elsewhere
                if ( strpos( $args_string, $command ) !== 0 ) {
                    return;
                }
                // Allow the user to hook into before_run_command
                WP_CLI::debug("About to execute before_run_command hook", "idearia");
                static::before_run_command( $args, $assoc_args, $options );
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
     * Override to inject code just before any command in
     * the class is found (runs before before_invoked)
     *
     * @param string[]             $args
     * @param array<string,string> $assoc_args
     * @param array<string,mixed>  $options
     */
    public static function before_run_command( array $args, array $assoc_args, array $options ): void
    {
        WP_CLI::debug("Skipping before_run_command hook", "idearia");
    }

    /**
     * Override to inject code just before any command in the
     * class is invoked
     */
    public static function before_invoke(): void
    {
        WP_CLI::debug("Skipping before_invoke hook", "idearia");
    }

    /**
     * Override to inject code just after any command in the
     * class is invoked
     */
    public static function after_invoke(): void
    {
        WP_CLI::debug("Skipping after_invoke hook", "idearia");
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
     * - The method will run *before* PHPDoc validation and *after*
     *   $this->before_run_command()
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
        WP_CLI::debug("Skipping custom validation hook", "idearia");
        return true;
    }

    /**
     * Wrapper to avoid running before_invoke twice, due to how
     * WP-CLI works (it runs both for the parent comand and the
     * subcommand)
     */
    public static function _before_invoke(): void
    {
        if ( static::$count_before_invoke == 0 ) {
            static::before_invoke();
        }
        static::$count_before_invoke++;
    }

    /**
     * Wrapper to avoid running after_invoke twice, due to how
     * WP-CLI works (it runs both for the parent comand and the
     * subcommand)
     */
    public static function _after_invoke(): void
    {
        if ( static::$count_after_invoke == 0 ) {
            static::after_invoke();
        }
        static::$count_after_invoke++;
    }

}
