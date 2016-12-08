<?php

namespace Aventura\Wprss\Core\Model\Regex;

use Aventura\Wprss\Core\Model\Collection;
use Aventura\Wprss\Core\Model\Set;

/**
 * Aids in adaprting regular expressions for use with HTML
 * by HTML-encodifying them.
 * HTML-encodifying transforms a regex in a way that HTML special chars in the expression get replaced
 * with all possibilities, each of which has the same meaning as the special char.
 * For example, encodifying "<div>" would produce "(?:\<|&lt;)div(?:\<|&lt;)", which would match both
 * the text "<div>" and the text "&lt;div&lt;", where the latter is the HTML-encoded version
 * of the original text.
 * While quotes are transformed in the same way, but by default they are considered do be "symmetrical",
 * i.e. an "opening" quote must be matched with an identical "closing" quote.
 *
 * @since [*next-version*]
 */
class HtmlEncoder extends AbstractRegex
{
    const K_SYM_PREFIX = 'sym_prefix';
    const SYM_PREFIX = '_sym';

    protected $synonymSets;
//    protected $htmlCharMap = null;
    protected $symChars = array();

    protected $occurrences = array();
    protected $replacementCount = 0;

    /**
     * @since [*next-version*]
     */
    protected function _construct()
    {
        parent::_construct();
        $this->reset();

        $this->addHtmlChars(array('<', '>', '\''));
        $this->addHtmlCharSynonyms('"', array("'", '&#039;', '&apos;'));
        $this->addSymmetricalChars('"');
    }

    public function reset()
    {
//        $this->htmlCharMap = array();
        $this->occurrences = array();
        $this->replacementCount = 0;

        return $this;
    }

    /**
     * @since [*next-version*]
     *
     * @param string $expr The regex to encodify.
     *
     * @return string The regular expression that has been HTML-encodified.
     */
    public function encodify($expr)
    {
        $delimiter = $this->getDelimiter();
        $symPrefix = $this->getSymPrefix();
        $chars = $this->getSynonymSets();
        $symmetrical = $this->getSymmetricalChars();
        return $this->htmlEncodifyRegex($expr, $chars, $delimiter, $symmetrical, $symPrefix);
    }

    public function getSymPrefix()
    {
        return $this->_getDataOrConst(self::K_SYM_PREFIX);
    }

    /**
     * Retrieve all symmetrical chars.
     *
     * @since [*next-version*]
     *
     * @return array Array of strings, each one being a char that must be matched in pairs by the transformed regex.
     */
    public function getSymmetricalChars()
    {
        return $this->symChars;
    }

    /**
     * Adds chars that must be symmetrical, e.g. must me matched in pairs by the transformed regex
     *
     * @since [*next-version*]
     *
     * @param string|array $chars A char, string of chars, or array of chars, each of which to add.
     *
     * @return \Aventura\Wprss\Core\Model\Regex\HtmlEncoder This instance.
     */
    public function addSymmetricalChars($chars)
    {
        $chars = $this->normalizeCharArray($chars);

        $chars = array_merge($this->symChars, $chars);
        $chars = array_keys(array_flip($chars));
        $this->_setSymmetricalChars($chars);

        return $this;
    }

    /**
     * @since [*next-version*]
     *
     * @param type $chars
     *
     * @return \Aventura\Wprss\Core\Model\Regex\HtmlEncoder
     */
    protected function _setSymmetricalChars($chars)
    {
        $this->symChars = (array) $chars;
        return $this;
    }

    /**
     * @since [*next-version*]
     *
     * @return Set\Synonym\Set
     */
    public function getSynonymSets()
    {
        if (is_null($this->synonymSets)) {
            $this->_setSynonymSets(new Set\Synonym\Set());
        }

        return $this->synonymSets;
    }

    /**
     * @since [*next-version*]
     *
     * @param Set\Synonym\Set $set The synonym set.
     *
     * @return HtmlEncoder This instance.
     */
    protected function _setSynonymSets(Set\Synonym\Set $set)
    {
        $this->synonymSets = $set;

        return $this;
    }

//    public function getHtmlCharMap()
//    {
//        if (is_null($this->htmlCharMap)) {
//
//        }
//        return $this->htmlCharMap;
//    }

