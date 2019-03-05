<?php

namespace Csv2Qif\Console;

use Throwable;

/**
 * TODO: Remove this intermediate class when parable-php/console#2 is merged.
 */
class Output extends \Parable\Console\Output
{
    public function parseTags(string $line): string
    {
        $tags = $this->getTagsFromString($line);

        foreach ($tags as $tag) {
            try {
                $code = $this->getCodeFor($tag);
            } catch (Throwable $throwable) {
                continue;
            }

            $line = str_replace("<{$tag}>", $code, $line);
            $line = str_replace("</{$tag}>", $this->predefinedTags['default'], $line);
        }

        return $line . $this->predefinedTags['default'];
    }
}
