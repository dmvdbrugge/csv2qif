<?php

namespace Command;

use Parable\Console\Command;
use RuleSet\RuleSetConverter;

class Config extends Command
{
    private const OPT_FORCE = 'force';

    protected $name = 'config';

    protected $description = 'Converts config from Parable Config to Yaml';

    /** @var RuleSetConverter */
    private $converter;

    public function __construct(RuleSetConverter $converter)
    {
        $this->addOption(self::OPT_FORCE);

        $this->converter = $converter;
    }

    public function run(): void
    {
        $force = (bool) $this->parameter->getOption(self::OPT_FORCE);

        $this->converter->convert($force);
    }
}
