<?php

/* error_markup.php
 *  contains types that interpret and process the Athene Error Markup
 * Language that represents verbose error cases handled by the system
 */

/* array of text files that contain Athene error case markup definitions
 * used for the programming 1 course; these are only loaded if the Athene
 * environment is not correct; the environment is used for testing and is
 * not present when running on the server */
$p1Catalog = array('cases.txt');
$p1Catalog_hidden = array('cases_hidden.txt');

/* class AtheneTag
 *  represents the basic structure for error markup
 * tags as well as their behavior (read, print, ETC.)
 */
class AtheneTag {
    // error logging
    private static $parseLog = ''; // log errors across multiple instances of tag objects
    static public function get_parse_error_log() {
        return self::$parseLog;
    }
    static public function reset_parse_error_log() {
        self::$parseLog = '';
    }

    // data
    private $name; // the kind of tag <tag-name> ... </tag-name>
    private $attribute; // <t attribute> ... </t>
    private $content; // what goes between <t> and </t>
    private $subTags; // array of tags; any nested tags within this tag's content
    private $position; // the index in the parent's content string where this tag is included

    /* get_name - returns the tag name
     * - The tag name is the string that goes in-between angle brackets,
     *  such as: <tag-name>. A tag name may be optionally followed by a self-closing
     *  forward-slash (/), which ends the tag and disallows the use of sub-tags and content.
     */
    function get_name() {
        return $this->name;
    }

    /* get_attribute - returns the tag attribute
     * - The tag attribute is the string that optionally appears next to a tag name in the open tag,
     *  such as: <tag-name Here is the attribute part/>. Optionally, a tag attribute may be followed
     *  by a self-closing forward-slash (/). This is not included in the attribute string and indicates
     *  that the tag is closed, also disallowing the use of sub-tags and content. Sub-tags are not interpreted
     *  from attribute strings.
     */
    function get_attribute() {
        return $this->attribute;
    }

    /* get_content - returns the tag content
     * - The tag content is any string data that appears between an open and close tag of the same name.
     *  Tag content may contain sub-tags, which are not included in the content-string. Sub-tags are referenced
     *  by position.
     */
    function get_content() {
        return $this->content;
    }

    /* get_subtag_count - returns the number of sub-tags found in the content section of the tag
     */
    function get_subtag_count() {
        return $this->subTags===null ? 0 : (is_countable($this->subTags) ? count($this->subTags) : 1);
    }

    /* get_subtag - returns an object of type AtheneTag that represents the sub-tag at the specified index
     * - Use the sub-tag's position-in-parent value to determine where the sub-tag lies in a tag's content
     *  string.
     */
    function get_subtag($index) {
        return $this->subTags[$index];
    }

    /* get_position_in_parent - returns the integer position of the tag's position in its parent tag's content
     *  string.
     * - If the tag has no parent, a value of null will be returned.
     */
    function get_position_in_parent() {
        return $this->position;
    }

    /* read_tag - reads a tag from source string: its attribute string, its content string, plus any sub-tags contained within the content string
     *  $source - tag source string (extra characters before tag start are read off)
     *  $iter (byref) - start read-position in source string; will contain end-position after
     *   the operation has been performed
     *  $line_no (byref) - the start line number for the read operation; used for parse error reporting purposes;
     *   will contain the end line number after the operation has been performed
     */
    function read_tag($source,&$iter = 0,&$line_no = 1) {
        $len = strlen($source);

        $bSuccess = $this->read_tag_recursive($source,$len,$iter,$line_no);
        if ( is_null($this->position) )
            $this->position = 0;

        // make tag-name lowercase by default
        // to ensure case-insensitivity
        $this->name = strtolower($this->name);

        return $bSuccess;
    }

    /* print_tag - returns a tag in its string representation; this operation will
     *  almost always preserve the original formatting of the markup
     */
    function print_tag() {
        $output = '';

        if ( !is_null($this->name) ) {
            $output .= "<" . $this->name;

            if ( !is_null($this->attribute) )
                $output .= " " . $this->attribute;
            if ( !is_null($this->content) ) {
                $output .= ">";

                // add content plus subtags (if any)
                $len = strlen($this->content);
                $cnt = count($this->subTags);
                $j = 0;
                for ($i = 0;$i < $len;$i++) {
                    if ( $j<$cnt && $i==$this->subTags[$j]->position )
                        $output .= $this->subTags[$j++]->print_tag(); // recursive call

                    $output .= $this->content{$i};
                }
                // print any tags that are positioned
                // at the end of the content stream
                while ( $j<$cnt )
                    $output .= $this->subTags[$j]->print_tag();

                // end tag
                $output .= "</" . $this->name . ">";
            }
            else
                $output .= "/>";
        }

        return $output;
    }

    /* clear - resets the entire tag to a null state;
     *  this operation includes clearing the sub-tags
     */
    function clear() {
        // set these to null for parsing accuracy later on
        $name = null;
        $attribute = null;
        $content = null;
        $subTags = null;
        $position = null;
    }

    private function read_tag_recursive($source,$length,&$iter,&$line_no) {
        $this->clear(); // reset content

        // read off extra characters before tag (for first frame)
        while ( $iter<$length && $source{$iter}!='<' ) {
            if ( !self::check_line_number($source,$iter,$line_no) && $source{$iter}=='#' ) { // handle "end-of-line" comments
                while ( true ) {
                    $iter++;
                    if ( $iter>=$length || self::check_line_number($source,$iter,$line_no) )
                        break;
                }
            }
            $iter++;
        }

        // check for end of input
        if ( $iter>=$length )
            return false; // no tag was found

        // get tag name
        $iter++;
        $this->name = '';
        while ( $iter<$length ) {
            if ( !self::check_line_number($source,$iter,$line) && $source{$iter}=='>' )
                break; // found name

            // check for attribute string
            if ( ctype_space( $source{$iter} ) ) {
                // move past leading whitespace
                do {
                    $iter++;
                    self::check_line_number($source,$iter,$line_no);
                } while ( $iter<$length && ctype_space( $source{$iter} ) );

                // get attribute string
                $this->attribute = '';
                $quoteLevel = false;
                while ( $iter<$length ) {
                    if ( $source{$iter}=='"' ) // handle string data
                        $quoteLevel = !$quoteLevel;
                    else if ( !self::check_line_number($source,$iter,$line_no) ) {
                        if ( $source{$iter}=="\\" && $quoteLevel ) { // check escape characters
                            $iter++;
                            if ( $iter<$length ) {
                                self::check_line_number($source,$iter,$line_no);
                                if ( self::is_handled_escape_character( $source{$iter} ) )
                                    $this->attribute .= "\\" . $source{$iter}; // keep escape character in string
                                else // flag the unhandled escape character and strip it from the string
                                    self::$parseLog .= "Warning: line $line_no: unsupported escape character found in input stream\n";
                                $iter++;
                                // $iter refers to the next input position
                                continue;
                            }
                            else
                                break;
                        }
                        else if ( !$quoteLevel && $source{$iter}=='>' ) // check end-of-attribute
                            break;
                        else if ( !$quoteLevel && $source{$iter}=='/' && $source{$iter+1}=='>' ) { // check tag-shorthand
                            if ( strlen($this->name)==0 )
                                self::$parseLog .= "Error: line $line_no: reached end-of-tag (with shorthand) and found no tag-name\n";
                            $iter += 2;
                            return true; // tag shorthand <tag-name attr/>
                        }
                    }
                    // default: add character to attribute string
                    $this->attribute .= $source{$iter++};
                }
            }
            else if ( $iter+1<$length && $source{$iter}=='/' && $source{$iter+1}=='>' ) {
                $iter += 2;
                return true; // <tag/> - no content, no attribute
            }
            else
                $this->name .= $source{$iter++}; // append to name
        }

        if ( strlen($this->name)==0 ) {
            self::$parseLog .= "Error: line $line_no: reached end-of-open-tag and found no tag-name\n";
            return false;
        }

        // get content
        $iter++;
        $this->content = '';
        $this->subTags = array();
        $quoteLevel = false;
        $completed = false; // indicates whether or not the tag was read completely
        while ( $iter<$length ) {
            if ( !self::check_line_number($source,$iter,$line_no) ) {
                if ( $source{$iter}=='"' )
                    $quoteLevel = !$quoteLevel;
                else if ( !$quoteLevel && $source{$iter}=='<' ) {
                    if ( $iter+1<$length ) {
                        if ( $source{$iter+1}=='/' ) {
                            // look for terminating tag
                            $endTagName = '';
                            $iter += 2;
                            while ( $iter<$length ) {
                                if ( !self::check_line_number($source,$iter,$line_no) && $source{$iter}=='>' )
                                    break;
                                $endTagName .= $source{$iter++};
                            }

                            if ( $endTagName!=$this->name ) {
                                self::$parseLog .= "Error: line $line_no: expected '".$this->name."' for end-tag; found '$endTagName'\n";
                                return false;
                            }

                            // (this should be the way the loops ends)
                            $iter++;
                            $completed = true;
                            break;
                        }

                        // read nested tag recursively
                        $subTag = new AtheneTag;
                        $subSource = substr($source,$iter);
                        $subIter = 0;

                        if ( !$subTag->read_tag($subSource,$subIter,$line_no) ) // recursive call
                            return false; // sub-tag wasn't formatted correctly

                        $iter += $subIter;
                        $subTag->position = strlen($this->content);
                        $this->subTags[] = $subTag;
                    }
                    else
                        $iter++;
                    // $iter refers to the next input position
                    continue;
                }
                else if ( $quoteLevel && $source{$iter}=="\\" ) { // check escape characters
                    $iter++;
                    if ( $iter<$length ) {
                        self::check_line_number($source,$iter,$line_no);
                        if ( self::is_handled_escape_character( $source{$iter} ) )
                            $this->content .= "\\" . $source{$iter}; // keep escape character in string
                        else // flag the unhandled escape character and strip it from the string
                            self::$parseLog .= "Warning: unhandled escape character found in input stream on line " . $line_no . "\n";
                        $iter++;
                    }
                    // $iter refers to the next input position
                    continue;
                }
                else if ( !$quoteLevel && $source[$iter]=='#' ) { // check for comments in content-stream
                    while ( $iter<$length && $source[$iter]!="\n" )
                        $iter++;
                    // leave endline for content string
                    // $iter refers to the next input position
                    continue;
                }
            }
            // default: add character to content stream
            $this->content .= $source{$iter++};
        }

        if ( !$completed )
            self::$parseLog .= "Error: line $line_no: reached end-of-input and did not find close-tag: expected {$this->name}\n";

        return $completed;
    }

