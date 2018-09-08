<?php

namespace Command;

use Actors\ConfigConverter;
use Parable\Console\Command;

class Config extends Command
{
    private const OPT_FORCE = 'force';

    protected $name = 'config';

    protected $description = 'Converts config from Parable Config (PHP) to RuleSetConfig (Yaml)';

    /** @var ConfigConverter */
    private $converter;

    public function __construct(ConfigConverter $converter)
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
