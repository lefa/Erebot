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

$oldErrorReporting = error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
require('File/Gettext.php');
error_reporting($oldErrorReporting);
unset($oldErrorReporting);

include_once('src/ifaces/i18n.php');

/**
 * \brief
 *      A class which provides translations for
 *      messages used by the core and modules.
 */
class ErebotI18n
implements iErebotI18n
{
    static protected $_cache = array();

    /// The locale for which messages are translated.
    protected $_locale;

    /// The component to get translations from (a module name or "Erebot").
    protected $_component;

    protected $_parser;

    // Documented in the interface.
    public function __construct($locale, $component)
    {
        $this->_locale = str_replace('-', '_', $locale);
        $this->_component = $component;
    }

    // Documented in the interface.
    public function getLocale()
    {
        return $this->_locale;
    }

    protected function real_gettext($message, $component)
    {
        if ($component == 'Erebot')
            $translationFile = dirname(dirname(__FILE__)).'/i18n/';
        else
            $translationFile = dirname(dirname(__FILE__)).
                '/modules/'.$component.'/i18n/';
        $translationFile .= $this->_locale.'/LC_MESSAGES/'.$component.'.mo';

        if (version_compare(PHP_VERSION, '5.3.0', '>='))
            clearstatcache(FALSE, $translationFile);
        else
            clearstatcache();
        $mtime = filemtime($translationFile);

        if (!isset(self::$_cache[$translationFile]) ||
            $mtime !== self::$_cache[$translationFile]['mtime']) {
            $oldErrorReporting = error_reporting(E_ALL & ~E_DEPRECATED);
            $parser =& File_Gettext::factory('MO', $translationFile);
            $parser->load();
            error_reporting($oldErrorReporting);
            self::$_cache[$translationFile] = array(
                'mtime'     => $mtime,
                'strings'   => $parser->strings,
            );
        }

        if (isset(self::$_cache[$translationFile]['strings'][$message]))
            return self::$_cache[$translationFile]['strings'][$message];
        return $message;
    }

    // Documented in the interface.
    public function gettext($message)
    {
        return $this->real_gettext($message, $this->_component);
    }

    // Documented in the interface.
    public function formatDuration($duration)
    {
        /**
         * @HACK: We need to translate the rule using $this->gettext
         * while specifying "Erebot" as the application. xgettext would
         * extract "Erebot" as the message to translate without this hack.
         */
        $gettext = create_function('$a', 'return $a;');
        $rule = $gettext("
%with-words:
    0: 0 seconds;
    1: 1 second;
    2: =#0= seconds;
    60/60: <%%min<;
    61/60: <%%min<, >%with-words>;
    3600/60: <%%hr<;
    3601/60: <%%hr<, >%with-words>;
    86400/86400: <%%day<;
    86401/86400: <%%day<, >%with-words>;
    604800/604800: <%%week<;
    604801/604800: <%%week<, >%with-words>;
%%min:
    1: 1 minute;
    2: =#0= minutes;
%%hr:
    1: 1 hour;
    2: =#0= hours;
%%day:
    1: 1 day;
    2: =#0= days;
%%week:
    1: 1 week;
    2: =#0= weeks;
");

        $fmt = new NumberFormatter($this->_locale,
                    NumberFormatter::PATTERN_RULEBASED,
                    $this->real_gettext($rule, "Erebot"));
        return $fmt->format($duration);
    }
}