    static private function is_handled_escape_character($character) {
        // only escape these characters
        return $character=='"' || $character=="\\" || $character=='n' || $character=='r' || $character=='t';
    }

    static private function check_line_number($source,$iterator,&$counter) {
        if ( $source[$iterator] == "\n" ) {
            ++$counter;
            return true;
        }
        return false;
    }
}

/* class AtheneMarkupToken
 *  simply provides a mechanism to distinguish markup token strings from
 *  text output strings via a PHP class name
 */
class AtheneMarkupToken {
    public $tok;

    function __construct() {
        $this->tok = '';
    }
}

/* class AtheneMarkupIL
 *  provides functionality for each markup element kind
 */
abstract class AtheneMarkupIL {
    static protected $parseLog = ''; // log errors across multiple instances of markup il objects

    // array of elements found in attribute (header) string
    // contains A) AtheneMarkupToken or B) string output
    protected $headingElements = array();

    // array of elements found in content string
    // contains A) AtheneMarkupIL, B) AtheneMarkupToken, or C) string output
    protected $contentElements = array();

    // keep a record of the tag name so that it can be looked up later
    protected $elementName = '';

    // associative array of special tag name characters and their
    // class name alternatives (this allows dashes and other special characters
    // to be used as tag-names)
    static private $specialClassNameChars = array( '-' => '_' );

    // associative array of special HTML character translations
    // these are translated from string data only
    static protected $HTML_SPECIAL_CHARS = array(
        '<' => '&lt;',
        '>' => '&gt;',
        "\n" => '<br />' );

    /* Constructor for class AtheneMarkupIl - creates a new AtheneMarkupIL base object that parses
     *  tag input (attribute and content) into elements that can be understood by derived implementation
     * AtheneTag $tagObject - The tag object whose data is to be parsed
     * bool $doConversion - if true, special characters are converted into their HTML equivilents
     * - This operation will take content and attribute strings and tokenize them by whitespace; double-quoted
     *  strings are considered to be a complete token
     */
    function __construct(AtheneTag $tagObject,$doConversion = true) {
        $this->elementName = $tagObject->get_name();
        $attr = $tagObject->get_attribute();
        $attrLen = strlen($attr);
        $cont = $tagObject->get_content();
        $contLen = strlen($cont);
        $subCnt = $tagObject->get_subtag_count();

        // tokenize attribute string into its elements
        $iter = 0;
        while ( $iter<$attrLen ) {
            self::feed_whitespace($attr,$attrLen,$iter);
            $this->headingElements[] = self::get_element($attr,$attrLen,$iter,$doConversion);
        }

        // tokenize content string into its elements
        //  sub-tags could be embedded in the content string, and therefore
        //  act as delimiters between elements
        $iter = 0;
        $subIter = 0;
        while ( $iter<$contLen || ($subCnt>0 && $subIter<$subCnt) ) {
            self::feed_whitespace($cont,$contLen,$iter);

            $subTag = $subIter<$subCnt ? $tagObject->get_subtag($subIter) : null;
            if ( $subTag==null )
                $element = self::get_element($cont,$contLen,$iter,$doConversion);
            else if ( $iter<$subTag->get_position_in_parent() ) // expect content up to sub-tag position
                $element = self::get_element($cont,$subTag->get_position_in_parent(),$iter,$doConversion);
            else {
                $subIter++;
                // the content stream contains an embedded sub-tag
                // create an IL object that handles its translation
                // assume the class name is 'AtheneMarkupIL_tagname' (tagname is modified slightly for special characters)
                $tname = $subTag->get_name();
                // change any special characters in tag-name to the
                // appropriate class name value (see the shared associative array $specialClassNameChars member for list)
                foreach (self::$specialClassNameChars as $original => $changed)
                    $tname = str_replace($original,$changed,$tname);
                $className = "AtheneMarkupIL_".$tname;
                if ( class_exists($className) ) {
                    // this is a recursive call of sorts
                    $element = new $className($subTag); // reflectively create object
                }
                else {
                    self::$parseLog .= "Error: markup includes unsupported element called '" . $subTag->get_name() ."'\n";
                    continue;
                }
            }

            // add element to array
            if ( $element!=null )
                $this->contentElements[] = $element;
        }
    }

    /* get_result - this function should return the result of evaluating
     *  the IL sub-structure that the sub-class represents; there are two kinds
     *  of IL sub-structures: output and conditional. An output IL sub-structure should
     *  return valid HTML code that represents the evaulation of the tag and its content.
     *  A conditional IL sub-structure should return a boolean indicating the evaluation
     *  of its conditional operation.
     * array $variableTokens - maps %n token to its value
     *
     * TODO: Add parameters for C++ source code access
     */
    abstract function get_result(array $variableTokens);

    // these helpers are used widely by the derived classes
    // that handle the IL sub-structures
    static protected function is_element_token($thing) {
        return is_a($thing,'AtheneMarkupToken');
    }
    static protected function is_element_string($thing) {
        return is_string($thing);
    }
    static protected function is_element_ilobj($thing) {
        // PHP 5.2 will throw a warning if $thing is not an object
        return is_object($thing) && is_subclass_of($thing,'AtheneMarkupIL');
    }
    // checks to see if a method exists (in this class) for the specified expression parts; if one exists,
    // a string that represents that method is returned, else null
    // the constantPart does NOT include the separating underscore
    static protected function check_variable_function($constantPart,$variablePart) {
        $functionName = "{$constantPart}_$variablePart";
        if ( method_exists(get_class(),$functionName) )
            return $functionName;
        return null;
    }
    // comparison functions of the form: string_comparison_PREDTOK,
    // where PREDTOK is the predicate token found in the markup
    static protected function string_comparison_mcase($left,$right) {
        return $left===$right;
    }
    static protected function string_comparison_icase($left,$right) {
        return strtolower($left)===strtolower($right);
    }
    static protected function string_comparison_substr($haystack,$needle) {
        return strpos($haystack,$needle) !== false;
    }

