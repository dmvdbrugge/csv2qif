<?php

namespace Csv2Qif\RuleSet;

use Csv2Qif\RuleSet\Exceptions\RuleSetConfigException;
use Csv2Qif\RuleSet\Rules\RulesFactory;
use InvalidArgumentException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

use function array_reduce;
use function explode;
use function is_readable;
use function preg_match;
use function preg_quote;
use function sprintf;
use function str_replace;

class RuleSetConfig
{
    /**
     * The %s will be replaced by the name of the rule set.
     */
    public const FILENAME_FORMAT = '%s.csv2qif.yml';

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
     * It must exist as file in the project root dir.
     *
     * @see RuleSetConfig::FILENAME_FORMAT
     *
     * @throws RuleSetConfigException
     */
    public function setRuleSet(string $ruleSet): void
    {
        if (isset(self::$configs[$ruleSet])) {
            $this->config = &self::$configs[$ruleSet];
        }

        $filename = sprintf(self::FILENAME_FORMAT, $ruleSet);

        if (!is_readable($filename)) {
            throw RuleSetConfigException::unreadable($filename);
        }

        try {
            $parsedYaml = Yaml::parseFile($filename);

            foreach ($parsedYaml['matchers'] ?? [] as $name => $matcher) {
                $rules = $matcher['rules'] ?? [];

                $parsedYaml['matchers'][$name]['rules'] = RulesFactory::create(['allof' => $rules]);
            }

            self::$configs[$ruleSet] = &$parsedYaml;
        } catch (ParseException $e) {
            throw RuleSetConfigException::invalidYaml($e);
        } catch (InvalidArgumentException $e) {
            throw RuleSetConfigException::invalidConfig($filename, $e);
        }

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

        $format = preg_quote(self::FILENAME_FORMAT, '/');
        $format = str_replace('%s', '(.*)', $format);
        $format = '/^' . $format . '$/';

        $reduce = function (array &$carry, string $filename) use ($format): array {
            if (preg_match($format, $filename, $matches)) {
                $carry[] = $matches[1];
            }

            return $carry;
        };

        return self::$available = array_reduce(scandir('.', 0), $reduce, []);
    }
}