    /**
     * Checks whether or not a char is registered as being an HTML char.
     *
     * @since [*next-version*]
     *
     * @param string $char The char to check for.
     *
     * @return boolean True if this HTML char is registered; false otherwise.
     */
    public function hasHtmlChar($char)
    {
        $char = $this->normalizeChar($char);
        return $this->_hasHtmlChar($char);
    }

    protected function _hasHtmlChar($char)
    {
        return isset($this->htmlCharMap[$char]);
    }

    /**
     * Get the set of HTML char synonyms for a specific character.
     *
     * @see [*next-char*]
     *
     * @param string $char The char, for which to get the synonym set.
     *
     * @return Set\Synonym\Set The synonym set. This set might only contain the original char.
     */
    public function getHtmlCharSynonymSet($char)
    {
        $char = $this->normalizeChar($char);
        return $this->getSynonymSets()->getSetForTerm($char);
    }

    public function getHtmlCharSynonyms($char, $other = null)
    {
        if (is_null($other)) {
            $other = array();
        }

        $set = $this->getHtmlCharSynonymSet($char);
        $synonyms = $set->items();
        return array_keys(array_flip(array_merge($synonyms, $other)));
    }

//    public function setHtmlCharSynonyms($char, $synonyms)
//    {
//        $chars = str_split($char);
//        foreach ($chars as $char) {
//            $this->_mapHtmlChar($char, $synonyms);
//        }
//
//        return $this;
//    }

    public function addHtmlCharSynonyms($char, $synonyms)
    {
//        $char = $this->normalizeChar($char);
        $set = $this->getHtmlCharSynonymSet($char);
        $set->addMany($synonyms);
//        var_dump($this->getSynonymSets());

        return $this;
    }

    public function removeHtmlChar($char)
    {
//        $char = $this->normalizeChar($char);
        $synonyms = array_flip($this->getHtmlCharSynonyms($char));
        if (array_key_exists($char, $synonyms)) {
            unset($synonyms[$char]);
        }
        $synonyms = array_keys($synonyms);
        $this->_mapHtmlChar($char, $synonyms);

        return $this;
    }

    public function addHtmlChar($char, $synonyms = null)
    {
        $this->getHtmlCharSynonymSet($char);
//        var_dump($this->getSynonymSets());

        return $this;
    }

    public function addHtmlChars($chars)
    {
        foreach ($chars as $_char) {
            $this->getHtmlCharSynonymSet($_char);
        }

        return $this;
    }

    public function normalizeChar($char)
    {
        if (is_array($char)) {
            if (isset($char[0])) {
                return $char[0];
            }
            throw $this->exception('Could not normalize char: input is array, but index 0 is empty');
        }
        return substr($char, 0, 1);
    }

    public function normalizeCharArray($chars)
    {
        if (is_array($chars)) {
            return $chars;
        }
        return str_split($chars);
    }

    public function _incrementOccurrence($char)
    {
        $char = $this->normalizeChar($char);
        $current = $this->getOccurrenceCount($char);
        $this->occurrences[$char] = ++$current;

        return $this;
    }

    public function getOccurrenceCount($char)
    {
        return isset($this->occurrences[$char])
            ? (int) $this->occurrences[$char]
            : 0;
    }

    public function _incrementReplacement()
    {
        $this->replacementCount++;
    }

    public function getReplacementCount()
    {
        return $this->replacementCount;
    }

    public function getAutoSynonyms($char)
    {
        return array(htmlspecialchars($char, /* ENT_HTML401 | */ ENT_QUOTES));
    }

