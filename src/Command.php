<?php
namespace Idearia\WP_CLI;

use WP_CLI;
use Idearia\WP_CLI\Utils;

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
     * Command handled by this class, set automatically
     */
    protected static string $command;

    /**
     * Short description of the command
     */
    protected static string $shortdesc = '';

    /**
     * Long description of the command
     */
    protected static string $longdesc = '';

    /**
     * Whether to allow the command to run on all sites in a multisite network
     */
    protected static bool $allow_all_sites_flag = false;

    /**
     * Synopsis of the command
     */
    protected static array $synopsis = [];

    /**
     * Number of times before_invoke is run
     */
    protected static int $count_before_invoke = 0;

    /**
     * Number of times after_invoke is run
     */
    protected static int $count_after_invoke = 0;

    /**
     * All subclasses must have an invoke method that handles the command
     */
    abstract protected static function invoke( array $args, array $assoc_args );

    public function __invoke( array $args, array $assoc_args )
    {
        if ( ! is_multisite() ) {
            static::invoke( $args, $assoc_args );
        } else {
            static::invoke_multisite( $args, $assoc_args );
        }
    }

    /**
     * Handle the command for multisite installations
     */
    public static function invoke_multisite( array $args, array $assoc_args )
    {
        $all_sites_flag = Utils::get_flag_value( $assoc_args, 'all-sites' );

        // Throw an error if the --all-sites flag is set but the command does not allow it.
        if ( $all_sites_flag && ! static::$allow_all_sites_flag ) {
            WP_CLI::error( 'The --all-sites flag is not allowed for this command.' );
        }

        // If the --all-sites flag is set then run the handler on all sites.
        if ( $all_sites_flag ) {
            Utils::run_on_all_sites( [ static::class, 'invoke' ], $args, $assoc_args );
        } else {
            // Run the handler on the current site.
            static::invoke( $args, $assoc_args );
        }
    }

    /**
     * Register the command with WP-CLI
     *
     * @param string $command CLI command handled by this class
     */
    public static function init( string $command ): void
    {
        if ( ! Utils::is_cli_running() ) {
            return;
        }

        static::$command = $command;

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
                'after_invoke' => [ static::class, '_after_invoke' ],
                'shortdesc' => static::$shortdesc,
                'synopsis' => static::get_synopsis(),
                'longdesc' => static::$longdesc,
            ],
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

    /**
     * Get the command synopsis
     *
     * @return array[]
     */
    public static function get_synopsis(): array
    {   
        // If the command allows it, then add the --all-sites flag
        // at the end of the synopsis array
        if ( static::$allow_all_sites_flag ) {
            static::$synopsis[] = [
                'type'        => 'flag',
                'name'        => 'all-sites',
                'description' => 'Run the command on all sites in the network',
                'optional'    => true,
            ];
        }

        return static::$synopsis;
    }
}
