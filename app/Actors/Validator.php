<?php

namespace Actors;

use Event\Hook;
use Parable\Console\Output;
use Parable\DI\Container;
use RuleSet\Rules\RulesValidator;
use RuleSet\RuleSetValidator;

use function is_array;
use function is_scalar;

class Validator
{
    /** @var Hook */
    private $hook;

    /** @var Output */
    private $output;

    public function __construct(Hook $hook, Output $output)
    {
        $this->hook   = $hook;
        $this->output = $output;
    }

    public function validate(string $ruleSet, int $verbose = 0): int
    {
        // Prepare things
        $validator = Container::get(RuleSetValidator::class);

        if ($verbose > 0) {
            $this->addVerboseHooks($verbose);
        }

        // Magic happens here
        $errorCount = $validator->validateAll($ruleSet);

        // Print out final info
        if ($errorCount) {
            if ($verbose > 0) {
                $this->output->newline();
            }

            $this->output->writeln("<red>Ruleset {$ruleSet} is invalid: {$errorCount} validation errors.</red>");
        } else {
            if ($verbose > 1) {
                $this->output->newline();
            }

            $this->output->writeln("<green>Ruleset {$ruleSet} is valid.</green>");
        }

        return $errorCount;
    }

    /* ******************** *\
    |* *** Such Verbose *** *|
    \* ******************** */

    private function addVerboseHooks(int $verbose): void
    {
        $this->hook->into(RuleSetValidator::VALIDATE_ERROR, function (string $event, string $message) {
            $this->output->writeln("<red>{$message}</red>");
        });

        if ($verbose > 1) {
            $this->addVerbose2Hooks($verbose);
        }
    }

    private function addVerbose2Hooks(int $verbose): void
    {
        $this->hook->into(RuleSetValidator::VALIDATE_MATCHER_VALID, function (string $event, string $name) {
            $this->output->writeln("<green>Matcher {$name} is valid.</green>");
        });

        if ($verbose > 2) {
            $this->addVerbose3Hooks($verbose);
        }
    }

    private function addVerbose3Hooks(int $verbose): void
    {
        $this->hook->into(RuleSetValidator::VALIDATE_MATCHER_START, function (string $event, string $name) {
            $this->output->newline();
            $this->output->writeln("<yellow>Validating matcher {$name}.</yellow>");
        });

        $this->hook->into(RulesValidator::VALIDATE_ERROR, function (string $event, $rule) {
            if (empty($rule)) {
                $this->output->writeln('<red>Empty rule.</red>');

                return;
            }

            if (is_scalar($rule)) {
                $this->output->writeln("<red>Invalid rule: {$rule}</red>");

                return;
            }

            if (!is_array($rule)) {
                $this->output->writeln('<red>Invalid rule.</red>');

                return;
            }

            $error = 'Invalid rule:';

            foreach ($rule as $rulePart) {
                if (is_scalar($rulePart)) {
                    $error .= " {$rulePart}";
                } else {
                    break;
                }
            }

            $this->output->writeln("<red>{$error}</red>");
        });

        // Currently no more levels, isn't it enough already?
    }
}
