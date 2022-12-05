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
     * Restrict the <name> parameter to be longer than 3
     * characters
     */
    public static function validate(array $args, array $assoc_args, array $options): bool
    {
        $name = $args[0] ?? null;
        if ( $name && strlen( $name ) <= 3 ) {
            return false;
        }
        return true;
    }
}
