<?php

include_once(dirname(__FILE__).'/exceptions/Exception.php');
include_once(dirname(__FILE__).'/exceptions/NotFound.php');

/**
 * \brief
 *      Utility methods for Erebot.
 */
class ErebotUtils
{
    /// Do not strip anything from the text.
    const STRIP_NONE        = 0x00;
    /// Strip (mIRC/pIRCh) colors from the text.
    const STRIP_COLORS      = 0x01;
    /// Strip the bold attribute from the text.
    const STRIP_BOLD        = 0x02;
    /// Strip the underline attribute from the text.
    const STRIP_UNDERLINE   = 0x04;
    /// Strip the reverse attribute from the text.
    const STRIP_REVERSE     = 0x08;
    /// Strip the reset control character from the text.
    const STRIP_RESET       = 0x10;
    /// Strip extended colors from the text.
    const STRIP_EXT_COLORS  = 0x20;
    /// Strip all forms of styles from the text.
    const STRIP_ALL         = 0xFF;

    /**
     * Includes the given file, relative to the including file.
     *
     * \param $file
     *      The file to include. If given a relative path, it is
     *      interpreted as relative to the including script (ie.
     *      the one who called this method).
     *
     * \param $required
     *      (optional) Indicates whether the soon to-be-included
     *      file is required (TRUE) or not (FALSE).
     *      A required file will cause a fatal error if it cannot
     *      be included (eg. because it does not exist).
     *      The default is to mark all files as required.
     *
     * \return
     *      Returns the value of the included/required script.
     *      See http://php.net/include for more information on
     *      return values with include/require.
     */
    static public function incl($file, $required = FALSE)
    {
        if ((!strncasecmp(PHP_OS, 'Win', 3) && strpos($file, ':') !== FALSE) ||
            substr($file, 0, 1) == DIRECTORY_SEPARATOR)
            $path = $file;

        else {
            $bt     = debug_backtrace();
            $path   = dirname($bt[0]['file']).DIRECTORY_SEPARATOR.$file;
        }

        if ($required)
            return require_once($path);

        return include_once($path);
    }

    /**
     * Returns the object (if any) associated with the method
     * that called the method which called ErebotUtils::getCallerObject().
     *
     * Consider the following example:
     * \code
     *      class Foo {
     *          public function bar() {
     *              var_dump(ErebotUtils::getCallerObject());
     *          }
     *      }
     *      class Bar {
     *          public function baz() {
     *              $foo = new Foo();
     *              $foo->bar();
     *          }
     *      }
     *      $bar = new Bar();
     *      $bar->baz(); // Prints something like "object(Bar)#2 (0) {}".
     * \endcode
     *
     * \return
     *      Returns the caller object of the method which called
     *      ErebotUtils::getCallerObject().
     */
    static public function getCallerObject()
    {
        $bt     = debug_backtrace();
        $caller = isset($bt[2]['object']) ? $bt[2]['object'] : NULL;
        return $caller;
    }

    /**
     * Returns the number of chunks (tokens) obtained
     * after splitting the given text with the given
     * separator.
     *
     * \param $text
     *      The text to split and from which the number
     *      of tokens should be returned.
     *
     * \param $separator
     *      (optional) The separator to use to split
     *      the text. Defaults to a whitespace (' ').
     *
     * \return
     *      The number of tokens obtained after splitting
     *      the text.
     */
    static public function numtok($text, $separator = ' ')
    {
        $string = preg_replace('/\\s+/', ' ', $text);
        return count(explode($separator, $string));
    }

    /**
     * Returns a string with some of the chunks (tokens)
     * obtained after splitting the given text with the
     * given separator. The $start and $length parameters
     * are used to determine what chunks are returned.
     *
     * \param $text
     *      The text to split and from which the tokens
     *      should be returned.
     *
     * \param $start
     *      Offset of the first chunk to return (starting at 0).
     *      If negative, it starts at the end of the text.
     *
     * \param $length
     *      Number of chunks to return in the new string.
     *      If set to 0 (the default), returns all chunks from
     *      $start onward until the end of the text.
     *
     * \param $separator
     *      (optional) The separator to use to split
     *      the text. Defaults to a whitespace (' ').
     *
     * \return
     *      A new string with at most $length tokens (if
     *      $length > 0) and its whitespaces squeezed.
     */
    static public function gettok($text, $start, $length = 0, $separator = ' ')
    {
        $string = preg_replace('/\\s+/', ' ', $text);
        $parts     = explode($separator, $string);

        if (!$length)
            $parts = array_slice($parts, $start);
        else
            $parts = array_slice($parts, $start, $length);

        if (!count($parts))
            return NULL;

        return implode($separator, $parts);
    }

