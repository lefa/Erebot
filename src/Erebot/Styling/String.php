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
 *      A class used to format strings.
 *
 * \note
 *      Actually, strings are rendered "as is",
 *      without any special formatting applied,
 *      so this class can safely be used as a
 *      passthrough.
 */
class       Erebot_Styling_String
implements  Erebot_Interface_Styling_String
{
    /// The value to format.
    protected $_value;

    /**
     * Constructor.
     *
     * \param string $value
     *      The value to format.
     *      It must support conversions to the
     *      string type.
     */
    public function __construct($value)
    {
        $this->_value = $value;
    }

    /// \copydoc Erebot_Interface_Styling_Variable::render()
    public function render(Erebot_Interface_I18n $translator)
    {
        return (string) $this->_value;
    }

    /// \copydoc Erebot_Interface_Styling_Variable::getValue()
    public function getValue()
    {
        return $this->_value;
    }
}

