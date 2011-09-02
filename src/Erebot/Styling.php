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
 *      Provides styling (formatting) features.
 *
 *  Given a format string (a template), this class can perform
 *  styling on that template to produce complex messages.
 *
 *  Most of the interface for this class is the same as what
 *  the Smarty templating engine uses, so if you're familiar
 *  with Smarty, you should have no problem using this class.
 *
 *  A template is composed of a single string, which may contain
 *  special markup to insert dynamic content, add formatting
 *  attributes to the text (like bold, underline, colors), etc.
 *
 *  <table>
 *      <caption>Special markup in templates</caption>
 *
 *      <tr>
 *          <th>Markup</th>
 *          <th>Role</th>
 *      </tr>
 *
 *      <tr>
 *          <td>&lt;b&gt;...&lt;/b&gt;</td>
 *          <td>The text is rendered in \b{bold}</td>
 *      </tr>
 *
 *      <tr>
 *          <td>&lt;u&gt;...&lt;/u&gt;</td>
 *          <td>The text is rendered \u{underlined}</td>
 *      </tr>
 *
 *      <tr>
 *          <td>&lt;var name="..."/&gt;</td>
 *          <td>This markup gets replaced by the content
 *              of the given variable</td>
 *      </tr>
 *
 *      <tr>
 *          <td>
 *              &lt;color<br/>
 *                  &nbsp;&nbsp;fg="..."<br/>
 *                  &nbsp;&nbsp;bg="..."&gt;<br/>
 *                  &nbsp;&nbsp;&nbsp;&nbsp;...<br/>
 *              &lt;/color&gt;
 *          </td>
 *          <td>The text is rendered with the given foreground (\a fg)
 *              and background (\a bg) colors. The value of the \a fg and
 *              \a bg attributes may be either an integer (see the COLOR_*
 *              constants in this class) or the name of the color (again,
 *              supported colors are named after the COLOR_* constants).</td>
 *      </tr>
 *
 *      <tr>
 *          <td>
 *              &lt;for<br/>
 *                  &nbsp;&nbsp;from="..."<br/>
 *                  &nbsp;&nbsp;item="..."<br/>
 *                  &nbsp;&nbsp;key="..."<br/>
 *                  &nbsp;&nbsp;sep=&quot;,&nbsp;&quot;<br/>
 *                  &nbsp;&nbsp;last=&quot;&nbsp;&amp;amp;&nbsp;&quot;&gt;<br/>
 *                  &nbsp;&nbsp;&nbsp;&nbsp;...<br/>
 *              &lt;/for&gt;
 *          </td>
 *          <td>This markup loops over the associative array in \a from.
 *              The key for each entry in that array is stored in the
 *              temporary variable named by the \a key attribute if given,
 *              while the associated value is stored in the temporary
 *              variable named by \a item. The value of \a sep (alias
 *              \a separator) is appended automatically between each entry
 *              of the array, except between the last two entries.
 *              The value of \a last (alias \a last_separator) is used
 *              to separate the last two entries.
 *              By default, no temporary variable is created for the key,
 *              ", " is used as the main \a separator and " & " is used as
 *              the \a last_separator.</td>
 *      </tr>
 *
 *      <tr>
 *          <td>
 *              &lt;plural var="..."&gt;<br/>
 *                  &nbsp;&nbsp;&lt;case form="..."&gt;<br/>
 *                      &nbsp;&nbsp;&nbsp;&nbsp;...<br/>
 *                  &nbsp;&nbsp;&lt;/case&gt;<br/>
 *              &lt;/plural&gt;
 *          </td>
 *          <td>Handles plurals. Depending on the value of the variable
 *              pointed by \a var, one of the cases will be used. The page at
 * http://unicode.org/cldr/data/charts/supplemental/language_plural_rules.html
 *              references every available form per language.</td>
 *      </tr>
 *  </table>
 */
