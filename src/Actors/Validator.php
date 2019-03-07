<?php

namespace Csv2Qif\Actors;

use Csv2Qif\Console\Output;
use Csv2Qif\Event\Hook;
use Csv2Qif\RuleSet\Rules\Rules\Rule;
use Csv2Qif\RuleSet\Rules\Rules\RuleHasReason;
use Csv2Qif\RuleSet\RuleSetValidator;

class Validator
{
    /** @var Hook */
    private $hook;

    /** @var Output */
    private $output;

    /** @var RuleSetValidator */
    private $validator;

    public function __construct(Hook $hook, Output $output, RuleSetValidator $validator)
    {
        $this->hook      = $hook;
        $this->output    = $output;
        $this->validator = $validator;
    }

    public function validate(string $ruleSet, int $verbose = 0): int
    {
        if ($verbose > 0) {
            $this->addVerboseHooks($verbose);
        }

        // Magic happens here
        $errorCount = $this->validator->validateAll($ruleSet);

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
        $this->hook->listen(RuleSetValidator::VALIDATE_ERROR, function (string $event, string $message) {
            $this->output->writeln("<red>{$message}</red>");
        });

        if ($verbose > 1) {
            $this->addVerbose2Hooks($verbose);
        }
    }

    private function addVerbose2Hooks(int $verbose): void
    {
        $this->hook->listen(RuleSetValidator::VALIDATE_MATCHER_VALID, function (string $event, string $name) {
            $this->output->writeln("<green>Matcher {$name} is valid.</green>");
        });

        if ($verbose > 2) {
            $this->addVerbose3Hooks($verbose);
        }
    }

    private function addVerbose3Hooks(int $verbose): void
    {
        $this->hook->listen(RuleSetValidator::VALIDATE_MATCHER_START, function (string $event, string $name) {
            $this->output->newline();
            $this->output->writeln("<yellow>Validating matcher {$name}.</yellow>");
        });

        $this->hook->listen(RuleSetValidator::VALIDATE_RULE_ERROR, function (string $event, Rule $rule) {
            $error = 'Invalid rule: ' . $rule->getOrigin();
            $reason = '';

            if ($rule instanceof RuleHasReason) {
                $reason = ' ' . $rule->getReason();
            }

            $this->output->writeln("<red>{$error}</red>{$reason}");
        });

        // Currently no more levels, isn't it enough already?
    }
}
