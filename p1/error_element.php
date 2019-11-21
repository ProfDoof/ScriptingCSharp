<?php

/* error_element.php
 *  contains an interface that provides
 * mechanisms for parsing G++ compile error
 * messages
 */

/* class GPPErrorElement
 *  basic interface for g++ error elements
 */
abstract class GPPErrorElement {
    protected $source; // original "in-error" source

    public function get_source_string() {
        return $this->source;
    }

    /* [integer] input
     *  accepts the source string for an element and
     *  optionally its sub-elements; this member function
     *  should return the next input position for another
     *  element to use, or negative upon failure
     */
    abstract public function input($errSource,$iter,$srcLen);

    /* [string] next_element
     *  returns the next element string from the
     *  error source string; if $readToEOL, then normal delimiters
     *  are ignored until the end-of-line is reached
     */
    static protected function next_element($source,&$iter,$srcLength,$readToEOL = false) {
        $element = '';

        while ( $iter<$srcLength ) {
            $c = $source{$iter++};

            // the colon (:) acts as a delimiter only if $readToEOL == false
            // and if no backslash appears after the colon; this allows for compatibility for
            // Windows systems where the colon separates a drive letter from the path; this allows the following
            // to be parsed correctly:
            // c:\mingw\bin\../lib/gcc/mingw32/4.6.2/include/c++/iostream:61:18: note:   'std::cin'
            if ( (!$readToEOL && $c==':' && ($iter>=$srcLength || $source{$iter}!="\\")) || $c=="\n" || $c=="\r" )
                break;

            $element .= $c;
        }

        // advance past any delimiters and whitespace
        while ( $iter<$srcLength && self::instr(" :\n\r",$source{$iter}) )
            $iter++;

        return $element;
    }

    static protected function instr($string,$char) {
        $len = strlen($string);
        for ($cnt = 0;$cnt < $len;$cnt++)
            if ( $string{$cnt} == $char )
                return 1;
        return 0;
    }
}

/* class GPPTokenizableElement
 *  provides functionality for elements that contain variable tokens
 */
abstract class GPPTokenizableElement extends GPPErrorElement {
    protected $tokens = array(); // associates a token label with its corresponding value

    /* holds the source string in its tokenized form; for example,
     * "'Int' was not declared in this scope" becomes "%1 was not declared in this scope"
     */
    protected $tokenized_source;

    public function get_tokenized_source_string() {
        return $this->tokenized_source;
    }
    public function get_variable_tokens() {
        return $this->tokens;
    }

    protected function tokenize_source() {
        $srcLength = strlen($this->source);
        $this->tokenized_source = '';

        $cnt = 0;
        $tokenCnt = "1";
        while ( $cnt < $srcLength ) {
            if ( self::is_open_tick($this->source,$cnt,$srcLength) ) {
                $tokenKey = '%' . $tokenCnt++;
                $tokenValue = '';

                while ( $cnt<$srcLength && !self::is_close_tick($this->source,$cnt,$srcLength) )
                    $tokenValue .= $this->source[$cnt++];

                $this->tokenized_source .= $tokenKey;
                $this->tokens[$tokenKey] = $tokenValue;
            }
            else
                $this->tokenized_source .= $this->source[$cnt++];
        }

        /*for ($cnt = 0;$cnt < $srcLength;$cnt++) {
            // the single-quote (')  delimits variable tokens
            // within an error message/scope description
            if ( $this->source{$cnt} == "'" ) {
                $tokenKey = '%' . $tokenCnt++;
                $tokenValue = '';

                $cnt++;
                while ( $cnt<$srcLength && $this->source{$cnt} != "'" )
                    $tokenValue .= $this->source{$cnt++};

                $this->tokenized_source .= $tokenKey;
                $this->tokens[$tokenKey] = $tokenValue;
            }
            else
                $this->tokenized_source .= $this->source{$cnt};
        }*/
    }

