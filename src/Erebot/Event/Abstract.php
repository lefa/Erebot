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

/**
 * \brief
 *      An abstract Event.
 */
abstract class  Erebot_Event_Abstract
implements      Erebot_Event_Interface
{
    /// @TODO add support for these events or remove them...
#    const ON_CHAT           = 30;
#    const ON_CONNECTFAIL    = 50;
#    const ON_DCCSERVER      = 80;
#    const ON_ERROR          = 130;
#    const ON_FILERCVD       = 150;
#    const ON_FILESENT       = 160;
#    const ON_GETFAIL        = 170;
#    const ON_MODE           = 240;  // *
#    const ON_NOSOUND        = 260;
#    const ON_SENDFAIL       = 350;
#    const ON_SERV           = 360;
#    const ON_SERVERMODE     = 370;
#    const ON_SERVEROP       = 380;
#    const ON_SNOTICE        = 390;
#    const ON_WALLOPS        = 440;

    protected $_halt;
    protected $_connection;

    public function __construct(iErebotConnection &$connection)
    {
        $this->_halt        =   FALSE;
        $this->_connection  =&  $connection;
    }

    // Documented in the interface.
    public function & getConnection()
    {
        return $this->_connection;
    }

    // Documented in the interface.
    public function preventDefault($prevent = NULL)
    {
        $res = $this->_halt;
        if ($prevent !== NULL) {
            if (!is_bool($prevent))
                throw new Erebot_InvalidValueException('Bad prevention value');

            $this->_halt = $prevent;
        }
        return $res;
    }
}