    // check status of AtheneMarkupToken object; see if it is a variable token
    // of the syntax used by the GCC error element parser
    static protected function is_variable_token(AtheneMarkupToken $token) {
        $len = strlen($token->tok);
        if ( $len>=2 && $token->tok[0]=='%' ) {
            for ($i = 1;$i < $len;$i++)
                if ( !ctype_digit($token->tok[$i]) )
                    return false;
            return true;
        }
        return false;
    }

    // see if markup token is a global tag variable
    static protected function is_global_variable_token(AtheneMarkupToken $token) {
        $len = strlen($token->tok);
        if ($len>=2 && $token->tok[0]=='%') {
            for ($i = 1;$i < $len;$i++)
                if (ctype_alpha($token->tok[$i]))
                    return true;
        }
        return false;
    }

    // global tag variable functionality
    static private $globalVars = array();
    static protected function lookup_global_variable($name) {
        if ( !array_key_exists($name,self::$globalVars) )
            return '';
        return self::$globalVars[$name];
    }
    static protected function set_global_variable($name,$value) {
        self::$globalVars[$name] = $value;
    }

    static private function feed_whitespace($source,$sourceLength,&$iter) {
        while ( $iter<$sourceLength && ctype_space($source{$iter}) )
            $iter++;
    }
    static private function get_element($source,$sourceLength,&$iter,$doConversion = true) {
        // Obtain next element separated by whitespace/dbl-quotes; dbl-quoted string denotes
        // single element. The element in question may be either a string or an
        // AtheneMarkupToken. If the element is a string, it was contained within
        // double quotes; if it is an AtheneMarkupToken, then it was found outside of dbl-quotes
        // and any whitespace.
        $element = new AtheneMarkupToken; // start with token, unless dbl-quotes are found

        $quoteLevel = false;
        while ( $iter<$sourceLength ) {
            if ( $source{$iter}=='"' ) {
                if ( $quoteLevel ) {
                    $iter++; // move past terminating dbl-quote
                    break; // done; hit end-quote
                }
                else if ( $element->tok!='' ) // found token separated by dbl-quotes
                    break; // don't remove dbl-quote, since it refers to the start of the next element
                $quoteLevel = true;
                $element = ''; // change type to string
                $iter++;
                continue;
            }
            else if ( $quoteLevel && $source{$iter}=="\\" && ($source{$iter+1}=='"' || $source{$iter+1}=="\\") )
                $iter++; // resolve quotes and backslashes to non-escaped version; leave other escaped characters for later processing
            else if ( !$quoteLevel && ctype_space($source{$iter}) )
                break; // done; hit whitespace (note: dbl-quotes delimit too)

            if ( is_object($element) ) // is-a AtheneMarkupToken (token)
                $element->tok .= $source{$iter++};
            else // just a string
                $element .= $source{$iter++};
        }

        // for easy comparisons, normalize
        // any token element by making it lower-case
        if ( is_object($element) )
            $element->tok = strtolower($element->tok);
        else if ( $doConversion && is_string($element) )
        {
            // translate special characters into their
            // appropriate HTML equivalents
            foreach (self::$HTML_SPECIAL_CHARS as $org => $trans)
                $element = str_replace($org,$trans,$element);
        }

        if ( (is_string($element) && $element=='') || (is_object($element) && $element->tok=='') )
            return null;
        return $element;
    }
}

// begin IL sub-structure derivations
//  These structures and their capacities are documented in athene/research/error-markup-docs.txt

/* class AtheneMarkupIL_output
 *  implements <output> tags
 */
class AtheneMarkupIL_output extends AtheneMarkupIL {
    function __construct(AtheneTag $tagObject) {
        parent::__construct($tagObject);
    }

    // kind=output
    final function get_result(array $variableTokens) {
        $output = '';
        $doesBullet = false; // show each text element in separate bullet
        $doesNumber = false; // show each text element in separate number
        $doesBlock = false; // show concatenation of text elements as paragraph

        // check attributes
        foreach ( $this->headingElements as $elem ) {
            if ( self::is_element_token($elem) ) {
                if ( $elem->tok=="bullet" )
                    $doesBullet = true;
                else if ( $elem->tok=="number" )
                    $doesNumber = true;
                else if ( $elem->tok == "block" )
                    $doesBlock = true;
            }
        }

        // handle bullets and numbers opening, block paragraphs
        if ( $doesBullet )
            $output .= "<ul>";
        else if ( $doesNumber )
            $output .= "<ol>";
        else if ( $doesBlock )
            $output .= "<p>"; // inserting a nested p-tag seems to work...

        // process content elements
        foreach ( $this->contentElements as $elem ) {
            if ( self::is_element_ilobj($elem) )
                $res = $elem->get_result($variableTokens);
            else if ( self::is_element_string($elem) )
                $res = $elem;
            else if ( self::is_element_token($elem) && self::is_variable_token($elem) && array_key_exists($elem->tok,$variableTokens) )
                $res = $variableTokens[$elem->tok];
            else if (self::is_element_token($elem) && self::is_global_variable_token($elem))
                $res = self::lookup_global_variable($elem->tok);
            else
                continue;

            if ( $doesBullet || $doesNumber )
                $output .= "<li>$res</li>";
            else
                $output .= $res;
        }

        // handle bullets and numbers closing
        if ( $doesBullet )
            $output .= "</ul>";
        else if ( $doesNumber )
            $output .= "</ol>";
        else if ( $doesBlock )
            $output .= "</p>";

        return $output;
    }
}
// 'output' aliases
class AtheneMarkupIL_else extends AtheneMarkupIL_output {}

/* class AtheneMarkupIL_cond_output
 *  implements <cond-output> tags
 */
class AtheneMarkupIL_cond_output extends AtheneMarkupIL {
    function __construct(AtheneTag $tagObject) {
        parent::__construct($tagObject);
    }

    // kind=output
    final function get_result(array $variableTokens) {
        $output = '';
        $anyCond = true; // default "any-condition" conditional output tag
        $doesBullet = false;
        $doesNumber = false;
        $negate = false;
        $elseOperation = null;

        // process attribute tokens
        foreach ( $this->headingElements as $elem ) {
            if ( self::is_element_token($elem) ) {
                if ( $elem->tok=="any" )
                    $anyCond = true;
                else if ( $elem->tok=="all" )
                    $anyCond = false;
                else if ( $elem->tok=="bullet" )
                    $doesBullet = true;
                else if ( $elem->tok=="number" )
                    $doesNumber = true;
                else if ( $elem->tok=="negate" )
                    $negate = true;
            }
        }

        // handle open list tags
        if ( $doesBullet )
            $output .= "<ul>";
        else if ( $doesNumber )
            $output .= "<ol>";

        // process content elements
        $condition = !$anyCond;
        foreach ($this->contentElements as $elem) {
            if ( self::is_element_ilobj($elem) ) {
                if ( $elem->elementName == 'else' ) {
                    // keep the else operation until the end
                    $elseOperation = $elem;
                    continue;
                }
                else {
                    $res = $elem->get_result($variableTokens);
                    if ( is_bool($res) ) {
                        // result was condition tag output
                        $condition = $anyCond ? ($condition || $res) : ($condition && $res);
                        continue;
                    }
                }
            }
            else if ( self::is_element_string($elem) )
                $res = $elem;
            else if ( self::is_element_token($elem) && self::is_variable_token($elem) && array_key_exists($elem->tok,$variableTokens) )
                $res = $variableTokens[$elem->tok];
            else if (self::is_element_token($elem) && self::is_global_variable_token($elem))
                $res = self::lookup_global_variable($elem->tok);

            if ( $res != "" ) {
                if ( $doesBullet || $doesNumber )
                    $output .= "<li>$res</li>";
                else
                    $output .= $res;
                $res = "";
            }
        }

        // handle close list tags
        if ( $doesBullet )
            $output .= "</ul>";
        else if ( $doesNumber )
            $output .= "</ol>";

        // return output only if condition was true
        // OR if the condition was negated
        if ( ($condition && !$negate) || (!$condition && $negate) )
            return $output;
        // perform the else operation if one was specified or else return no output
        return $elseOperation==null ? '' : $elseOperation->get_result($variableTokens);
    }
}

