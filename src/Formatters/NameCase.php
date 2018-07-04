<?php

namespace TimMcLeod\LaravelCoreLib\Formatters;


class NameCase
{
    public static $defaultDelimiters = [
        ' ', '-', "O'", "O’", "L'", "L’", "D'", "D’", 'St.', 'Mc', 'Mac', '(', '"', '*', '.'
    ];

    public static $defaultForceLowercase = [
        'the', 'van', 'den', 'von', 'und', 'der', 'de', 'da', 'of', 'and', "l'", "l’", "d'", "d’"
    ];

    public static $defaultForceUppercase = [
        'II', 'III', 'IV', 'VI', 'VII', 'VIII', 'IX'
    ];

    /**
     * Accepts a name as a string, and returns a properly cased version of the name.
     * Example: Input: MCLEOD, Output: McLeod
     * -
     * Modified version of the function posted here:
     * http://www.media-division.com/correct-name-capitalization-in-php/
     * Used with permission from Liviu Niculescu under MIT License.
     * -
     * @param $str
     * @param array|null $delimiters Split words on these strings.
     * @param array|null $forceLowercase Force these words to be uppercase.
     * @param array|null $forceUppercase Force these words to be lowercase.
     * @return string
     */
    public static function format($str, $delimiters = null, $forceLowercase = null, $forceUppercase = null)
    {
        $str = strtolower($str);
        $delimiters = is_null($delimiters) ? static::$defaultDelimiters : $delimiters;

        $forceLowercase = is_null($forceLowercase) ? static::$defaultForceLowercase : $forceLowercase;
        $forceLowercase = array_map('strtolower', $forceLowercase);

        $forceUppercase = is_null($forceUppercase) ? static::$defaultForceUppercase : $forceUppercase;
        $forceUppercase = array_map('strtoupper', $forceUppercase);

        foreach ($delimiters as $delimiter)
        {
            $words = explode($delimiter, $str);
            $newWords = [];

            foreach ($words as $word)
            {
                if (in_array(strtoupper($word), $forceUppercase))
                {
                    $word = strtoupper($word);
                }
                elseif (!in_array($word, $forceLowercase))
                {
                    $word = ucfirst($word);
                }

                $newWords[] = $word;
            }

            if (in_array(strtolower($delimiter), $forceLowercase))
            {
                $delimiter = strtolower($delimiter);
            }

            $str = join($delimiter, $newWords);
        }

        return $str;
    }
}