    /**
     * HTML-encodify a regex expresion to match HTML-encoded variants of the strings.
     *
     * @since [*next-version*]
     *
     * @param string $expr The expression to HTML-encodify.
     * @param Set\Synonym\Set $synonymSets A set of synonym sets.
     *  Each char in any of the synonym sets will be encodified.
     * @param string|null $delimiter The delimiter used by the regex. Default: ! (exclamation mark).
     * @param array|string|null $symmetricChars Chars that come in pairs
     *
     * @return type
     */
    public function htmlEncodifyRegex($expr, $synonymSets, $delimiter, $symmetricChars, $symPrefix)
    {
        $chars = iterator_to_array($synonymSets->getAllItems());
        $chars = $this->quoteAll($chars, $delimiter);
        $charsRegex = sprintf('%2$s(%1$s)%2$s', implode('|', $chars), $delimiter);
        $me = $this;

        $result = $this->replaceCallback(
            $charsRegex, // Replace potentially encoded symbols that have significance in HTML
            function($matches) use ($delimiter, $symmetricChars, $symPrefix, $synonymSets, $me) {
                if (!isset($matches[0])) {
                    return null;
                }
                $char = $matches[0]; // Only one character will match
                /* The return value should be a regular expression that
                 * represents the HTML-significant char and all of its
                 * HTML-encoded alternatives.
                 */
                $me->_incrementOccurrence($char);
                $currentOccurrence = $me->getOccurrenceCount($char);
                $synonymSet = $synonymSets->getSetForTerm($char);
                $synonyms = $synonymSet->getSynonyms($char);
                $synonyms = array_merge(array($char), $synonyms, $me->getAutoSynonyms($char));
                $synonyms = array_unique($synonyms);
                $synonyms = $me->quoteAll($synonyms, $delimiter);

                // Dealing with symmetric chars
                $return = '';
                $isSymmetric = $synonymSet->hasOneOf($symmetricChars);
                if ($isSymmetric && $currentOccurrence) {
                    // if even occurrence, replace with lookbehind of the previous occurrence
                    $return = $currentOccurrence % 2
                        ? sprintf('(?<%2$s%3$s>%1$s)', implode('|', $synonyms), $symPrefix, $currentOccurrence)
                        : sprintf('\\g{%1$s%2$s}', $symPrefix, $currentOccurrence-1);
                }
                else {
                    // The final substitute regex, which by default is non-capturing
                    $return = sprintf('(?:%1$s)', implode('|', $synonyms));
                }

                $me->_incrementReplacement();
                return $return;
            },
            $expr);
        return $result;
    }

    /**
     * Cleans an array of matches from symmetric char matches.
     *
     * Those matches are added as a result of HTML-encodifying a regular expressions,
     * and may cause desired matches to not appear at their normal indexes.
     *
     * @since [*next-version*]
     *
     * @param array $matches The matches array to clean.
     * @param string $symPrefix The symmetric char match name prefix used by the matcher function.
     *
     * @return array The matches array with symmetrical character matches removed.
     */
    public function cleanMatches(array $matches, $symPrefix = null)
    {
        if (is_null($symPrefix)) {
            $symPrefix = $this->getSymPrefix();
        }

        /**
         * Iterating through all matches.
         * http://php.net/manual/en/control-structures.foreach.php#88578
         */
        while (list($key, $value) = each($matches))  {
            // Is this a symmetrical char match key?
            if (!$this->isSymKey($key, $symPrefix)) {
                continue;
            }

            $keys = array_keys($matches);
            // The index of the current key
            $keyIndex = array_search($key, $keys, true);
            // The next key
            $nextKey = $keys[$keyIndex+1];

            if (array_key_exists($nextKey, $matches)) {
                // Remove the numeric copy of the symmetric char match
                unset($matches[$nextKey]);
            }
            // Remove the symmetric char match
            unset($matches[$key]);
        }

        $matches = array_merge($matches);
        return $matches;
    }

    public function isSymKey($key, $symPrefix = null)
    {

        if (is_null($symPrefix)) {
            $symPrefix = $this->getSymPrefix();
        }

        return $this->_isStringStartsWith($key, $symPrefix);
    }

    protected function _isStringStartsWith($string, $start)
    {
        $startLength = strlen($start);
        return ($actualStart = substr($string, 0, $startLength)) === $start;
    }
}