/* class AtheneMarkupIL_vtok
 *  implements <vtok> tags
 */
class AtheneMarkupIL_vtok extends AtheneMarkupIL {
    function __construct(AtheneTag $tagObject) {
        parent::__construct($tagObject,false);
    }

    // kind=condition
    final function get_result(array $variableTokens) {
        $compFunct = null;
        $vtok = '';
        $sourceString = '';

        // search attribute elements for needed information
        foreach ( $this->headingElements as $elem ) {
            if ( self::is_element_token($elem) ) {
                $compFunct = self::check_variable_function("string_comparison",$elem->tok);
                if ( is_null($compFunct) ) {
                    if ( self::is_variable_token($elem) )
                        $vtok = $elem->tok;
                    else
                        self::$parseLog .= "Warning: unsupported token '".$elem->tok."' specified in attribute string\n";
                }
            }
            else if ( self::is_element_string($elem) )
                $sourceString = $elem;
        }

        // search content elements for needed information
        foreach ( $this->contentElements as $elem ) {
            if ( self::is_element_token($elem) ) {
                if ( is_null($compFunct) ) {
                    $compFunct = self::check_variable_function("string_comparison",$elem->tok);
                    if ( is_null($compFunct) ) {
                        if ( self::is_variable_token($elem) )
                            $vtok = $elem->tok;
                        else
                            self::$parseLog .= "Warning: unsupported token '".$elem->tok."' specified in attribute string\n";
                    }
                }
                else if ( $vtok=='' && strlen($elem->tok)>=2 && $elem->tok[0]=='%' )
                    $vtok = $elem->tok;
                else
                    self::$parseLog .= "Warning: tokens respecified in content string that were specified in attribute string\n";
            }
            else if ( self::is_element_string($elem) && $sourceString!='' )
                $sourceString = $elem;
        }

        if ( is_null($compFunct) )
            $compFunct = "string_comparison_mcase"; // this one exists

        if ( array_key_exists($vtok,$variableTokens) )
            return self::$compFunct($variableTokens[$vtok],$sourceString); // argument order is necessary in case of needle-haystack operation
        return false;
    }
}

/* class AtheneMarkupIL_tolower
 *  implements <tolower> tags
 */
class AtheneMarkupIL_tolower extends AtheneMarkupIL {
    function __construct(AtheneTag $tagObject) {
        parent::__construct($tagObject);
    }

    // kind=output
    final function get_result(array $variableTokens) {
        $output = '';

        // check attribute string for string elements
        foreach ($this->headingElements as $elem) {
            if ( self::is_element_string($elem) )
                $output .= $elem;
            else if ( self::is_element_token($elem) && self::is_variable_token($elem) && array_key_exists($elem->tok,$variableTokens) )
                $output .= $variableTokens[$elem->tok];
            else if (self::is_element_token($elem) && self::is_global_variable_token($elem))
                $output .= self::lookup_global_variable($elem->tok);
        }

        // check content string for string elements
        foreach ($this->contentElements as $elem) {
            if ( self::is_element_ilobj($elem) ) {
                $res = $elem->get_result($variableTokens);
                if ( is_string($res) )
                    $output .= $res;
            }
            else if ( self::is_element_string($elem) )
                $output .= $elem;
            else if ( self::is_element_token($elem) && self::is_variable_token($elem) && array_key_exists($elem->tok,$variableTokens) )
                $output .= $variableTokens[$elem->tok];
            else if (self::is_element_token($elem) && self::is_global_variable_token($elem))
                $output .= self::lookup_global_variable($elem->tok);
        }

        return strtolower($output);
    }
}

/* class AtheneMarkupIL_toupper
 *  implements <toupper> tags
 */
class AtheneMarkupIL_toupper extends AtheneMarkupIL {
    function __construct(AtheneTag $tagObject) {
        parent::__construct($tagObject);
    }

    // kind=output
    final function get_result(array $variableTokens) {
        $output = '';

        // check attribute string for string elements
        foreach ($this->headingElements as $elem) {
            if ( self::is_element_string($elem) )
                $output .= $elem;
            else if ( self::is_element_token($elem) && self::is_variable_token($elem) && array_key_exists($elem->tok,$variableTokens) )
                $output .= $variableTokens[$elem->tok];
            else if (self::is_element_token($elem) && self::is_global_variable_token($elem))
                $output .= self::lookup_global_variable($elem->tok);
        }

        // check content string for string elements
        foreach ($this->contentElements as $elem) {
            if ( self::is_element_ilobj($elem) ) {
                $res = $elem->get_result($variableTokens);
                if ( is_string($res) )
                    $output .= $res;
            }
            else if ( self::is_element_string($elem) )
                $output .= $elem;
            else if ( self::is_element_token($elem) && self::is_variable_token($elem) && array_key_exists($elem->tok,$variableTokens) )
                $output .= $variableTokens[$elem->tok];
            else if (self::is_element_token($elem) && self::is_global_variable_token($elem))
                $output .= self::lookup_global_variable($elem->tok);
        }

        return strtoupper($output);
    }
}

/* class AtheneMarkupIL_bullet
 *  implements <bullet> tags
 */
class AtheneMarkupIL_bullet extends AtheneMarkupIL {
    function __construct(AtheneTag $tagObject) {
        parent::__construct($tagObject);
    }

    // kind=output
    final function get_result(array $variableTokens) {
        $output = '';

        foreach ($this->headingElements as $elem) {
            if ( self::is_element_string($elem) )
                $output .= "<li>$elem</li>";
        }

        foreach ($this->contentElements as $elem) {
            if ( self::is_element_string($elem) )
                $output .= "<li>$elem</li>";
            else if ( self::is_element_token($elem) && self::is_variable_token($elem) && array_key_exists($elem->tok,$variableTokens) )
                $output .= "<li>" . $variableTokens[$elem->tok] . "</li>";
            else if (self::is_element_token($elem) && self::is_global_variable_token($elem))
                $output .= "<li>" . self::lookup_global_variable($elem->tok) . "</li>";
            else if ( self::is_element_ilobj($elem) ) {
                $res = $elem->get_result($variableTokens);
                if ( is_string($res) && $res!="" )
                    $output .= "<li>$res</li>";
            }
        }

        return "<ul>$output</ul>";
    }
}

/* class AtheneMarkupIL_code
 *  implements the <code> tag
 */
class AtheneMarkupIL_code extends AtheneMarkupIL {
    static private $KEYWORDS = array(
        "alignas", "alignof", "asm", "auto", "bool", "break", "case", "catch", "char", "char16_t", "char32_t", "class",
        "const", "constexpr", "const_cast", "continue", "decltype", "default", "delete", "do", "double", "dynamic_cast",
        "else", "enum", "explicit", "export", "extern", "false", "float", "for", "friend", "goto", "if", "inline", "int",
        "long", "mutable", "namespace", "new", "noexcept", "nullptr", "operator", "private", "protected", "public", "register",
        "reinterpret_cast", "return", "short", "signed", "sizeof", "static", "static_assert", "static_cast", "struct", "switch",
        "template", "this", "thread_local", "throw", "true", "try", "typedef", "typeid", "typename", "union", "unsigned", "using",
        "virtual", "void", "volatile", "wchar_t", "while" );
    static private $DIRECTIVES = array(
        "#if", "#ifdef", "#ifndef", "#else", "#elif", "#endif", "#include" );
    static private $COLORS = array(
        "keyword" => "0000FF",
        "directive" => "FF00FF",
        "string" => "FF0000",
        "comment" => "009933",
        "lineno" => "C3C3C3" );
    static private $SEPARATORS = "`~!@#\$%^&*()-=+[{]}\\|;:\"',<.>/? \r\n\t";

