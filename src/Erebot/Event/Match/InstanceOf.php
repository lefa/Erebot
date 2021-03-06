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
 *      A filter that matches based on the type of event provided.
 */
class       Erebot_Event_Match_InstanceOf
implements  Erebot_Interface_Event_Match
{
    /// Type to use in comparisons, as a string.
    protected $_type;

    /**
     * Creates a new instance of this filter.
     *
     * \param string|object $type
     *      Type to match incoming events against.
     */
    public function __construct($type)
    {
        $this->setType($type);
    }

    /**
     * Returns the type associated with this filter.
     *
     * \retval array
     *      Array of types (as strings) associated with this filter.
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Sets the type used in comparisons.
     *
     * \param $types string|object|array
     *      Type(s) to match incoming events against,
     *      either as a string or as an instance
     *      of the type of match against.
     *      An array of strings/objects may also be passed;
     *      The filter will match if the incoming event
     *      is of any of the types given.
     *
     * \throw Erebot_InvalidValueException
     *      The given type is invalid.
     */
    public function setType($types)
    {
        if (!is_array($types))
            $types = array($types);

        $this->_type = array();
        foreach ($types as $type) {
            if (is_object($type))
                $type = get_class($type);

            if (!is_string($type))
                throw new Erebot_InvalidValueException('Not a valid type');

            $this->_type[] = $type;
        }
    }

    /// \copydoc Erebot_Interface_Event_Match::match()
    public function match(Erebot_Interface_Event_Base_Generic $event)
    {
        foreach ($this->_type as $type) {
            if ($event instanceof $type)
                return TRUE;
        }
        return FALSE;
    }
}

