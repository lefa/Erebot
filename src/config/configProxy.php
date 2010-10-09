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

include_once('src/exceptions/NotFound.php');
include_once('src/exceptions/InvalidValue.php');
include_once('src/config/moduleConfig.php');
include_once('src/i18n.php');

/**
 * \brief
 *      A configuration proxy which cascades settings.
 *
 * This class is used to build a hierarchy of configurations.
 * Each level in this hierarchy may override settings applied at
 * upper levels. If no override has been made on some setting,
 * that setting retains the value it had on the preceding level.
 * This allows settings to be cascaded in the hierarchy of
 * configuration levels.
 *
 * The root of the hierarchy acts as a proxy for itself and is
 * always an instance implementing iErebotMainConfig.
 */
class ErebotConfigProxy
{
    /// The current locale.
    protected $_locale;

    /// Reference to a proxified object.
    protected $_proxified;

    /// Array of modules loaded at this particular configuration level.
    protected $_modules;

    /**
     * Creates new instance of a ErebotConfigProxy object.
     *
     * \param $proxified
     *      An ErebotConfigProxy object which should be proxied through
     *      this instance. This allows settings to be cascaded.
     *
     * \param $xml
     *      A SimpleXMLElement representing the XML node which should
     *      be used as the basis for configuration.
     */
    protected function __construct(
        ErebotConfigProxy   &$proxified,
        SimpleXMLElement    &$xml
    )
    {
        $this->_proxified   =&  $proxified;
        $this->_modules     =   array();

        if (isset($xml['language']))
            $this->_locale = (string) $xml['language'];
        else
            $this->_locale = NULL;

        if (!isset($xml->modules->module))
            return;

        foreach ($xml->modules->module as $module) {
            $instance = new ErebotModuleConfig($module);
            $this->_modules[$instance->getName()] = $instance;
        }
    }

    /**
     * Destructor for ErebotConfigProxy instances.
     * Takes care of breaking possible circular references.
     */
    public function __destruct()
    {
        unset(
            $this->_modules,
            $this->_proxified
        );
    }

    // Documented in the interface.
    public function getTranslator($component)
    {
        if (isset($this->_locale))
            return new ErebotI18n($this->_locale, $component);
        if ($this->_proxified === $this)
            throw new EErebotNotFound('No translator associated');
        return $this->_proxified->getTranslator($component);
    }

    // Documented in the interface.
    public function & getMainCfg()
    {
        if ($this->_proxified === $this)
            return $this;
        return $this->_proxified->getMainCfg();
    }

    // Documented in the interface.
    public function getModules($recursive)
    {
        if (!is_bool($recursive))
            throw new EErebotInvalidValue('Invalid value for recursion');

        if ($recursive && $this->_proxified !== $this)
            $inherited = $this->_proxified->getModules(TRUE);
        else
            $inherited = array();

        $added      = array();
        $removed    = array();
        foreach ($this->_modules as $name => &$module) {
            if ($module->isActive())
                $added[]    = $name;
            else
                $removed[]  = $name;
        }
        unset($module);

        $inherited = array_diff($inherited, $removed);
        return array_merge($added, $inherited);
    }

    // Documented in the interface.
    public function & getModule($moduleName)
    {
        if (!isset($this->_modules[$moduleName])) {
            if ($this->_proxified !== $this)
                return $this->_proxified->getModule($moduleName);
            throw new EErebotNotFound('No such module');        
        }
        return $this->_modules[$moduleName];
    }

    /**
     * Parses a text and tries to extract a boolean value.
     *
     * \param $value
     *      The text from which a boolean should be extracted.
     *
     * \return
     *      If a boolean could be extracted from the $value provided,
     *      it is returned as the corresponding PHP boolean value
     *      (either TRUE or FALSE). If no boolean could be extracted,
     *      NULL is returned instead.
     *
     * \note
     *      Currently, the following texts are recognized as TRUE:
     *      "true", "1", "on" & "yes", while the values
     *      "false", "0", "off" & "no" are recognized as FALSE.
     *      The comparison is case-insensitive (ie. "true" == "TrUe").
     */
    protected function _parseBool($value)
    {
        $value = strtolower($value);
        if (in_array($value, array('true', '1', 'on', 'yes'), TRUE))
            return TRUE;
        if (in_array($value, array('false', '0', 'off', 'no'), TRUE))
            return FALSE;
        return NULL;
    }