    function __construct(AtheneTag $tagObject) {
        // inform the parent class to not perform
        // conversions - the original characters are
        // needed in this context and will be translated later
        parent::__construct($tagObject,false);
    }

    // kind=output
    final function get_result(array $variableTokens) {
        $code = '';
        $bold = false; // applies to keywords
        $itallic = false; // applies to entire text
        $block = false; // place code in a separate division
        $lineNo = null; // if not null, then stores line numbers and displays them in the result

        foreach ($this->headingElements as $elem) {
            if ( self::is_element_token($elem) ) {
                if ( $elem->tok == "bold" )
                    $bold = true;
                else if ( $elem->tok == "itallic" )
                    $itallic = true;
                else if ( $elem->tok == "lineno" )
                    $lineNo = 0;
                else if ( $elem->tok == "block" )
                    $block = true;
            }
        }

        foreach ($this->contentElements as $elem) {
            if ( self::is_element_token($elem) )
            {
                if ( $elem->tok == "p1" )
                    $code .= "#include <iostream>\nusing namespace std;\n\nint main()\n{\n";
                else if ( self::is_variable_token($elem) && array_key_exists($elem->tok,$variableTokens) )
                    $code .= $variableTokens[$elem->tok];
                else if (self::is_element_token($elem) && self::is_global_variable_token($elem))
                    $code .= self::lookup_global_variable($elem->tok);
            }
            else if ( self::is_element_string($elem) )
                $code .= $elem;
            else if ( self::is_element_ilobj($elem) )
            {
                $res = $elem->get_result($variableTokens);
                if ( is_string($res) )
                    $code .= $res;
            }
        }

        // syntax highlight the code
        $html = isset($lineNo) ? self::color_line_no(++$lineNo) : '';
        $i = 0;
        $sz = strlen($code);
        while ( $i < $sz ) {
            $html .= self::color_element( self::get_part($code,$sz,$i,$type,$lineNo),$type,$bold );
        }

        if ( $itallic )
            $html = "<i>$html</i>";
        if ( $block )
            $html = "<p>$html</p>";

        return "<code>$html</code>";
    }

    static private function color_element($element,$type,$isBold) {
        if ( isset(self::$COLORS[$type]) )
            $hex = self::$COLORS[$type];
        else
            return $element;
        if ( $isBold )
            $element = "<b>$element</b>";
        return "<span style=\"color: #$hex\">$element</span>";
    }

    static private function color_line_no($lineNo) {
        return self::color_element(str_pad($lineNo,3,' ',STR_PAD_LEFT).": ","lineno",false);
    }

    static private function get_part($source,$sourceLength,&$iterator,&$type,&$lineNo) {
        $part = '';
        $type = null;

        // handle keywords - can't be comments or directives because those
        // act as separators
        if ( strpos(self::$SEPARATORS,$source[$iterator]) === false ) {
            do {
                $part .= self::process_character( $source[$iterator++],$lineNo );
            } while ( $iterator<$sourceLength && strpos(self::$SEPARATORS,$source[$iterator])===false );
            if ( in_array($part,self::$KEYWORDS) )
                $type = "keyword"; // was a keyword
        }
        // handle single-line comments
        else if ( $iterator+1<$sourceLength && $source[$iterator]=='/' && $source[$iterator+1]=='/' ) {
            $type = "comment";
            do {
                $part .= self::process_character( $source[$iterator++],$lineNo );
            } while ( $iterator<$sourceLength && $source[$iterator]!="\n" );
        }
        // handle multi-line comments
        else if ( $iterator+1<$sourceLength && $source[$iterator]=='/' && $source[$iterator+1]=='*' ) {
            $type = "comment";
            $part = "/*";
            $iterator += 2;
            while ( $iterator<$sourceLength && ($source[$iterator]!='*' || $iterator+1>=$sourceLength || $source[$iterator+1]!='/') )
                $part .= self::process_character( $source[$iterator++],$lineNo );
            if ( $iterator+1<$sourceLength && $source[$iterator]=='*' && $source[$iterator+1]=='/' ) {
                $part .= "*/";
                $iterator += 2;
            }
        }
        // handle string literals
        else if ( $source[$iterator] == '"' ) {
            $type = "string";
            $part = $source[$iterator++];
            do {
                $part .= self::process_character( $source[$iterator],$lineNo );
                if ( $source[$iterator] == '"' )
                    break;
                // handle escaped characters
                if ( $iterator+1<$sourceLength && $source[$iterator]=='\\' )
                    $part .= self::process_character( $source[++$iterator],$lineNo );
                ++$iterator;
            } while ( $iterator<$sourceLength );
            $iterator++;
        }
        else
            $part .= self::process_character( $source[$iterator++],$lineNo );

        return $part;
    }

    static private function process_character($char,&$lineNo) {
        // some special characters aren't translated by
        // our base class; these will need to be translated
        // for accurate rendering in HTML
        if ( $char == "\t" ) // convert tabs into 4 non-breaking spaces
            return str_repeat("&nbsp;",4);
        if ( $char == " " ) // don't let the web browser take liberty with whitespace; enforce 1 space per space ratio
            return "&nbsp;";
        if ( isset(self::$HTML_SPECIAL_CHARS[$char]) ) { // convert special HTML characters
            $stuff = self::$HTML_SPECIAL_CHARS[$char];
            if ( $char=="\n" && isset($lineNo) )
                $stuff .= self::color_line_no(++$lineNo);
            return $stuff;
        }
        return $char;
    }
}

class AtheneMarkupIL_simpltype extends AtheneMarkupIL {

    // kind=output
    final function get_result(array $variableTokens) {
        $typename = null;
        $doesBold = false;
        $doesUnderline = false;
        $doesItallic = false;
        $isResolved = false;

        foreach ($this->headingElements as $element) {
            if ( $typename==null && self::is_element_string($element) && $element!='' )
                $typename = $element;
            else if ( self::is_element_token($element) ) {
                if ($element->tok == 'bold')
                    $doesBold = true;
                else if ($element->tok == 'underline')
                    $doesUnderline = true;
                else if ($element->tok == 'itallic')
                    $doesItallic = true ;
                else if ( $typename==null && self::is_variable_token($element) && array_key_exists($element->tok,$variableTokens) )
                    $typename = $variableTokens[$element->tok];
                else if (self::is_element_token($elem) && self::is_global_variable_token($elem))
                    $typename = self::lookup_global_variable($elem->tok);
            }
        }
        // (ignore content elements)

        // check for complex type names (such as those of standard library types whose typedefs have been resolved)
        $original = $typename;
        $len = strlen($original);
        if ( strpos($original,'std::string') !== false || strpos($original,'std::basic_string') !== false )
            $typename = "string";
        else if ( strpos($original,'std::basic_ostream') !== false )
            $typename = "ostream";
        else if ( strpos($original,'std::basic_istream') !== false )
            $typename = "istream";
        else if ( strpos($original,'char') !== false ) {
            // check for string literals or non-const c-strings
            $dim = 0;
            // count dimensions of char array
            for ($i = 0;$i<$len;$i++)
                if ($original[$i] == '*')
                    ++$dim;
            // it's most likely a string if the char array has 1 dimension
            // or if it's a string literal of a const known size, indicated by 'const char [n]'
            if ( $dim==1 || strpos($original,'[') !== false )
            {
                $isResolved = true; // the typename is nothing else as far as I'm concerned
                $typename = "string";
            }
        }
        else {
            // get just the typename without any symbols
            // typenames in C++ are alpha-numeric allowing underscores (number can't be first but I don't care about that here...)
            $iter = 0;
            $typename = '';
            // advance past anything in front
            while ( $iter<$len && !ctype_alpha($original[$iter]) && $original[$iter]!='_' )
                ++$iter;
            // get the typename
            while ( $iter<$len && (ctype_alpha($original[$iter]) || ctype_digit($original[$iter]) || $original[$iter]=='_') )
                $typename .= $original[$iter++];
        }

        if ( !$isResolved ) // the type could potentially be an array of itself and so on
        {
            // check for arrays; it is assumed that every pointer
            // type is an array; therefore, each * character corresponds
            // to 'array of ...'; each * is also assumed to be trailing
            // at the end of each typename
            $iter = $len - 1;
            while ($iter>=0 && !ctype_alpha($original[$iter]) && !ctype_digit($original[$iter]) && $original[$iter]!='_') {
                if ($original[$iter] == '*')
                    $typename = "array of $typename";
                --$iter;
            }
        }

        return $typename;
    }
}

