<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Create a login from the command line, e.g. the first admin after install:
 *
 *   bin/cake create_user admin --email you@example.com --first-name Jo --last-name Bloggs
 *
 * Replaces the legacy web installer (/setup), which was reachable without login.
 */
class CreateUserCommand extends Command
{
    public static function defaultName(): string
    {
        return 'create_user';
    }

    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return $parser
            ->setDescription('Create an admin or manager login.')
            ->addArgument('username', ['required' => true])
            ->addOption('role', ['default' => 'admin', 'choices' => ['admin', 'manager']])
            ->addOption('email', ['required' => true])
            ->addOption('first-name', ['default' => 'Admin'])
            ->addOption('last-name', ['default' => 'User'])
            ->addOption('password', ['help' => 'Omit to be prompted (recommended; keeps it out of shell history).']);
    }

    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $password = (string)($args->getOption('password') ?? $io->ask('Password (6-32 characters)'));

        $users = $this->fetchTable('Users');
        $user = $users->newEntity([
            'username' => $args->getArgument('username'),
            'password' => $password,
            'role' => $args->getOption('role'),
            'email' => $args->getOption('email'),
            'first_name' => $args->getOption('first-name'),
            'last_name' => $args->getOption('last-name'),
        ]);
        if (!$users->save($user)) {
            foreach ($user->getErrors() as $field => $errors) {
                $io->error($field . ': ' . implode(', ', $errors));
            }

            return static::CODE_ERROR;
        }
        $io->success(sprintf('Created %s user "%s".', $user->role, $user->username));

        return static::CODE_SUCCESS;
    }
}