class       Erebot_Styling
implements  Erebot_Interface_Styling
{
    /// Translator to use for plurals.
    protected $_translator;

    /// Associative array of variables to pass to the template.
    protected $_variables;

    /// Parser used to interpret the template.
    protected $_dom;

    // Control codes.

    /// Mark the following text as being bold.
    const CODE_BOLD         = "\002";
    /// Change the color of the following text.
    const CODE_COLOR        = "\003";
    /// Reset the styles.
    const CODE_RESET        = "\017";
    /// Swao the foreground & background colors.
    const CODE_REVERSE      = "\026";
    /// Underline the text that follows.
    const CODE_UNDERLINE    = "\037";


    // mIRC/PIRCH colors.

    /// White.
    const COLOR_WHITE       = 0;
    /// Black.
    const COLOR_BLACK       = 1;
    /// Blue.
    const COLOR_BLUE        = 2;
    /// Alias for Erebot_Styling::COLOR_NAVY_BLUE.
    const COLOR_NAVY_BLUE   = 2;
    /// Alias for Erebot_Styling::COLOR_DARK_BLUE.
    const COLOR_DARK_BLUE   = 2;
    /// Green.
    const COLOR_GREEN       = 3;
    /// Alias for Erebot_Styling::COLOR_DARK_GREEN.
    const COLOR_DARK_GREEN  = 3;
    /// Red.
    const COLOR_RED         = 4;
    /// Brown.
    const COLOR_BROWN       = 5;
    /// Purple.
    const COLOR_PURPLE      = 6;
    /// Orange.
    const COLOR_ORANGE      = 7;
    /// Alias for Erebot_Styling::COLOR_ORANGE (sounds odd, doesn't it?).
    const COLOR_OLIVE       = 7;
    /// Yellow.
    const COLOR_YELLOW      = 8;
    /// Light green.
    const COLOR_LIGHT_GREEN = 9;
    /// Alias for Erebot_Styling::COLOR_LIGHT_GREEN.
    const COLOR_LIME_GREEN  = 9;
    /// Cyan.
    const COLOR_CYAN        = 10;
    /// Alias for Erebot_Styling::COLOR_CYAN.
    const COLOR_TEAL        = 10;
    /// Alias for Erebot_Styling::COLOR_CYAN.
    const COLOR_DARK_CYAN   = 10;
    /// Light cyan.
    const COLOR_LIGHT_CYAN  = 11;
    /// Alias for Erebot_Styling::COLOR_LIGHT_CYAN.
    const COLOR_AQUA_LIGHT  = 11;
    /// Light blue.
    const COLOR_LIGHT_BLUE  = 12;
    /// Alias for Erebot_Styling::COLOR_LIGHT_BLUE.
    const COLOR_ROYAL_BLUE  = 12;
    /// Pink.
    const COLOR_PINK        = 13;
    /// Alias for Erebot_Styling::COLOR_HOT_PINK.
    const COLOR_HOT_PINK    = 13;
    /// Gray.
    const COLOR_GRAY        = 14;
    /// Alias for Erebot_Styling::COLOR_GRAY.
    const COLOR_GREY        = 14;
    /// Alias for Erebot_Styling::COLOR_GRAY.
    const COLOR_DARK_GRAY   = 14;
    /// Alias for Erebot_Styling::COLOR_GRAY.
    const COLOR_DARK_GREY   = 14;
    /// Light gray.
    const COLOR_LIGHT_GRAY  = 15;
    /// Alias for Erebot_Styling::COLOR_LIGHT_GRAY.
    const COLOR_LIGHT_GREY  = 15;

    /**
     * Construct a new styling object.
     *
     * \param string $source
     *      The template which will be used to produce
     *      the final message.
     *
     * \param Erebot_Interface_I18n $translator
     *      A translator object, used to determine the correct
     *      pluralization rules.
     */
    public function __construct($source, Erebot_Interface_I18n $translator)
    {
        $source =
            '<msg xmlns="http://www.erebot.net/xmlns/erebot/styling">'.
            $source.
            '</msg>';

        $dataDir = '@data_dir@';
        // Running from the repository.
        if ($dataDir == '@'.'data_dir'.'@')
            $dataDir = dirname(dirname(dirname(__FILE__))) .
                        DIRECTORY_SEPARATOR . 'data';
        else
            $dataDir .= DIRECTORY_SEPARATOR . 'peat.erebot.net' .
                        DIRECTORY_SEPARATOR . 'Erebot';

        $schema = $dataDir . DIRECTORY_SEPARATOR . 'styling.rng';

        $this->_translator  = $translator;
        $this->_dom         =   new Erebot_DOM();
        $this->_variables   =   array();

        $ue = libxml_use_internal_errors(TRUE);
        $this->_dom->loadXML($source);
        $valid  = $this->_dom->relaxNGValidate($schema);
        $errors = $this->_dom->getErrors();
        libxml_use_internal_errors($ue);

        if (!$valid || count($errors)) {
            // Some unpredicted error occurred,
            // show some (hopefully) useful information.
            $errmsg     =   print_r($errors, TRUE);
            $logging    =&  Plop::getInstance();
            $logger     =   $logging->getLogger(__FILE__);
            $logger->error($errmsg);
            throw new Erebot_InvalidValueException(
                'Error while validating the message'
            );
        }
    }

    /**
     * Add a value to a variable which will be passed
     * to the template as an array.
     * Unlike Erebot_Styling::append_by_ref(),
     * this method assigns the variable by value.
     *
     * \param string $varname
     *      Name of the array variable to set.
     *
     * \param mixed $var
     *      Value to append to the array.
     *
     * \param mixed $merge
     *      Whether to merge the values (TRUE)
     *      or not (FALSE).
     *
     * \TODO
     *      Implement merging.
     */
    public function append($varname, $var, $merge = NULL)
    {
        if (!is_array($var)) {
            $this->_variables[$varname][] = $var;
            return;
        }
    }

    /**
     * Add a value to a variable which will be passed
     * to the template as an array.
     * Unlike Erebot_Styling::append(), this method
     * assigns the variable by reference.
     *
     * \param string $varname
     *      Name of the array variable to set.
     *
     * \param mixed $var
     *      Value to append to the array.
     *
     * \param mixed $merge
     *      Whether to merge the values (TRUE)
     *      or not (FALSE).
     *
     * \TODO
     *      Implement merging.
     */
    public function append_by_ref($varname, &$var, $merge = NULL)
    {
        if (!is_array($var)) {
            $this->_variables[$varname][] =& $var;
        }
    }

    /**
     * Assign a value to a variable which will be
     * passed to the template.
     * Unlike Erebot_Styling::assign_by_ref(), this
     * method assigns the variable by value.
     *
     * \param string $name
     *      Name of the variable to assign.
     *
     * \param mixed $value
     *      Value for that variable.
     */
    public function assign($name, $value)
    {
        $this->_variables[$name] = $value;
    }

    /**
     * Assign a value to a variable which will be
     * passed to the template.
     * Unlike Erebot_Styling::assign(), this method
     * assigns the variable by reference.
     *
     * \param string $name
     *      Name of the variable to assign.
     *
     * \param mixed $value
     *      Value for that variable, as a reference.
     */
    public function assign_by_ref($name, &$value)
    {
        $this->_variables[$name] =& $value;
    }

    /**
     * Unsets any previous value assigned to
     * the templates' variables.
     */
    public function clear_all_assign()
    {
        unset($this->_variables);
        $this->_variables = array();
    }

    /**
     * Unsets any previous value assigned to
     * a given variable.
     *
     * \param string $varname
     *      Name of the variable to unset.
     */
    public function clear_assign($varname)
    {
        unset($this->_variables[$varname]);
    }

    /**
     * Renders the template using assigned
     * variables.
     *
     * \retval string
     *      The formatted result for this template.
     */
    public function render()
    {
        $attributes = array(
            'underline' => 0,
            'bold'      => 0,
            'bg'        => NULL,
            'fg'        => NULL,
        );
        $variables  = $this->_variables;
        $result     = $this->_parseNode(
            $this->_dom->documentElement,
            $attributes,
            $variables
        );
        $pattern =  '@'.
                    '\\003,(?![01])'.
                    '|'.
                    '\\003(?:[0-9]{2})?,(?:[0-9]{2})?(?:\\002\\002)?(?=\\003)'.
                    '|'.
                    '(\\003(?:[0-9]{2})?,)\\002\\002(?![0-9])'.
                    '|'.
                    '(\\003[0-9]{2})\\002\\002(?!,)'.
                    '@';
        $replace    = '\\1\\2';
        $result     = preg_replace($pattern, $replace, $result);
        return $result;
    }

    /**
     * Returns either all variables assigned to the template,
     * or the value assigned to a particular variable.
     *
     * \param NULL|string $varname
     *      If given, this must be the name of a variable
     *      assigned to the template.
     *
     * \retval mixed
     *      If $varname was given, returns the current value
     *      assigned to the variable which goes by that name.
     * \retval dict(string=>mixed)
     *      Otherwise, returns all variables currently assigned
     *      to this template, as an associative array mapping
     *      the variables' names to their values.
     */
    public function get_template_vars($varname = NULL)
    {
        if ($varname === NULL)
            return $this->_variables;
        return $this->_variables[$varname];
    }

    /**
     * This is the main parsing method.
     *
     * \param DOMNode $node
     *      The node being parsed.
     *
     * \param array $attributes
     *      Array of styling attributes.
     *
     * \param array $variables
     *      Template variables that can be injected in the return.
     *
     * \retval string
     *      Parsing result, with styles applied as appropriate.
     */
    protected function _parseNode($node, &$attributes, $variables)
    {
        $result     = '';
        $saved      = $attributes;

        if ($node->nodeType == XML_TEXT_NODE)
            return $node->nodeValue;

        if ($node->nodeType != XML_ELEMENT_NODE)
            return '';

        // Pre-handling.
        switch ($node->tagName) {
            case 'var':
                $varname = $node->getAttribute('name');
                return (string) $variables[$varname];

            case 'u':
                if (!$attributes['underline'])
                    $result .= self::CODE_UNDERLINE;
                $attributes['underline'] = 1;
                break;

            case 'b':
                if (!$attributes['bold'])
                    $result .= self::CODE_BOLD;
                $attributes['bold'] = 1;
                break;

            case 'color':
                $colors     = array('', '');
                $mapping    = array('fg', 'bg');

                foreach ($mapping as $pos => $color) {
                    $value = $node->getAttribute($color);
                    if ($value != '') {
                        $value = str_replace(array(' ', '-'), '_', $value);
                        try {
                            if (!ctype_digit($value))
                                $value = Erebot_Utils::getVStatic(
                                    $this, 'COLOR_'.strtoupper($value)
                                );
                            $attributes[$color] = sprintf('%02d', $value);
                            if ($attributes[$color] != $saved[$color])
                                $colors[$pos] = $attributes[$color];
                        }
                        catch (Erebot_NotFoundException $e) {
                            throw new Erebot_InvalidValueException(
                                'Invalid color "'.$value.'"'
                            );
                        }
                    }
                }

                $code = implode(',', $colors);
                if ($colors[0] != '' && $colors[1] != '')
                    $result .= self::CODE_COLOR.$code;
                else if ($code != ',')
                    $result .= self::CODE_COLOR.rtrim($code, ',').
                                self::CODE_BOLD.self::CODE_BOLD;
                break;
        }

        // Handle loops.
        if ($node->tagName == 'for') {
            $savedVariables = $variables;
            $separator      = array(', ', ' & ');

            foreach (array('separator', 'sep') as $attr) {
                $attrNode       = $node->getAttributeNode($attr);
                if ($attrNode !== FALSE) {
                    $separator[0] = $separator[1] = $attrNode->nodeValue;
                    break;
                }
            }

            foreach (array('last_separator', 'last') as $attr) {
                $attrNode       = $node->getAttributeNode($attr);
                if ($attrNode !== FALSE) {
                    $separator[1] = $attrNode->nodeValue;
                    break;
                }
            }

            $loopKey    = $node->getAttribute('key');
            $loopItem   = $node->getAttribute('item');
            $loopFrom   = $node->getAttribute('from');
            $count      = count($variables[$loopFrom]);
            reset($variables[$loopFrom]);

            for ($i = 1; $i < $count; $i++) {
                if ($i > 1)
                    $result .= $separator[0];

                $item = each($variables[$loopFrom]);
                if ($loopKey !== NULL)
                    $variables[$loopKey] = $item['key'];
                $variables[$loopItem] = $item['value'];

                $result .= $this->_parseChildren(
                    $node,
                    $attributes,
                    $variables
                );
            }

            $item = each($variables[$loopFrom]);
            if ($loopKey !== NULL)
                $variables[$loopKey] = $item['key'];
            $variables[$loopItem] = $item['value'];
            if ($count > 1)
                $result .= $separator[1];

            $result .= $this->_parseChildren($node, $attributes, $variables);
            $variables = $savedVariables;
        }

        // Handle plurals.
        else if ($node->tagName == 'plural') {
            /* We don't need the full set of features/complexity/bugs
             * ICU contains. Here, we use a simple "plural" formatter
             * to detect the right plural form to use. The formatting
             * steps are done before without using ICU. */
            $attrNode = $node->getAttributeNode('var');
            if ($attrNode === FALSE)
                throw new Erebot_InvalidValueException(
                    'No variable name given'
                );
            $value = (int) $variables[$attrNode->nodeValue];
            $subcontents = array();
            $pattern = '{0,plural,';
            for (   $child = $node->firstChild;
                    $child != NULL;
                    $child = $child->nextSibling) {
                if ($child->nodeType != XML_ELEMENT_NODE ||
                    $child->tagName != 'case')
                    continue;
                // See this class documentation for a link
                // which lists available forms for each language.
                $form = $child->getAttribute('form');
                $subcontents[$form] = $this->_parseNode(
                    $child, $attributes, $variables
                );
                $pattern .= $form.'{'.$form.'} ';
            }
            $pattern .= '}';
            $locale = $this->_translator->getLocale(
                Erebot_Interface_I18n::LC_MESSAGES
            );
            $formatter = new MessageFormatter($locale, $pattern);
            // HACK: PHP <= 5.3.3 returns NULL when the pattern in invalid
            // instead of throwing an exception.
            // See http://bugs.php.net/bug.php?id=52776
            if ($formatter === NULL)
                throw new Erebot_InvalidValueException('Invalid plural forms');
            $correctForm = $formatter->format(array($value));
            $result .= $subcontents[$correctForm];
        }

        // Handle childrens.
        else
            $result .= $this->_parseChildren($node, $attributes, $variables);

        // Post-handling : restore old state.
        switch ($node->tagName) {
            case 'u':
                if (!$saved['underline'])
                    $result .= self::CODE_UNDERLINE;
                $attributes['underline'] = 0;
                break;

            case 'b':
                if (!$saved['bold'])
                    $result .= self::CODE_BOLD;
                $attributes['bold'] = 0;
                break;

            case 'color':
                $colors     = array('', '');
                $mapping    = array('fg', 'bg');

                foreach ($mapping as $pos => $color) {
                    if ($attributes[$color] != $saved[$color])
                        $colors[$pos] = $saved[$color];
                    $attributes[$color] = $saved[$color];
                }

                $code = implode(',', $colors);
                if ($colors[0] != '' && $colors[1] != '')
                    $result .= self::CODE_COLOR.$code;
                else if ($code != ',')
                    $result .= self::CODE_COLOR.rtrim($code, ',').
                                self::CODE_BOLD.self::CODE_BOLD;
                break;
        }

        return $result;
    }

    /**
     * This method is used to apply the parsing method
     * to children of an XML node.
     *
     * \param DOMNode $node
     *      The node being parsed.
     *
     * \param array $attributes
     *      Array of styling attributes.
     *
     * \param array $variables
     *      Template variables that can be injected in the result.
     *
     * \retval string
     *      Parsing result, with styles applied as appropriate.
     */
    private function _parseChildren($node, &$attributes, $variables)
    {
        $result = '';
        for (   $child = $node->firstChild;
                $child != NULL;
                $child = $child->nextSibling) {
            $result .=  $this->_parseNode($child, $attributes, $variables);
        }
        return $result;
    }
}

