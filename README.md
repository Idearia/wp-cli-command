Simple class to create WP-CLI commands with validation with little effort.

# Features

- Create a WP CLI command by extending a simple class
- Easily hook into the [command lifecycle](https://make.wordpress.org/cli/handbook/references/internal-api/wp-cli-add-hook/#notes) by extending methods `before_run_command`, `before_invoke`, `after_invoke`
- Advanced validation by extending method `validate`
- Support for sub-commands by defining public methods
- Support for [PHPDoc validation](https://make.wordpress.org/cli/handbook/guides/commands-cookbook/#annotating-with-phpdoc)

# Quick start

1. Require `wp-cli-command` in your project:
   ```
   composer require idearia/wp-cli-command
   ```
2. Create a new command by extending the `Command` class. How? Have a look at the [examples folder](./examples) 🙂
3. Register your command with `MyCommand::init( 'my-command' );`
4. Run the command with `wp my-command`.

# Available hooks

In oder of execution:

- `before_run_command` > Just before the command is found and executed
- `before_invoke` > Just before a command is invoked
- `after_invoke` > Just after a command is invoked

# Example

- A simple command from [WP-CLI docs](https://make.wordpress.org/cli/handbook/guides/commands-cookbook/#annotating-with-phpdoc) with an additional layer of validation: [examples/SimpleCommand.php](./examples/SimpleCommand.php)
- An example with two sub-commands, each with its own validation > TODO!

# To do

- Make hook methods aware of the specific command being executed, in case the class contains more than one. Probably, we will need to switch away from static, in order to save $args in the class.
- Behat tests with [`wp scaffold package`](https://github.com/wp-cli/scaffold-package-command)
- Example with subcommands
- Find a way to execute PHPDoc validation before custom validation (maybe invoking the command and the exiting with `before_invoke:{$cmd}`)?
- Find a way to print actual usage, instead of relying on $usage property (how to force show_usage in subcommand.php)?