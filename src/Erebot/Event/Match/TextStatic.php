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

class   Erebot_Event_Match_TextStatic
extends Erebot_Event_Match_TextAbstract
{
    protected function _match($prefix, $text)
    {
        $text       = preg_replace('/\s+/', ' ', $text);
        $pattern    = preg_replace('/\s+/', ' ', (string) $this->_pattern);

        // Prefix forbidden.
        if ($this->_requirePrefix === FALSE)
            return ($pattern == $text);

        $matched    = ($text == $prefix.$pattern);
        // Prefix required.
        if ($this->_requirePrefix === TRUE)
            return $matched;

        // Prefix allowed.
        return ($matched || $pattern == $text);
    }
}

