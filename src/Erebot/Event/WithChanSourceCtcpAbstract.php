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
 *      An abstract CTCP Event which applies to a channel
 *      and contains some text.
 */
abstract class  ErebotEventWithChanSourceAndCtcp
extends         ErebotEventWithChanSourceAndText
implements      iErebotEventCtcp
{
    protected $_ctcpType;
    
    public function __construct(
        iErebotConnection   &$connection,
                            $chan,
                            $source,
                            $ctcpType,
                            $text
    )
    {
        parent::__construct($connection, $chan, $source, $text);
        $this->ctcpType =& $_ctcpType;
    }

    // Documented in the interface.
    public function & getCtcpType()
    {
        return $this->_ctcpType;
    }
}