class AtheneMarkupIL_cond extends AtheneMarkupIL {
    // kind=conditional

    final function get_result(array $variableTokens) {
        $or = true; // default "any-condition"
        $negate = false; // default "do not negate"

        // look through attribute field
        foreach ($this->headingElements as $elem) {
            if ( self::is_element_token($elem) ) {
                if ($elem->tok == "all")
                    $or = false;
                else if ($elem->tok == "any")
                    $or = true;
                else if ($elem->tok == "negate")
                    $negate = true;
            }
        }

        // look for nested conditional tags in content field
        $result = !$or; // default condition
        foreach ($this->contentElements as $elem) {
            if ( self::is_element_ilobj($elem) ) {
                $bresult = $elem->get_result($variableTokens);
                if ( is_bool($bresult) )
                    $result = $or ? ($result || $bresult) : ($result && $result);
                if (($result && $or) || (!$result && !$or))
                    break; // short circuit
            }
        }

        // return the result; observe the negate condition
        return ($result && !$negate) || (!$result && $negate);
    }
}

class AtheneMarkupIL_decl extends AtheneMarkupIL {
    final function get_result(array $variableTokens) {
        $onlyFunctions = false;
        $onlyVariables = false;
        $distance = 0;
        $decl = '';

        foreach ($this->headingElements as $elem) {
            if ( self::is_element_token($elem) ) {
                if ($elem->tok == "any")
                    $onlyFunctions = $onlyVariables = false;
                else if ($elem->tok == "function")
                    $onlyFunctions = true;
                else if ($elem->tok == "var")
                    $onlyVariables = true;
                else if (strpos($elem->tok,"distance") !== false) {
                    $parts = explode(':',$elem->tok,2);
                    if ( array_key_exists(1,$parts) )
                        $distance = intval($parts[1]);
                }
            }
        }

        foreach ($this->contentElements as $elem) {
            if ( self::is_element_token($elem) ) {
                if ( self::is_variable_token($elem) && array_key_exists($elem->tok,$variableTokens) )
                    $decl .= $variableTokens[$elem->tok];
                else if ( self::is_global_variable_token($elem) )
                    $decl .= self::lookup_global_var($elem->tok);
                else
                    $decl .= $elem->tok;
            }
            else if ( is_string($elem) )
                $decl .= $elem;
        }

        // load global sources list; this is a listing of parse tree objects
        // for the submission set; we only consider the first submission
        global $sources;
        if (!isset($sources) || !is_array($sources) || !array_key_exists(0,$sources))
            return false;
        $parseTree = $sources[0]->get_parse_tree_root();

        /* define search items in parse tree; note: for functions we don't
           care about prototypes; hopefully the user has defined the function;
           this is why we search for CPPFunctionDefinition */
        $searches = array();
        if ($onlyFunctions)
            $searches[] = "CPPFunctionDefinition";
        else if ($onlyVariables)
            $searches[] = "CPPSimpleDeclaration";
        else {
            $searches[] = "CPPFunctionDefinition";
            $searches[] = "CPPSimpleDeclaration";
        }

        foreach ($searches as $kind) {
            $handle = $parseTree->find_first($kind);
            while ( !is_null($handle) ) {
                if ($kind == "CPPSimpleDeclaration")
                    // this excludes any function prototype names
                    $names = $handle->get_variable_names();
                else
                    $names = array($handle->get_name());
                foreach ($names as $name) {
                    similar_text($name,$decl,$f);
                    // if the names are within 50% similar, then compute the edit distance
                    if ($f >= 50.0 && levenshtein($name,$decl) <= $distance) {
                        self::set_global_variable("%decl",$name);
                        self::set_global_variable("%decltype",$kind=="CPPFunctionDefinition"?"function":"variable");
                        self::set_global_variable("%lineno",(string)$handle->get_line_no());
                        return true;
                    }
                }
                $handle = $handle->find_next($kind);
            }
        }

        return false;
    }
}

// end IL sub-structure derivations

/* class AtheneErrorCase
 *  implements the <err> tag functionality; this
 *  class should be used to manage all error cases;
 *  it is not recognized by the underlying IL system
 *  (it's name is not AtheneMarkupIL_err); it presents
 *  a top-level interface for any managing context to handle
 *  <err> tags independently of the AtheneMarkupIL system; in other
 *  words, an <err> tag is a "special-case" tag that's handled by
 *  an AtheneErrorManager and handles any other IL sub-structures
 */
class AtheneErrorCase extends AtheneMarkupIL {
    private $errLog = ''; // store errors that occurred with specific object
    public function get_parse_error_log() {
        return $this->errLog;
    }

    function __construct(AtheneTag $tagObject) {
        self::$parseLog = ''; // reset parse log for construction-time errors
        parent::__construct($tagObject); // invoke base-class c-stor
        $this->errLog .= self::$parseLog; // cache parse log for this object
    }

    /* get_result (AtheneErrorCase) - returns the result of the error case evaluation
     *  as HTML; kind=output
     */
    final function get_result(array $variableTokens) {
        self::$parseLog = ''; // reset parse log for construction-time errors

        // tag format documented in athene/research/error-markup-docs.txt
        $html = '';
        foreach ($this->contentElements as $elem) {
            if ( self::is_element_ilobj($elem) ) {
                $res = $elem->get_result($variableTokens);
                if ( is_string($res) ) // just include string output
                    $html .= $res;
            }
            else if ( self::is_element_string($elem) )
                $html .= $elem;
            else if ( self::is_element_token($elem) && self::is_variable_token($elem) && array_key_exists($elem->tok,$variableTokens) )
                $html .= $variableTokens[$elem->tok];
            else if (self::is_element_token($elem) && self::is_global_variable_token($elem))
                $html .= self::lookup_global_variable($elem->tok);
            else
                self::$parseLog .= "Error: unexpected element found in <err> tag\n";
        }

        // catalog any errors that happened locally for this object
        $this->errLog .= self::$parseLog;

        return $html;
    }
}

/* class ErrorManager
 *  manages the error cases for a session;
 *  this type handles all <err> tags within
 *  one or more markup files
 */
class AtheneErrorManager {
    // error logging
    private $parseLog = '';
    public function get_parse_error_log() {
        return $this->parseLog;
    }

    private $errLookup = array(); // associates error source string to AtheneTag object
    private $errModifierLookup = array(); // associates modifier token to error source string array