    /**
     * Strips IRC styles from a text.
     *
     * \param $text
     *      The text from which styles must be stripped.
     *
     * \param $strip
     *      A bitwise OR of the codes of the styles we want to strip.
     *      The default is to strip all forms of styles from the text.
     *      See also the ErebotUtils::STRIP_* constants.
     *
     * \return
     *      The text with all the styles specified in $strip stripped.
     */
    static public function stripCodes($text, $strip = self::STRIP_ALL)
    {
        if (!is_int($strip))
            throw new EErebotInvalidValue("Invalid stripping flags");

        if ($strip & self::STRIP_BOLD)
            $text = str_replace("\002", '', $text);

        if ($strip & self::STRIP_COLORS)
            $text = preg_replace("/\003(?:[0-9]{1,2}(?:,[0-9]{1,2})?)?/", '', $text);

        /// @TODO: strip extended colors.

        if ($strip & self::STRIP_RESET)
            $text = str_replace("\017", '', $text);

        if ($strip & self::STRIP_REVERSE)
            $text = str_replace("\026", '', $text);

        if ($strip & self::STRIP_UNDERLINE)
            $text = str_replace("\037", '', $text);

        return $text;
    }

    /// @TODO improve this code, eg. root = 'abc/def/' & path = 'ghi'
    static public function resolveRelative($root, $path)
    {
        $prefix = '';

        // Windows
        if (!strncasecmp(PHP_OS, 'Win', 3)) {
            $pos    = strpos($root, ':');
            if ($pos !== FALSE) {
                $prefix = substr($root, 0, $pos + 2);
                $root   = substr($root, $pos + 1);
            }
        }

        if (substr($root, -1) == DIRECTORY_SEPARATOR)
            $root = substr($root, 0, -1);

        $path_parts = explode(DIRECTORY_SEPARATOR, $path);
        $root_parts = explode(DIRECTORY_SEPARATOR, $root);

        foreach ($path_parts as $part) {
            if ($part == '.')
                continue;

            if ($part != '..')
                $root_parts[] = $part;

            else if (count($root_parts) > 0)
                array_pop($root_parts);
        }

        return $prefix.implode(DIRECTORY_SEPARATOR, $root_parts);
    }

    /**
     * Given some user's full IRC identity (nick!ident\@host),
     * this methods extracts and returns that user's nickname.
     *
     * \param $source
     *      Some user's full IRC identity (as "nick!ident\@host").
     *
     * \return
     *      The nickname of the user represented by that identity.
     *
     * \note
     *      This method will still work as expected if given
     *      only a nickname to work with. Therefore, it is safe
     *      to call this method with the result of a previous
     *      invocation. Thus, the following snippet:
     *      ErebotUtils::extractNick(ErebotUtils::extractNick('foo!bar\@baz'));
     *      will return "foo" as expected.
     */
    static public function extractNick($source)
    {
        if (strpos($source, '!') === FALSE)
            return $source;
        return substr($source, 0, strpos($source, '!'));
    }

    /**
     * Can be used to determine if a string contains a sequence
     * of valid UTF-8 encoded codepoints.
     *
     * \param $text
     *      Some text to test for UTF-8 correctness.
     *
     * \return
     *      Returns TRUE if the $text contains a valid UTF-8
     *      sequence of codepoints, FALSE otherwise.
     */
    static public function isUTF8($text)
    {
        // From http://w3.org/International/questions/qa-forms-utf-8.html
        // Pointed out by bitseeker on http://php.net/utf8_encode
        return (bool) preg_match('%^(?:
              [\x09\x0A\x0D\x20-\x7E]            # ASCII
            | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
            |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
            |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
            |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
        )*$%xs', $text);
    }

    /**
     * Transforms the given text into a UTF-8 sequence.
     *
     * \param $text
     *      The text to convert into a UTF-8 sequence.
     *
     * \param $from
     *      (optional) The encoding currently used by $text.
     *      A default of "iso-8859-1" is assumed.
     *
     * \return
     *      The original text, converted into UTF-8.
     *
     * \throw EErebotNotImplemented
     *      Raised if no method could be found to convert
     *      the text. See also the notes for information on
     *      how to avoid this exception being raised.
     *
     * \note
     *      This method tries different technics to convert
     *      the text. If despite its best efforts, it still
     *      fails, you may consider installing one of PHP's
     *      extension for "Human Language and Character 
     *      Encoding Support".
     */
    static public function toUTF8($text, $from='iso-8859-1')
    {
        if (ErebotUtils::isUTF8($text))
            return $text;

        if (!strcasecmp($from, 'iso-8859-1') &&
            function_exists('utf8_encode'))
            return utf8_encode($text);

        if (function_exists('iconv'))
            return iconv($from, 'UTF-8//TRANSLIT', $text);

        if (function_exists('recode'))
            return recode($from.'..utf-8', $text);

        if (function_exists('mb_convert_encoding'))
            return mb_convert_encoding($text, 'UTF-8', $from);

        if (function_exists('html_entity_decode'))
            return html_entity_decode(
                htmlentities($text, ENT_QUOTES, $from),
                ENT_QUOTES, 'UTF-8');

        throw new EErebotNotImplemented('No way to convert to UTF-8');
    }

    static public function getVStatic($class, $variable)
    {
        if (is_object($class))
            $class = get_class($class);
        $refl = new ReflectionClass($class);
        if (!$refl->hasConstant($variable))
            throw new EErebotNotFound('No such constant');
        return $refl->getConstant($variable);
    }
}

?>