    static private function is_open_tick($source,&$iter,$sourceLen) {
        /* the UTF-8 LEFT-SINGLE-QUOTATION-MARK denotes the beginning of
           a variable token */
        if ( $iter+2<$sourceLen && ord($source[$iter])==0xe2 && ord($source[$iter+1])==0x80 && ord($source[$iter+2])==0x98 ) {
            $iter += 3;
            return true;
        }
        /* the ASCII single tick for some versions of GCC may 
            have to be used */
        else if ( $source[$iter] == "'" ) {
           ++$iter;
           return true;
        }
        return false;
    }
    static private function is_close_tick($source,&$iter,$sourceLen) {
        /* the UTF-8 RIGHT-SINGLE-QUOTATION-MARK denotes the end of
           a variable token */
        if ( $iter+2<$sourceLen && ord($source[$iter])==0xe2 && ord($source[$iter+1])==0x80 && ord($source[$iter+2])==0x99 ) {
            $iter += 3;
            return true;
        }
        /* the ASCII single tick for some versions of GCC may 
            have to be used */
        else if ( $source[$iter] == "'" ) {
           ++$iter;
           return true;
        }
        return false;
    }

}

/* class GPPErrorMessage
 *  represents a single instance of an error message
 */
class GPPErrorMessage extends GPPTokenizableElement {
    // line and column info might be useful later (source lookup, ETC.)
    private $line_no;
    private $column_no; // some implementations of GCC provide column information; some don't

    function __construct($lineNumber,$columnNumber) {
        $this->line_no = $lineNumber;
        $this->column_no = $columnNumber;
    }

    function get_line_no() {
        return $this->line_no;
    }
    function get_column_no() {
        return $this->column_no;
    }

    final public function input($errSource,$iter,$errLen) {
        // read off the message to the end of the line
        $this->source = self::next_element($errSource,$iter,$errLen,true);
        $this->tokenize_source();

        // fail if there was no source text
        return strlen($this->source) > 0 ? $iter : -1;
    }
}

/* class GPPScopeDescription
 *  represents the errors for a scope, plus the actual
 *  description of the scope in which the errors occurred
 */
class GPPScopeDescription extends GPPTokenizableElement {
    // arrays of GPPErrorMessage objects
    private $errors = array();
    private $warnings = array(); // probably not going to be used (since -Werror is set), but included for completeness
    private $fatalErrors = array(); // these compile errors generally stop compilation before it begins

    // provide a mechanism for cycling through errors
    private $errorIter = 0;
    public function get_number_of_errors() {
        return count($this->errors);
    }
    public function get_next_error() {
        if ( $this->errorIter < count($this->errors) )
            return $this->errors[$this->errorIter++];
        $this->errorIter = 0;
        return null;
    }

    // provide a mechanism for cycling through warnings
    private $warningIter = 0;
    public function get_number_of_warnings() {
        return count($this->warnings);
    }
    public function get_next_warning() {
        if ( $this->warningIter < count($this->warnings) )
            return $this->warnings[$this->warningIter++];
        $this->warningIter = 0;
        return null;
    }

    // provide a mechanism for cycling through fatal errors
    private $fatalIter = 0;
    public function get_number_of_fatalerrs() {
        return count($this->fatalErrors);
    }
    public function get_next_fatalerr() {
        if ( $this->fatalIter < count($this->fatalErrors) )
            return $this->fatalErrors[$this->fatalIter++];
        $this->fatalIter = 0;
        return null;
    }

