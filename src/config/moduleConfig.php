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

include_once('src/ifaces/moduleConfig.php');

/**
 * \brief
 *      This class stores configuration data about modules.
 *
 * For each module at any configuration level, an instance of
 * ErebotModuleConfig will be created.
 */
class       ErebotModuleConfig
implements  iErebotModuleConfig
{
    /// A dictionary mapping parameter names to their textual value.
    protected $_params;

    /// A boolean indicating whether the module is active or not.
    protected $_active;

    /// The name of the module.
    protected $_name;

    // Documented in the interface.
    public function __construct(SimpleXMLElement &$xml)
    {
        $this->_name    = (string) $xml['name'];
        $this->_params  = array();
        $active         = strtoupper(
            isset($xml['active']) ?
            (string) $xml['active'] :
            'TRUE'
        );
        $this->_active  = in_array($active, array('1', 'TRUE', 'ON', 'YES'));

        foreach ($xml->param as $param) {
            $prm    = (string) $param['name'];
            $val    = (string) $param['value'];
            $this->_params[$prm] = $val;
        }
    }

    // Documented in the interface.
    public function isActive($active = NULL)
    {
        $res = $this->_active;
        if ($active !== NULL) {
            if (!is_bool($active))
                throw new EErebotInvalidValue('Invalid activation value');
            $this->_active = $active;
        }
        return $res;
    }

    // Documented in the interface.
    public function getName()
    {
        return $this->_name;
    }

    // Documented in the interface.
    public function getParam($param)
    {
        if (!is_string($param))
            throw new EErebotInvalidValue('Bad parameter name');
        if (!isset($this->_params[$param]))
            throw new EErebotNotFound('No such parameter');
        return $this->_params[$param];
    }

    // Documented in the interface.
    public function getParamsNames()
    {
        return array_keys($this->_params);
    }
}

