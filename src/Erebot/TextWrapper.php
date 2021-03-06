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
 *      A wrapper to easily split a string using a separator and
 *      deal with other operations related to separators.
 */
class       Erebot_TextWrapper
implements  Erebot_Interface_TextWrapper
{
    /// The text wrapped by this instance.
    protected $_text;

    /// Position in the text.
    protected $_position;

    /**
     * Constructs a new instance of a text wrapper.
     *
     * \param string $text
     *      The text to wrap.
     */
    public function __construct($text)
    {
        $this->_text        = $text;
        $this->_position    = 0;
    }

    /// \copydoc Erebot_Interface_TextWrapper::getTokens()
    public function getTokens($start, $length = 0, $separator = ' ')
    {
        $string = preg_replace('/\\s+/', ' ', trim($this->_text, $separator));
        $parts  = explode($separator, $string);

        if (!$length)
            $parts = array_slice($parts, $start);
        else
            $parts = array_slice($parts, $start, $length);

        if (!count($parts))
            return "";

        return implode($separator, $parts);
    }

    /// \copydoc Erebot_Interface_TextWrapper::countTokens()
    public function countTokens($separator = ' ')
    {
        $string = preg_replace('/\\s+/', ' ', trim($this->_text, $separator));
        return count(explode($separator, $string));
    }

    /// \copydoc Erebot_Interface_TextWrapper::__toString()
    public function __toString()
    {
        return $this->_text;
    }

    /**
     * \copydoc Countable::count()
     * \see
     *      docs/additions/iface_Countable.php
     */
    public function count()
    {
        return $this->countTokens();
    }

    /**
     * \copydoc Iterator::current()
     * \see
     *      docs/additions/iface_Iterator.php
     */
    public function current()
    {
        return $this->getTokens($this->_position, 1);
    }

    /**
     * \copydoc Iterator::key()
     * \see
     *      docs/additions/iface_Iterator.php
     */
    public function key()
    {
        return $this->_position;
    }

    /**
     * \copydoc Iterator::next()
     * \see
     *      docs/additions/iface_Iterator.php
     */
    public function next()
    {
        $this->_position++;
    }

    /**
     * \copydoc Iterator::rewind()
     * \see
     *      docs/additions/iface_Iterator.php
     */
    public function rewind()
    {
        $this->_position = 0;
    }

    /**
     * \copydoc Iterator::valid()
     * \see
     *      docs/additions/iface_Iterator.php
     */
    public function valid()
    {
        return ($this->_position < $this->countTokens());
    }

    /**
     * \copydoc ArrayAccess::offsetExists()
     * \see
     *      docs/additions/iface_ArrayAccess.php
     */
    public function offsetExists($offset)
    {
        return (
            is_int($offset) &&
            $offset >= 0 &&
            $offset < $this->countTokens()
        );
    }

    /**
     * \copydoc ArrayAccess::offsetGet()
     * \see
     *      docs/additions/iface_ArrayAccess.php
     */
    public function offsetGet($offset)
    {
        if (!is_int($offset))
            return NULL;
        return $this->getTokens($offset, 1);
    }

    /**
     * \copydoc ArrayAccess::offsetSet()
     * \see
     *      docs/additions/iface_ArrayAccess.php
     */
    public function offsetSet($offset, $value)
    {
        throw new RuntimeException('The wrapped text is read-only');
    }

    /**
     * \copydoc ArrayAccess::offsetUnset()
     * \see
     *      docs/additions/iface_ArrayAccess.php
     */
    public function offsetUnset($offset)
    {
        throw new RuntimeException('The wrapped text is read-only');
    }
}

