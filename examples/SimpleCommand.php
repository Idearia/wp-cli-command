<?php
use Idearia\WP_CLI\Command;
use WP_CLI;

class SimpleCommand extends Command
{
    /**
     * Prints a greeting.
     *
     * ## OPTIONS
     *
     * <name>
     * : The name of the person to greet; must be at least
     * 4 characters
     *
     * [--type=<type>]
     * : Whether or not to greet the person with success or error.
     * ---
     * default: success
     * options:
     *   - success
     *   - error
     * ---
     *
     * ## EXAMPLES
     *
     *     wp example hello Newman
     *
     * @param array $args       Indexed array of positional arguments.
     * @param array $assoc_args Associative array of associative arguments.
     */
    public function __invoke( array $args, array $assoc_args )
    {
        list( $name ) = $args;
        $type = $assoc_args['type'];
        WP_CLI::$type( "Hello, $name!" );
    }

    /**
     * Restrict the <name> parameter to be longer than 3 characters.
     *
     * @param string[]             $args
     * @param array<string,string> $assoc_args
     * @param array<string,mixed>  $options
     */
    public static function validate(array $args, array $assoc_args, array $options): bool
    {
        $name = $args[0] ?? null;
        if ( $name && strlen( $name ) <= 3 ) {
            return false;
        }
        return true;
    }

    /**
     * Tell the user what's going on, as soon as WP CLI starts
     * thinking about executin a command
     *
     * @param string[]             $args
     * @param array<string,string> $assoc_args
     * @param array<string,mixed>  $options
     */
    public static function before_run_command( array $args, array $assoc_args, array $options ): void
    {
        WP_CLI::line( "About to run command `wp " . join( " ", $args ) . "`" );
    }
    /**
     * Tell the user what's going on, before the command is invoked
     */
    public static function before_invoke(): void
    {
        WP_CLI::line( "Invoking command..." );
    }

    /**
     * Tell the user what's going on, after the command is invoked
     */
    public static function after_invoke(): void
    {
        WP_CLI::line( "All done 💪" );
    }
}
