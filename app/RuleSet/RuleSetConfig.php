<?php

namespace RuleSet;

use RuleSet\Exceptions\RuleSetConfigException;
use Symfony\Component\Yaml\Yaml;

use function array_reduce;
use function explode;
use function is_readable;
use function preg_match;

class RuleSetConfig
{
    /** @var array */
    private static $configs = [
        '' => [],
    ];

    /** @var array|null */
    private static $available;

    /** @var array */
    private $config = [];

    /**
     * Reads a config for the given rule set.
     *
     * It must exist as `{$ruleSet}.config.yml` in the project root dir.
     */
    public function setRuleSet(string $ruleSet): void
    {
        if (isset(self::$configs[$ruleSet])) {
            $this->config = &self::$configs[$ruleSet];
        }

        $filename = "{$ruleSet}.config.yml";

        if (!is_readable($filename)) {
            throw RuleSetConfigException::unreadable($filename);
        }

        self::$configs[$ruleSet] = Yaml::parseFile($filename);

        $this->config = &self::$configs[$ruleSet];
    }

    /**
     * Returns whatever is at the requested index in the config, or the default.
     *
     * Supports nested look-ups in the form of `toplevel.secondlevel.third.etc`.
     */
    public function get(string $index, $default = null)
    {
        $config = $this->config;

        foreach (explode('.', $index) as $part) {
            if (!isset($config[$part])) {
                return $default;
            }

            $config = $config[$part];
        }

        return $config;
    }

    /**
     * @return string[]
     */
    public static function getAvailableRuleSets(): array
    {
        if (self::$available !== null) {
            return self::$available;
        }

        $reduce = function (array &$carry, string $filename): array {
            if (preg_match('/^(.*)\.config\.yml$/', $filename, $matches)) {
                $carry[] = $matches[1];
            }

            return $carry;
        };

        return self::$available = array_reduce(scandir('.', 0), $reduce, []);
    }
}