    final public function input($errSource,$iter,$errLen) {
        // read to end-of-line
        $iterCopy = $iter;
        $this->source = self::next_element($errSource,$iter,$errLen,true);

        // sometimes g++ will not report a scope-description;
        // in this case, the iterator needs to be reset
        // and a default scope description will be provided
        if ( !$this->check_description() ) {
            $iter = $iterCopy;
            $this->source = "Unknown scope:";
        }
        else
            $this->tokenize_source();

        if ( strlen($this->source) > 0 ) {
            // elements appear in the following order
            //  [file]:[line-no]:[column-no-opt]:[error-level]:[error-message-to-end]

            while (true) {
                $iterCopy = $iter; // save at beginning

                // $junk will either refer to the file-name element or the line-no
                // depending on whether or not a scope description was actually read in
                // or provided by default
                $junk = self::next_element($errSource,$iter,$errLen); // read off [file] OR [line-no]
                if ( !ctype_digit($junk) ) {
                    // $junk did not refer to the line-no and therefore was the file name;
                    // so safely read off [line-no]
                    $line_no = self::next_element($errSource,$iter,$errLen);
                    if ( !ctype_digit($line_no) ) {
                        // bad formatting
                        $iter = $iterCopy;
                        break;
                    }
                }
                else
                    $line_no = $junk;

                // attempt to read column number, which occurs in some
                // versions of g++ and not others (frustratingly)
                $iterCopy = $iter;
                $column_no = self::next_element($errSource,$iter,$errLen);
                if ( !ctype_digit($column_no) ) {
                    $iter = $iterCopy;
                    $column_no = 0;
                }

                $errorLevel = self::next_element($errSource,$iter,$errLen);

                $errMsg = new GPPErrorMessage($line_no,$column_no);
                $iterCopy = $errMsg->input($errSource,$iter,$errLen);
                if ($iterCopy < 0)
                    break;

                $iter = $iterCopy;
                if ( $errorLevel == "warning" )
                    $this->warnings[] = $errMsg;
                else if ( $errorLevel == "error" )
                    $this->errors[] = $errMsg;
                else if ( $errorLevel == "fatal error" )
                    $this->fatalErrors[] = $errMsg;
                else if ( $errorLevel == "note" ) {
                    continue; // function overload info (mostly) that we'll skip
                }
                else
                    break; // format error
            }

            // say failure if no errors or warnings were parsed
            return count($this->errors)>0 || count($this->warnings)>0 || count($this->fatalErrors)>0 ? $iter : -1;
        }

        // fail
        return -1;
    }

    // sometimes, a scope description is not provided
    // by the compiler; this member function determines
    // if the source string is actually a scope description
    private function check_description() {
        return !ctype_digit($this->source{0});
    }
}

/* class GPPFileNameElement
 *  represents the error information for a single
 *  implementation file or header file
 */
class GPPFileNameElement extends GPPErrorElement {
    // the sub-elements for the file name
    private $scopes = array();

    // returns the code file name that was processed
    public function get_file_name() {
        return $this->source;
    }

    // provide a mechanism for iterating through
    // the different scope descriptions that the file
    // element parsed
    private $scopeIter = 0;
    public function get_next_scope() {
        if ( $this->scopeIter < count($this->scopes) )
            return $this->scopes[$this->scopeIter++];
        $this->scopeIter = 0;
        return null;
    }

    public function read_compiler_output($output) {
        if ( is_string($output) ) {
            $iter = 0;
            $len = strlen($output);
            return $this->input($output,$iter,$len) >= 0;
        }
        return false;
    }

    final public function input($errSource,$iter,$srcLen) {
        $this->source = self::next_element($errSource,$iter,$srcLen); // will (should) hold file name

        // check for other compiler messages
        if ( $this->source == "cc1plus" ) {
            self::next_element($errSource,$iter,$srcLen,true);
            return $this->input($errSource,$iter,$srcLen);
        }

        if ( strlen($this->source) > 0 ) {
            do {
                $scope = new GPPScopeDescription;
                $i = $scope->input($errSource,$iter,$srcLen);
                if ($i < 0)
                    break;

                $this->scopes[] = $scope;
                $iter = $i;

                $elem = self::next_element($errSource,$iter,$srcLen);
            } while ($elem === $this->source);

            // fail if no complete scopes were found
            return count($this->scopes) > 0 ? $iter : -1;
        }

        // assume failure
        return -1;
    }
}

?>