    // Documented in the interface.
    public function parseBool($module, $param, $default = NULL)
    {
        try {
            if (!isset($this->_modules[$module]))
                throw new EErebotNotFound('No such module');
            $value = $this->_modules[$module]->getParam($param);
            $value = $this->_parseBool($value);
            if ($value !== NULL)
                return $value;
            throw new EErebotInvalidValue('Bad value in configuration');
        }
        catch (EErebotNotFound $e) {
            if ($this->_proxified !== $this)
                return $this->_proxified->parseBool($module, $param, $default);

            if ($default === NULL)
                throw new EErebotNotFound('No such parameter');

            if (is_bool($default))
                return $default;
            throw new EErebotInvalidValue('Bad default value');
        }
    }

    // Documented in the interface.
    public function parseString($module, $param, $default = NULL)
    {
        try {
            if (!isset($this->_modules[$module]))
                throw new EErebotNotFound('No such module');
            return $this->_modules[$module]->getParam($param);
        }
        catch (EErebotNotFound $e) {
            if ($this->_proxified !== $this)
                return $this->_proxified->parseString(
                    $module,
                    $param,
                    $default
                );

            if ($default === NULL)
                throw new EErebotNotFound('No such parameter');

            if (is_string($default))
                return $default;
            throw new EErebotInvalidValue('Bad default value');
        }
    }

    /**
     * Parses a text and tries to extract an integer value.
     *
     * \param $value
     *      The text from which an integer should be extracted.
     *
     * \return
     *      If an integer could be extracted from the $value provided,
     *      it is returned as the corresponding PHP (signed) integer value.
     *      If no integer could be extracted, NULL is returned instead.
     */
    protected function _parseInt($value)
    {
        if ($value == '')
            return NULL;

        if (is_int($value))
            return $value;

        if (ctype_digit($value))
            return (int) $value;

        if (strpos('+-', $value[0]) !== FALSE &&
            ctype_digit(substr($value, 1)))
            return (int) $value;

        return NULL;
    }

    // Documented in the interface.
    public function parseInt($module, $param, $default = NULL)
    {
        try {
            if (!isset($this->_modules[$module]))
                throw new EErebotNotFound('No such module');
            $value = $this->_modules[$module]->getParam($param);
            $value = $this->_parseInt($value);
            if ($value !== NULL)
                return $value;
            throw new EErebotInvalidValue('Bad value in configuration');
        }
        catch (EErebotNotFound $e) {
            if ($this->_proxified !== $this)
                return $this->_proxified->parseInt($module, $param, $default);

            if ($default === NULL)
                throw new EErebotNotFound('No such parameter');

            if (is_int($default))
                return $default;
            throw new EErebotInvalidValue('Bad default value');
        }
    }

    /**
     * Parses a text and tries to extract a real.
     *
     * \param $value
     *      The text from which a real should be extracted.
     *
     * \return
     *      If a real could be extracted from the $value provided,
     *      it is returned as the corresponding PHP double value.
     *      If no real could be extracted, NULL is returned instead.
     */
    protected function _parseReal($value)
    {
        if (!is_numeric($value))
            return NULL;

        return (double) $value;
    }

    // Documented in the interface.
    public function parseReal($module, $param, $default = NULL)
    {
        try {
            if (!isset($this->_modules[$module]))
                throw new EErebotNotFound('No such module');
            $value = $this->_modules[$module]->getParam($param);
            $value = $this->_parseReal($value);
            if ($value !== NULL)
                return $value;
            throw new EErebotInvalidValue('Bad value in configuration');
        }
        catch (EErebotNotFound $e) {
            if ($this->_proxified !== $this)
                return $this->_proxified->parseReal($module, $param, $default);

            if ($default === NULL)
                throw new EErebotNotFound('No such parameter');

            if (is_real($default))
                return $default;
            throw new EErebotInvalidValue('Bad default value');
        }
    }
}
