<?php

namespace Transactions\IngTransaction;

use function array_key_exists;
use function preg_match;

class Notes
{
    /** @var string */
    public $name = '';

    /** @var string */
    public $description = '';

    /** @var string */
    public $iban = '';

    /** @var string */
    public $reference = '';

    /** @var string */
    public $authorizationId = '';

    /** @var string */
    public $debtorId = '';

    /** @var string */
    public $source = '';

    /** @var string */
    private static $regex = '';

    /**
     * The values of these constants should be the same as the property name
     */
    private const FIELD_NAAM          = 'name';
    private const FIELD_OMSCHRIJVING  = 'description';
    private const FIELD_IBAN          = 'iban';
    private const FIELD_KENMERK       = 'reference';
    private const FIELD_MACHTIGING_ID = 'authorizationId';
    private const FIELD_INCASSANT_ID  = 'debtorId';

    /**
     * This maps the properties to how they are specified in the notes
     */
    private const HEADINGS = [
        self::FIELD_NAAM          => 'Naam',
        self::FIELD_OMSCHRIJVING  => 'Omschrijving',
        self::FIELD_IBAN          => 'IBAN',
        self::FIELD_KENMERK       => 'Kenmerk',
        self::FIELD_MACHTIGING_ID => 'Machtiging ID',
        self::FIELD_INCASSANT_ID  => 'Incassant ID',
    ];

    public function __construct(string $notes)
    {
        $this->source = $notes;

        if (!preg_match(self::getRegex(), $notes, $matches)) {
            return;
        }

        foreach ($matches as $key => $match) {
            if (array_key_exists($key, self::HEADINGS)) {
                // The key is one we specified, set its property to the match
                $this->{$key} = $match;
            }
        }
    }

    private static function getRegex(): string
    {
        if (!empty(self::$regex)) {
            return self::$regex;
        }

        $regex = '/^'; // Start the regex

        foreach (self::HEADINGS as $field => $heading) {
            $group = "(?P<{$field}>.*)";       // Create a named capture group, name/key == property
            $part  = "{$heading}: {$group} ?"; // Create the full part to match
            $regex .= "({$part})?";            // Make the full part optional and append to the regex
        }

        $regex .= '$/U'; // Finish the regex, Un-greedy so not everything is captured by the first matching field

        return self::$regex = $regex;
    }
}