    /* Constructor for class AtheneErrorManager - create a new AtheneErrorManager, which loads
     *  error cases from the specified file(s) as well
     *  as from the global list of files; an
     *  AtheneErrorManager object reads the markup from
     *  the specified files and stores the text until
     *  it's ready to be processed as an error case
     */
    function __construct($extraFiles = array()) {
        foreach (array_merge($extraFiles,self::load_catalog()) as $filename) {
            if ($filename == "")
                continue;
            if ( file_exists($filename) ) {
                $iter = 0;
                $line_no = 1;
                $text = @file_get_contents($filename,true);

                while (true) {
                    $newTag = new AtheneTag;
                    if ( $newTag->read_tag($text,$iter,$line_no) ) {
                        if ( $newTag->get_name()!=="err" )
                            $this->parseLog .= "Error: file $filename: expected <err> tag at file scope; found '" . $newTag->get_name() . "'\n";
                        else if ( is_null( $newTag->get_attribute() ) )
                            $this->parseLog .= "Error: file $filename: expected <err> tag with error source string attribute\n";
                        else {
                            $sourceStrings = self::get_source_strings( $newTag->get_attribute(),$modifier );
                            foreach ($sourceStrings as $str) {
                                $this->errLookup[$str] = $newTag;
                                $this->errModifierLookup[$modifier][] = $str;
                            }
                        }
                    }
                    else
                        break;
                }

                if (count($this->errLookup) == 0)
                    $this->parseLog .= "Error: file $filename: found no error cases in markup\n";

                // add any parse errors that happened in a markup tag
                $this->parseLog .= AtheneTag::get_parse_error_log();
                AtheneTag::reset_parse_error_log();
            }
            else
                $this->parseLog .= "Error: could not open specified markup file '$filename'\n";
        }
    }

    /* list_error_source_strings - returns a string that contains all
     *  of the supported error source strings
     *  for which the manager can actively
     *  generate an AtheneErrorCase
     */
    function list_error_source_strings() {
        $supported = '';
        $keys = array_keys($this->errLookup);

        foreach ($keys as $key) {
            $supported .= "$key\n";
        }

        return $supported;
    }

    /* list_error_source_strings_comparison - returns a string that contains all
     *  of the supported error source strings
     *  organized by comparison predicate for
     *  which the manager can actively generate
     *  an AtheneErrorCase
     */
    function list_error_source_strings_comparison() {
        $supported = '';

        foreach ($this->errModifierLookup as $key => $value) {
            $supported .= "($key)\n";
            foreach ($value as $src)
                $supported .= "\t$src\n";
        }

        return $supported;
    }

    /* supports_error - returns true if the specified error source
     *  string is supported by the error manager
     */
    function supports_error($errorSource) {
        return !is_null( $this->lookup($errorSource) );
    }

    /* get_error_case - returns an AtheneErrorCase object for the case
       represented by the specified source string; if no such case
       exists, null is returned
    */
    function get_error_case($errorSource) {
        $src = $this->lookup($errorSource);

        if ( !is_null($src) )
            return new AtheneErrorCase( $this->errLookup[$src] );
        return null;
    }

    /* Returns a source string if the case represented by the
     *  specified source string exists or null on failure
     */
    private function lookup($errorSource) {
        foreach ($this->errModifierLookup as $modifier => $sourceArray) {
            $callMe = "compare_case_$modifier";
            if ( method_exists(get_class(),$callMe) ) {
                $result = self::$callMe($errorSource,$sourceArray);
                if ( !is_null($result) )
                    return $result;
            }
            else {
                // add an error message to the log for debugging purposes
                $this->parseLog .= "Error: attempted to use unrecognized predicate modifier '$modifier' for the following source strings:\n";
                foreach ($sourceArray as $elem)
                    $this->parseLog .= "\t$elem\n";
            }
        }
        return null;
    }

    /* Returns an array of all the source strings
     *  contained within the specified attribute string
     *  and puts a modifier token in $modifiers if one exists.
     *  Note: if more than one token is found, the last token
     *  found is kept as the modifier token for all source strings
     */
    private static function get_source_strings($attribute,&$modifier) {
        $strs = array();
        $length = strlen($attribute);
        $iter = 0;
        $modifier = "mcase"; // the default (implied) modifier is match-case

        while ( true ) {
            $src = '';

            // find begin-quote
            $tok = '';
            while ( $iter < $length ) {
                if ( $attribute{$iter}=='"' )
                    break;

                if ( ctype_space( $attribute{$iter} ) ) { // apply token to modifier argument
                    if ( $tok != '' ) {
                        $modifier = strtolower($tok);
                        $tok = '';
                    }
                }
                else
                    $tok .= $attribute{$iter};

                $iter++;
            }

            // apply token (if it was modified) to modifier argument
            if ( $tok != '' )
                $modifier = strtolower($tok);

            if ( $iter>=$length )
                break; // no begin-quote found

            // get source string (watch out for escaped \"s)
            $iter++;
            while ( $iter<$length && $attribute{$iter}!='"' ) {
                if ( $attribute{$iter}=="\\" )
                    ++$iter;
                $src .= $attribute{$iter};

                $iter++;
            }

            $strs[] = $src;
            $iter++;
        }

        return $strs;
    }

    /* Error case comparison functions
     */
    static private function compare_case_mcase($comparisonString,array $sourceStringArray) {
        foreach ($sourceStringArray as $str)
            if ( $comparisonString===$str )
                return $str;
        return null;
    }
    static private function compare_case_icase($comparisonString,array $sourceStringArray) {
        $comparisonString = strtolower($comparisonString);
        foreach ($sourceStringArray as $str) {
            $compStr = strtolower($str);
            if ( $comparisonString===$compStr )
                return $str;
        }
        return null;
    }
    static private function compare_case_substr($comparisonString,array $sourceStringArray) {
        // the source string is a needle in the haystack formed by the comparison string
        foreach ($sourceStringArray as $str) {
            if ( strpos($comparisonString,$str) !== false )
                return $str;
        }
        return  null;
    }

    static private function load_catalog() {
        global $p1Catalog;
        $ATHDIR = getenv("ATHENE_DIRECTORY");

        if ($ATHDIR !== false) {
            // use catalog markups from catalog tree
            return explode("\n",self::find_markups_recursive($ATHDIR));
        }

        // use catalog from P1 repository
        return $p1Catalog;
    }

    // produce string of newline-delimited file names representing
    // markup case files to load into the markup system
    static private function find_markups_recursive($directoryName) {
        $dirhandle = @opendir($directoryName);
        if ($dirhandle !== false) {
            $result = "";

            while (false !== ($entry = readdir($dirhandle))) {
                if ($entry=="." || $entry=="..")
                    continue;

                $safe = $entry;
                $entry = "$directoryName/$entry";
                if ( is_dir($entry) )
                    $result .= self::find_markups_recursive($entry);
                else if (is_file($entry) && $safe=="markup.txt")
                    $result .= "\n$entry";
            }
            closedir($dirhandle);

            return $result;
        }

        return "";
    }
}




//----------------------------------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------------------------

//the following classes are duplicates from above. The changes are minor, only necessary so that we can use the cases_hidden.txt file for the few error changes

/* class AtheneErrorCase
 *  implements the <err> tag functionality; this
 *  class should be used to manage all error cases;
 *  it is not recognized by the underlying IL system
 *  (it's name is not AtheneMarkupIL_err); it presents
 *  a top-level interface for any managing context to handle
 *  <err> tags independently of the AtheneMarkupIL system; in other
 *  words, an <err> tag is a "special-case" tag that's handled by
 *  an AtheneErrorManager and handles any other IL sub-structures
 */
class AtheneErrorCase_hidden extends AtheneMarkupIL {
    private $errLog = ''; // store errors that occurred with specific object
    public function get_parse_error_log() {
        return $this->errLog;
    }

    function __construct(AtheneTag $tagObject) {
        self::$parseLog = ''; // reset parse log for construction-time errors
        parent::__construct($tagObject); // invoke base-class c-stor
        $this->errLog .= self::$parseLog; // cache parse log for this object
    }

    /* get_result (AtheneErrorCase) - returns the result of the error case evaluation
     *  as HTML; kind=output
     */
    final function get_result(array $variableTokens) {
        self::$parseLog = ''; // reset parse log for construction-time errors

        // tag format documented in athene/research/error-markup-docs.txt
        $html = '';
        foreach ($this->contentElements as $elem) {
            if ( self::is_element_ilobj($elem) ) {
                $res = $elem->get_result($variableTokens);
                if ( is_string($res) ) // just include string output
                    $html .= "$res";
            }
            else if ( self::is_element_string($elem) )
                $html .= $elem;
            else if ( self::is_element_token($elem) && self::is_variable_token($elem) && array_key_exists($elem->tok,$variableTokens) )
                $html .= $variableTokens[$elem->tok];
            else if (self::is_element_token($elem) && self::is_global_variable_token($elem))
                $html .= self::lookup_global_variable($elem->tok);
            else
                self::$parseLog .= "Error: unexpected element found in <err> tag\n";
        }

        // catalog any errors that happened locally for this object
        $this->errLog .= self::$parseLog;

        //is the enhanced feedback that appears after "On line #:"
        return $html;
    }
}

