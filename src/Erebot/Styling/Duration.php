<?php
/*
    This file is part of Erebot.

    Erebot is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Erebot is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Erebot.  If not, see <http://www.gnu.org/licenses/>.
*/

class       Erebot_Styling_Duration
implements  Erebot_Interface_Styling_Variable
{
    protected $_value;

    /**
     * Formats a duration according to the rules
     * of the current locale.
     *
     * \param int $value
     *      The duration to format, given in seconds.
     *
     * \retval string
     *      A representation of the duration according to
     *      the rules of the current locale.
     */
    public function __construct($value)
    {
        $this->_value       = $value;
    }

    static protected function gettext($a) {
        return $a;
    }

    public function render(Erebot_Interface_I18n $translator)
    {
        $locale = $translator->getLocale(Erebot_Interface_I18n::LC_MESSAGES);
        $coreTranslator = new Erebot_I18n("Erebot");
        $coreTranslator->setLocale(Erebot_Interface_I18n::LC_MESSAGES, $locale);

        // DO NOT CHANGE THE CODE BELOW, ESPECIALLY COMMENTS & WHITESPACES.
        // It has all been carefully crafted to make both xgettext and
        // PHP_CodeSniffer happy! Also, it avoids relying on OS line endings
        // as it breaks xgettext on at least Windows platforms.

        // I18N: ICU rule used to format durations (with words).
        $rule = self::gettext( //
        // Eg. 12345 becomes "3 hours, 25 minutes, 45 seconds" (in english).
        // For examples of valid rules, see:
        // http://userguide.icu-project.org/formatparse/numbers/rbnf-examples
        // For the complete syntaxe, see also:
        // http://icu-project.org/apiref/icu4c/classRuleBasedNumberFormat.html
            "%with-words:\n".
            "    0: 0 seconds;\n".
            "    1: 1 second;\n".
            "    2: =#0= seconds;\n".
            "    60/60: <%%min<;\n".
            "    61/60: <%%min<, >%with-words>;\n".
            "    3600/60: <%%hr<;\n".
            "    3601/60: <%%hr<, >%with-words>;\n".
            "    86400/86400: <%%day<;\n".
            "    86401/86400: <%%day<, >%with-words>;\n".
            "    604800/604800: <%%week<;\n".
            "    604801/604800: <%%week<, >%with-words>;\n".
            "%%min:\n".
            "    1: 1 minute;\n".
            "    2: =#0= minutes;\n".
            "%%hr:\n".
            "    1: 1 hour;\n".
            "    2: =#0= hours;\n".
            "%%day:\n".
            "    1: 1 day;\n".
            "    2: =#0= days;\n".
            "%%week:\n".
            "    1: 1 week;\n".
            "    2: =#0= weeks;"
        );

        $formatter = new NumberFormatter(
            $locale,
            NumberFormatter::PATTERN_RULEBASED,
            $coreTranslator->gettext($rule)
        );
        return (string) $formatter->format($this->_value);
    }

    public function getValue()
    {
        return $this->_value;
    }
}
