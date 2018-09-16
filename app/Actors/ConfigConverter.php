<?php

namespace Csv2Qif\Actors;

use Csv2Qif\RuleSet\Rules\RulesConverter;
use Csv2Qif\RuleSet\RuleSetConfig;
use Csv2Qif\Transactions\IngTransaction;
use Parable\Framework\Config;
use Symfony\Component\Yaml\Yaml;

use function file_exists;
use function file_put_contents;
use function is_writable;
use function sprintf;
use function str_replace;

class ConfigConverter
{
    /** @var Config */
    private $config;

    /** @var RulesConverter */
    private $converter;

    public function __construct(Config $config, RulesConverter $converter)
    {
        $this->config    = $config;
        $this->converter = $converter;
    }

    public function convert(bool $force = false)
    {
        $fakeTransaction        = new IngTransaction();
        $fakeTransaction->notes = new IngTransaction\Notes('Fake notes ;)');

        $replacements = [
            "'\n" => "\n",
            "':"  => ':',
            " '"  => ' ',
            "''"  => "'",
        ];

        foreach ($this->config->get('csv2qif') as $ruleSetName => $ruleSet) {
            foreach ($ruleSet['matchers'] as $matcherName => $matcher) {
                ['all of' => $converted] = $this->converter->allOf($fakeTransaction, ...$matcher['rules']);

                $ruleSet['matchers'][$matcherName]['rules'] = $converted;
            }

            $filename = sprintf(RuleSetConfig::FILENAME_FORMAT, $ruleSetName);
            $yaml     = Yaml::dump($ruleSet, 99, 4, Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE);

            foreach ($replacements as $needle => $replacement) {
                $yaml = str_replace($needle, $replacement, $yaml);
            }

            if (!$force && file_exists($filename)) {
                throw new \Exception("File {$filename} already exists, run with --force to override!");
            }

            if (file_exists($filename) && !is_writable($filename)) {
                throw new \Exception("File {$filename} is not writable, make sure to run with the right permissions!");
            }

            if (!file_exists($filename) && !is_writable('.')) {
                throw new \Exception('Project root is not writable, make sure to run with the right permissions!');
            }

            file_put_contents($filename, $yaml);
        }
    }
}