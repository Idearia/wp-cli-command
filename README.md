Simple class to create WP-CLI commands with validation with little effort.

# Features

- Create a command by extending a simple class
- Advanced validation
- Support for sub-commands
- Support for [PHPDoc validation](https://make.wordpress.org/cli/handbook/guides/commands-cookbook/#annotating-with-phpdoc)

# Quick start

1. Require `wp-cli-command` in your project:
   ```
   composer require idearia/wp-cli-command
   ```
2. Create a new command by extending the `Command` class. How? Have a look at the [examples folder](./examples) 🙂
3. Register your command with `MyCommand::init( 'my-command' );`
4. Run the command with `wp my-command`.

# Example

- A simple command from [WP-CLI docs](https://make.wordpress.org/cli/handbook/guides/commands-cookbook/#annotating-with-phpdoc) with an additional layer of validation: [examples/SimpleCommand.php](examples/SimpleCommand.php)
- An example with two sub-commands, each with its own validation > TODO!