/* class ErrorManager
 *  manages the error cases for a session;
 *  this type handles all <err> tags within
 *  one or more markup files
 */
class AtheneErrorManager_hidden {
    // error logging
    private $parseLog = '';
    public function get_parse_error_log() {
        return $this->parseLog;
    }

    private $errLookup = array(); // associates error source string to AtheneTag object
    private $errModifierLookup = array(); // associates modifier token to error source string array

    /* Constructor for class AtheneErrorManager - create a new AtheneErrorManager, which loads
     *  error cases from the specified file(s) as well
     *  as from the global list of files; an
     *  AtheneErrorManager object reads the markup from
     *  the specified files and stores the text until
     *  it's ready to be processed as an error case
     */
    function __construct($extraFiles = array()) {
        foreach (array_merge($extraFiles,self::load_catalog()) as $filename) {
            if ($filename == "")
                continue;
            if ( file_exists($filename) ) {
                $iter = 0;
                $line_no = 1;
                $text = @file_get_contents($filename,true);

                while (true) {
                    $newTag = new AtheneTag;
                    if ( $newTag->read_tag($text,$iter,$line_no) ) {
                        if ( $newTag->get_name()!=="err" )
                            $this->parseLog .= "Error: file $filename: expected <err> tag at file scope; found '" . $newTag->get_name() . "'\n";
                        else if ( is_null( $newTag->get_attribute() ) )
                            $this->parseLog .= "Error: file $filename: expected <err> tag with error source string attribute\n";
                        else {
                            $sourceStrings = self::get_source_strings( $newTag->get_attribute(),$modifier );
                            foreach ($sourceStrings as $str) {
                                $this->errLookup[$str] = $newTag;
                                $this->errModifierLookup[$modifier][] = $str;
                            }
                        }
                    }
                    else
                        break;
                }

                if (count($this->errLookup) == 0)
                    $this->parseLog .= "Error: file $filename: found no error cases in markup\n";

                // add any parse errors that happened in a markup tag
                $this->parseLog .= AtheneTag::get_parse_error_log();
                AtheneTag::reset_parse_error_log();
            }
            else
                $this->parseLog .= "Error: could not open specified markup file '$filename'\n";
        }
    }

    /* list_error_source_strings - returns a string that contains all
     *  of the supported error source strings
     *  for which the manager can actively
     *  generate an AtheneErrorCase
     */
    function list_error_source_strings() {
        $supported = '';
        $keys = array_keys($this->errLookup);

        foreach ($keys as $key) {
            $supported .= "$key\n";
        }

        return $supported;
    }

    /* list_error_source_strings_comparison - returns a string that contains all
     *  of the supported error source strings
     *  organized by comparison predicate for
     *  which the manager can actively generate
     *  an AtheneErrorCase
     */
    function list_error_source_strings_comparison() {
        $supported = '';

        foreach ($this->errModifierLookup as $key => $value) {
            $supported .= "($key)\n";
            foreach ($value as $src)
                $supported .= "\t$src\n";
        }

        return $supported;
    }

    /* supports_error - returns true if the specified error source
     *  string is supported by the error manager
     */
    function supports_error($errorSource) {
        return !is_null( $this->lookup($errorSource) );
    }

    /* get_error_case - returns an AtheneErrorCase object for the case
       represented by the specified source string; if no such case
       exists, null is returned
    */
    function get_error_case($errorSource) {
        $src = $this->lookup($errorSource);

        if ( !is_null($src) )
            return new AtheneErrorCase_hidden( $this->errLookup[$src] );
        return null;
    }

    /* Returns a source string if the case represented by the
     *  specified source string exists or null on failure
     */
    private function lookup($errorSource) {
        foreach ($this->errModifierLookup as $modifier => $sourceArray) {
            $callMe = "compare_case_$modifier";
            if ( method_exists(get_class(),$callMe) ) {
                $result = self::$callMe($errorSource,$sourceArray);
                if ( !is_null($result) )
                    return $result;
            }
            else {
                // add an error message to the log for debugging purposes
                $this->parseLog .= "Error: attempted to use unrecognized predicate modifier '$modifier' for the following source strings:\n";
                foreach ($sourceArray as $elem)
                    $this->parseLog .= "\t$elem\n";
            }
        }
        return null;
    }

    /* Returns an array of all the source strings
     *  contained within the specified attribute string
     *  and puts a modifier token in $modifiers if one exists.
     *  Note: if more than one token is found, the last token
     *  found is kept as the modifier token for all source strings
     */
    private static function get_source_strings($attribute,&$modifier) {
        $strs = array();
        $length = strlen($attribute);
        $iter = 0;
        $modifier = "mcase"; // the default (implied) modifier is match-case

        while ( true ) {
            $src = '';

            // find begin-quote
            $tok = '';
            while ( $iter < $length ) {
                if ( $attribute{$iter}=='"' )
                    break;

                if ( ctype_space( $attribute{$iter} ) ) { // apply token to modifier argument
                    if ( $tok != '' ) {
                        $modifier = strtolower($tok);
                        $tok = '';
                    }
                }
                else
                    $tok .= $attribute{$iter};

                $iter++;
            }

            // apply token (if it was modified) to modifier argument
            if ( $tok != '' )
                $modifier = strtolower($tok);

            if ( $iter>=$length )
                break; // no begin-quote found

            // get source string (watch out for escaped \"s)
            $iter++;
            while ( $iter<$length && $attribute{$iter}!='"' ) {
                if ( $attribute{$iter}=="\\" )
                    ++$iter;
                $src .= $attribute{$iter};

                $iter++;
            }

            $strs[] = $src;
            $iter++;
        }

        return $strs;
    }

    /* Error case comparison functions
     */
    static private function compare_case_mcase($comparisonString,array $sourceStringArray) {
        foreach ($sourceStringArray as $str)
            if ( $comparisonString===$str )
                return $str;
        return null;
    }
    static private function compare_case_icase($comparisonString,array $sourceStringArray) {
        $comparisonString = strtolower($comparisonString);
        foreach ($sourceStringArray as $str) {
            $compStr = strtolower($str);
            if ( $comparisonString===$compStr )
                return $str;
        }
        return null;
    }
    static private function compare_case_substr($comparisonString,array $sourceStringArray) {
        // the source string is a needle in the haystack formed by the comparison string
        foreach ($sourceStringArray as $str) {
            if ( strpos($comparisonString,$str) !== false )
                return $str;
        }
        return  null;
    }

    static private function load_catalog() {
        global $p1Catalog_hidden;
        $ATHDIR = getenv("ATHENE_DIRECTORY");

        if ($ATHDIR !== false) {
            // use catalog markups from catalog tree
            return explode("\n",self::find_markups_recursive($ATHDIR));
        }

        // use catalog from P1 repository
        return $p1Catalog_hidden;
    }

    // produce string of newline-delimited file names representing
    // markup case files to load into the markup system
    static private function find_markups_recursive($directoryName) {
        $dirhandle = @opendir($directoryName);
        if ($dirhandle !== false) {
            $result = "";

            while (false !== ($entry = readdir($dirhandle))) {
                if ($entry=="." || $entry=="..")
                    continue;

                $safe = $entry;
                $entry = "$directoryName/$entry";
                if ( is_dir($entry) )
                    $result .= self::find_markups_recursive($entry);
                else if (is_file($entry) && $safe=="markup.txt")
                    $result .= "\n$entry";
            }
            closedir($dirhandle);

            return $result;
        }

        return "";
    }
}

?>
