<?php

/* source_parser.php
 *  contains a programming interface for
 * parsing C++ source code
 */

/* provides basic generic token representation and
   decomposition */
abstract class Token {
    protected $payload = ''; // token value

    /* factory function that generates token objects of the correct
       kind; if the end of stream has been reached, then null is
       returned */
    static function decompose_next_token($source,$slength,&$iterator,array &$context) {
        // list of all token sub-classes; the order of the token classes is somewhat sensitive and
        // is explained below:
        static $subclasses = array('WSpaceToken', // whitespace is never considered part of an initial token; check it first
            'HeaderNameToken', // could potentially contain operator characters (<>) or look like a string literal
            'StringLiteralToken', // could start with identifier character (prefix)
            'CharacterLiteralToken', // could start with identifier character (prefix)
            'NumericLiteralToken', // could start with identifier character (prefix) or operator character
            'OperatorPunctuatorToken', // few identifiers (e.g. new) are operators (and keywords); check ops/puncs before identifiers
            'KeywordToken', // these are mainly special identifiers
            'IdentifierToken', // this should handle a large majority of the source code
            'MiscToken' // should handle anything left over
            );

        if ($iterator >= $slength)
            return null;

        // attempt to decompose next characters in sequence as
        // tokens; try each kind until success
        foreach ($subclasses as $tokenKind) {
            $tok = new $tokenKind;
            if ( $tok->decompose($source,$slength,$iterator,$context) )
                return $tok;
        }

        throw new Exception("Token::decompose_next_token: no token matched or end of stream flagged");
    }

    /* determines if the specified character is acceptable for the token kind
       based on the specified source set */
    static protected function is_acceptable_char($char,$sourceSet,$negate = false) {
        // determine if the character belongs to the specified source set
        $contains = strpos($sourceSet,$char) !== false;
        return (!$negate && $contains) || ($negate && !$contains);
    }

    /* performs a decomposition of the sequence of characters that are known
       to be expected; this sequence terminates when an invalid character is
       found; the longest valid sequence of characters is used to produce the
       returned value */
    static protected function sequence_decomposition($source,$slength,&$iterator,$elems,$values = null,$negate = false) {
        if (is_null($elems) || $elems=='' || (!is_null($values) && !is_array($values)))
            return '';

        $iter = $iterator;
        $usesValues = !empty($values);
        $valid = '';
        $value = '';

        while ($iter<$slength && self::is_acceptable_char($source[$iter],$elems,$negate)) {
            // append character to token value
            $value .= $source[$iter++];

            // determine if current value is valid
            if (!$usesValues || in_array($value,$values))
                $valid = $value;
        }

        $iterator += strlen($valid);
        return $valid;
    }

    /* creates a new token of the specified type with the specified
       default payload value */
    static function make_token($type,$payload) {
        $tok = new $type;
        $tok->payload = $payload;
        return $tok;
    }

    function __toString() {
        return $this->get_value();
    }

    /* gets the string value that represents the token's payload */
    function get_value() {
        return $this->payload;
    }
}

/* represents a sequence of whitespace characters; this token
   is mainly used by the implementation */
class WSpaceToken extends Token {
    /* decomposes the next characters in the source stream as a token;
       returns false if the next characters do not match the token kind */
    protected function decompose($source,$slength,&$iterator,array &$context) {
        $this->payload = parent::sequence_decomposition($source,$slength,$iterator,CPlusPlus::$whitespace);
        return $this->payload != '';
    }

    function __toString() {
        return get_class();
    }

    /* gets the number of newline characters in the token's payload */
    function count_newlines() {
        $len = strlen($this->payload);
        $cnt = 0;

        for ($i = 0;$i < $len;$i++)
            if ($this->payload[$i] == "\n")
                ++$cnt;

        return $cnt;
    }
}

/* represents a header-name specifier used in #include
   directives */
class HeaderNameToken extends Token {
    private $isSystem; // true: <header-name>; false: "header-name"

    /* decomposes the next characters in the source stream as a token;
       returns false if the next characters do not match the token kind */
    protected function decompose($source,$slength,&$iterator,array &$context) {
        // examine context: look at the two non-whitespace tokens that are most recent
        $cnt = count($context) - 1;

        // make $cnt refer to last non-whitespace token
        while ($cnt>0 && get_class($context[$cnt])=='WSpaceToken') {
            if ($context[$cnt]->count_newlines() > 0)
                return false; // header-name can only appear on same line as #include directive
            --$cnt;
        }

        // make $cnt refer to token before last non-whitespace token
        if (--$cnt < 0)
            return false;

        // expect tokA to be op-punc with value '#' and expect
        // tokB to be id with value 'include'
        $tokA = $context[$cnt];
        $tokB = $context[$cnt+1];
        if (get_class($tokA)!='OperatorPunctuatorToken' || $tokA->payload!='#'
            || get_class($tokB)!='IdentifierToken' || $tokB->payload!='include')
            return false;

        // make sure that the tokens are the first
        // non-whitespace tokens on their row
        while (--$cnt >= 0) {
            $tok = $context[$cnt];
            if (get_class($tok) != 'WSpaceToken') {
                // TODO: generate an error token: #include directive valid only as first element on line
                return false;
            }
            if ($tok->count_newlines() > 0)
                break;
        }

        // expect header-name: <q-char-seq> OR "h-char-seq"
        if ($iterator < $slength) {
            if ($source[$iterator] == '<') {
                $unexcept = ">\n";
                $this->isSystem = true;
            }
            else if ($source[$iterator] == '"') {
                $unexcept = '"' . "\n";
                $this->isSystem = false;
            }
            else {
                // TODO: generate an error token: expected header-name
                return false;
            }

            ++$iterator;
            $this->payload = parent::sequence_decomposition($source,$slength,$iterator,$unexcept,null,true);

            // move past the terminating > or " if it was found; it does not belong in another token
            if ($iterator<$slength && ($source[$iterator]=='>' || $source[$iterator]=='"'))
                ++$iterator;

            return true;
        }

        return false;
    }

    function is_system_header() {
        return $this->isSystem;
    }
}

/* represents C++ operator and punctuator tokens; distinction between
   operators and punctuators is resolved later in another context */
class OperatorPunctuatorToken extends Token {
    /* decomposes the next characters in the source stream as a token;
       returns false if the next characters do not match the token kind */
    protected function decompose($source,$slength,&$iterator,array &$context) {
        // there are special operators composed of non operator/punctuator
        // characters; check for these first
        foreach (CPlusPlus::$letterops as $op)
            if ( $this->check_letter_operator($op,$source,$slength,$iterator) )
                return true;

        // perform sequence decomp with characters in op/punc set
        $this->payload = parent::sequence_decomposition($source,$slength,$iterator,CPlusPlus::$oppuncchars,CPlusPlus::$opspuncs);
        return $this->payload != '';
    }

    function is_operator() {
        return in_array($this->payload,CPlusPlus::$ops);
    }

    function is_punctuator() {
        return in_array($this->payload,CPlusPlus::$punctuators);
    }

    private function check_letter_operator($operator,$source,$slength,&$iterator) {
        // check if operator could exist in input stream
        $len = strlen($operator);
        $iter = $iterator;
        if ($iterator + $len > $slength)
            return false;

        $actual = '';
        for ($i = 0;$i < $len;$i++,$iter++)
            $actual .= $source[$iter];

        if ($iter<$slength && parent::is_acceptable_char($source[$iter],CPlusPlus::$namechar))
            return false; // name is a substring of a larger identifier

        if ($actual == $operator) {
            $this->payload = $operator;
            $iterator = $iter;
            return true;
        }

        return false;
    }
}

/* represents a string literal token; the token's payload is the string data itself;
   this class serves as the base class for character literals as well */
class StringLiteralToken extends Token {
    private $prefix; // optional prefix specifiers before string literal

    /* decomposes the next characters in the source stream as a token;
       returns false if the next characters do not match the token kind */
    protected function decompose($source,$slength,&$iterator,array &$context) {
        static $prefixchars = "u8UL";
        static $prefixes = array("u8","u","U","L");
        $iter = $iterator;

        // handle special cases with prefixes
        $this->prefix = parent::sequence_decomposition($source,$slength,$iter,$prefixchars,$prefixes);

        if ($iter<$slength && !$this->is_quote_char($source[$iter])) {
            ++$iter;
            $this->payload .= $this->string_sequence_decomposition($source,$slength,$iter);
            $iterator = $iter+1;
            return true;
        }

        return false;
    }

    private function string_sequence_decomposition($source,$slength,&$iterator) {
        $value = '';

        while ($iterator<$slength && $this->is_quote_char($source[$iterator])) {
            // append character to token value
            $value .= $source[$iterator];

            // ensure escape characters become part of the value and
            // do not serve as delimiters
            if ($source[$iterator++] == '\\')
                $value .= $source[$iterator++]; // the escape character must be part of the value
        }

        return $value;
    }

    protected function is_quote_char($char) {
        return $char != '"';
    }
}

/* represents a character literal token */
class CharacterLiteralToken extends StringLiteralToken {
    // (I'll say that a character behaves just like a string
    // except that it uses a single-quote)
    protected function is_quote_char($char) {
        return $char != "'";
    }
}

/* represents either an integral or floating point literal token */
class NumericLiteralToken extends Token {
    /* decomposes the next characters in the source stream as a token;
       returns false if the next characters do not match the token kind */
    protected function decompose($source,$slength,&$iterator,array &$context) {
        // a pp-number may begin with a '.' or a digit followed by any
        // digit or non-digit or '.'
        if ($iterator >= $slength)
            return false;
        if ( !parent::is_acceptable_char($source[$iterator],CPlusPlus::$digit . '.') ) {
            $iter = $iterator+1;
            if ($iter>=$slength || $source[$iter]!='.' || !parent::is_acceptable_char($source[$iter],CPlusPlus::$digit))
                return false;
        }

        $accept = CPlusPlus::$digit . CPlusPlus::$nondigit . '.';
        $sign = "+-";
        while ($iterator < $slength) {
            if (strpos($accept,$source[$iterator]) === false)
                break;
            $this->payload .= $source[$iterator];
            if ($source[$iterator++] == 'e') // expect an optional sign
                if ($iterator<$slength && strpos($sign,$source[$iterator])!==false)
                    $this->payload .= $source[$iterator++];
        }

        return $this->payload != '';
    }
}

/* represents a C++ identifier token */
class IdentifierToken extends Token {
    /* decomposes the next characters in the source stream as a token;
       returns false if the next characters do not match the token kind */
    protected function decompose($source,$slength,&$iterator,array &$context) {
        return $this->decompose_identifier($source,$slength,$iterator,$context);
    }

    protected function decompose_identifier($source,$slength,&$iterator,array &$context) {
        // an identifier must begin with a non-digit
        if ($iterator<$slength && parent::is_acceptable_char($source[$iterator],CPlusPlus::$nondigit)) {
            $this->payload = parent::sequence_decomposition($source,$slength,$iterator,CPlusPlus::$namechar);
            return $this->payload != '';
        }

        return false;
    }
}

/* represents a special identifier reserved for the language's use */
class KeywordToken extends IdentifierToken {
    /* decomposes the next characters in the source stream as a token;
       returns false if the next characters do not match the token kind */
    function decompose($source,$slength,&$iterator,array &$context) {
        // a keyword is essentially a special identifier that matches
        // one of the defined keyword symbols
        $iter = $iterator;
        if ($this->decompose_identifier($source,$slength,$iter,$context) && in_array($this->payload,CPlusPlus::$keywords)) {
            $iterator = $iter;
            return true;
        }
        return false;
    }
}

/* represents any kind of unidentified token; any character can be
   matched for this token type except whitespace */
class MiscToken extends Token {
    /* decomposes the next characters in the source stream as a token;
       returns false if the next characters do not match the token kind */
    protected function decompose($source,$slength,&$iterator,array &$context) {
        $this->payload = parent::sequence_decomposition($source,$slength,$iterator,CPlusPlus::$whitespace,null,true);
        return $this->payload != '';
    }

}

/* contains useful sets of C++ constructs */
class CPlusPlus {
    static public $nondigit = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_";
    static public $digit = "0123456789";
    static public $namechar = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_0123456789"; // non-digit and digit
    static public $whitespace = " \t\r\n\v";

    static $directives = array('include','define'); // directives handled by this system

    static public $keywords = array('alignof','case','class','asm','catch','const','auto','char','constexpr',
        'bool','char16_t','const_cast','break','char32_t','continue','default','delete',
        'enum','float','do','explicit','for','double','export','friend',
        'dynamic_cast','extern','goto','else','false','if','inline','new','protected',
        'int','noexcept','public','long','nullptr','register','mutable','operator',
        'reinterpret_cast','namespace','private','return','short','static_cast','thread_local',
        'signed','struct','throw','sizeof','switch','true','static','template','try','static_assert',
        'this','typedef','typeid','virtual','typename','void','union','volatile','unsigned','wchar_t',
        'using','while');
    static public $booleanLiteral = array('true','false');

    static public $oppuncchars = "{}[]#();:.?.,*+-*/%^&|~!=<>"; // operator/punctuator characters
    static public $ops = array('#','##',':','...','?','::','.','.*','+','-','*','/','%','^','&','|','~','!',
        '=','<','>','+=','-=','*=','/=','%=','^=','&=','|=','<<','>>','>>=','<<=','==','!=','<=','>=','&&','||',
        '++','--','->*','->'); // operators: not exhaustive due to special operators
    static public $opspuncs = array('#','##',':','...','?','::','.','.*','+','-','*','/','%','^','&','|','~','!',
        '=','<','>','+=','-=','*=','/=','%=','^=','&=','|=','<<','>>','>>=','<<=','==','!=','<=','>=','&&','||',
        '++','--','->*','->','{','}','[',']','(',')',',',';');
    static public $unaryops = array('*','&','+','-','!','~','++','--');
    static public $postfixops = array('++','--');
    static public $assignops = array('=','*=','/=','%=','+=','-=','>>=','<<=','&=','^=','|=');
    static public $punctuators = array('{','}','[',']','(',')',',',';'); // punctuators: what generally are considered punctuators
    static public $letterops = array('new','delete','and','and_eq','bitand','bitor','compl','not','not_eq','or','or_eq','xor','xor_eq');
    static public $overloadableops = array('new','delete','+','-','*','/','%','^','&','|','~','!','=','<','>','+=','-=','*=','/=','%=',
        '^=','&=','|=','<<','>>','>>=','<<=','==','!=','<=','>=','&&','||','++','--',',','->*','->','('/*expect ')' after this*/,
        '['/*expect ']' after this*/);

    // generic declaration specifiers that don't fit a particular category
    static public $ungrouped_decl_specifier = array('friend','typedef');

    // specifiers for built-in types (they are keywords in the language)
    static public $simple_type_specifier = array('int','char','double','float','bool','void',
        'short','signed','unsigned','long','wchat_t','auto'/*C++11*/,'char16_t','char32_t');

    // specifiers for user-defined types; I include some that are commonly
    // used in the P1 course; types are added here as they are found declared in the source
    static public $user_defined_type_specifier = array('string','stringstream');

    // names (identifiers) that can be used as nested-name-specifiers (e.g. std::cout);
    // I include those commonly used in the P1 course
    static public $user_defined_nested_name_specifier = array('std');

    // qualifiers for types/member functions
    static public $cv_qualifier = array('const','volatile');

    // function/variable storage class specifiers
    static public $storage_class_specifier = array('auto','register','static','extern','mutable');

    // function specifiers
    static public $function_specifier = array('inline','virtual','explicit');
}

/* provides many of the operations performed by
   a simple C++ preprocessor */
class Preprocessor {
    private $code = ''; // string
    private $tokens = array(); /* objects derived from Token */
    private $includeFiles = array(); /* HeaderNameToken objects */

    static function strip_comments($code) {
        $inQuote = false;
        $inSngl = false;
        $inMulti = false;
        $iterator = 0;
        $sourceLength = strlen($code);
        $result = "";
        $nline = 0;

        // replace all comment instances with a single
        // whitespace character
        while ($iterator < $sourceLength) {
            if ($inQuote) {
                if ($code[$iterator] == "\\")
                    // skip past any escaped characters
                    $result .= $code[$iterator++];
                else if ($code[$iterator] == '"')
                    $inQuote = false;
                $result .= $code[$iterator++];
            }
            else if (!$inSngl && !$inMulti) {
                if ($code[$iterator] == '"')
                    $inQuote = true;
                else if ($iterator+1 < $sourceLength) {
                    // check for comments: // and /*
                    if ($code[$iterator] == '/') {
                        $inext = $iterator+1;
                        if ($code[$inext] == '/')
                            $inSngl = true;
                        else if ($code[$inext] == '*') {
                            $inMulti = true;
                            $nline = 0; // count number of newlines in multi-line comment
                        }
                        if ($inSngl || $inMulti) {
                            $iterator += 2;
                            continue;
                        }
                    }
                }
                $result .= $code[$iterator++];
            }
            else if ($inSngl && $code[$iterator]=="\n") {
                ++$iterator;
                $result .= " \n"; // replace comment with whitespace; preserve newline
                $inSngl = false;
            }
            else if ($inMulti && $iterator+1<$sourceLength && $code[$iterator]=="*" && $code[$iterator+1]=='/') {
                $iterator += 2;
                // replace comment with whitespace followed by appropriate number of newlines
                $result .= " " . str_repeat("\n",$nline); 
                $inMulti = false;
            }
            else {
                if ($inMulti && $code[$iterator]=="\n")
                    ++$nline;
                ++$iterator;
            }
        }

        return $result;
    }

    function __construct($code) {
        // convert multibyte code to ASCII if there are
        // any UTF-8 characters in the source
        $code = @iconv("UTF-8","ASCII//TRANSLIT",$code);

        $code = str_replace("\r\n","\n",$code); // normalize line endings
        $code = str_replace("\\\n","",$code); // resolve line continuation

        // strip comments
        $code = $this->strip_comments($code);

        // perform token decomposition; context-sensitive tokens
        // are interpreted from the list of tokens that have been
        // compiled
        $iterator = 0;
        $slength = strlen($code);
        while (true) {
            $tok = Token::decompose_next_token($code,$slength,$iterator,$this->tokens);
            if ( is_null($tok) )
                break;
            $this->tokens[] = $tok;
        }

        // analyze tokens for anything of value to the preprocessor; this
        // includes any preprocessor directives and error tokens flagged
        // by the token decompositor
        $wspace = Token::make_token('WSpaceToken',' '); // replace preprocessing directives with this
        $iterator = 0;
        $slength = count($this->tokens)-1; // subtract 1 to make $iterator+1 always refer to valid token
        while ($iterator < $slength) {
            if (get_class($this->tokens[$iterator])=='OperatorPunctuatorToken' && $this->tokens[$iterator]->get_value()=='#') {
                $next = $iterator+1;
                if (get_class($this->tokens[$next])=='IdentifierToken' && in_array($this->tokens[$next]->get_value(),CPlusPlus::$directives)) {
                    if ($this->tokens[$next]->get_value() == 'include') {
                        ++$next;
                        while ($next<$slength && get_class($this->tokens[$next])=='WSpaceToken')
                            ++$next;
                        if (get_class($this->tokens[$next]) == 'HeaderNameToken')
                            $this->includeFiles[] = $this->tokens[$next]; // save the token object
                        else
                            ; // TODO: error case for expected header-name
                    }
                    for (;$iterator<=$next;$iterator++)
                        $this->tokens[$iterator] = $wspace;
                    continue;
                }
            }
            ++$iterator;
        }
    }

    /* prints the state of the preprocessor for debugging purposes */
    function debug() {
        $result = "";
        $columns = 2;

        $i = 0;
        foreach ($this->tokens as $tok) {
            $current = "$tok";
            $result .= $current;
            $space = 80 - strlen($current);

            if ($i % ($columns) != 0)
                $result .= "\n";
            else
                for ($j = 0;$j < $space;$j++)
                    $result .= ".";

            ++$i;
        }

        // print information
        $result .= "Line Count: " . $this->count_lines();
        $result .= "\nHeaders included: " . implode(', ',$this->includeFiles);

        echo $result;
    }

    /* returns the real line count of the processed
       source code file */
    function count_lines() {
        // the number of lines is equal to 1 plus the
        // number of newline characters found
        $lineCnt = 1;

        foreach ($this->tokens as $token) {
            if (get_class($token) == 'WSpaceToken')
                $lineCnt += $token->count_newlines();
        }

        return $lineCnt;
    }

    /* gets an array containing the sequence of decomposed tokens; they
       are objects of types derived from Token */
    function get_tokens() {
        return $this->tokens;
    }

    /* gets an array containing strings representing the file; if 
       $includeMetaInfo is true, then each array element is an object
       of type stdClass with the following members: $file and $system,
       where 'file' is the header file name and 'system' is a boolean
       describing if the header was a system header file or not */
    function get_include_files($includeMetaInfo = false) {
        $arr = array();

        if ($includeMetaInfo) {
            foreach ($this->includeFiles as $headerTok) {
                $obj->file = $headerTok->get_value();
                $obj->system = $headerTok->is_system_header();
                $arr[] = $obj;
            }
        }
        else
            foreach ($this->includeFiles as $headerTok)
                $arr[] = $headerTok->get_value(); // just get string file name

        return $arr;
    }

    /* matches $fileName against any files included in the
       source file decomposed by this preprocessor; returns
       true if a match was found */
    function did_include_file($fileName) {
        return in_array($fileName,$this->includeFiles);
    }
}

/* encapsulates the decomposition of a C++ source code
   document into language constructs */
class SourceCode {
    private $parseTree; // root node of parse tree (CPPTranslationUnit)
    private $preprocessor; // store the preprocessor used to decompose the source code document

    // construct object from source code file contents
    function __construct($contents) {
        // let this constructor do everything with the contents of a file
        $this->preprocessor = new Preprocessor($contents);
        $parser = new TokenParser( $this->preprocessor->get_tokens() );
        CPPTranslationUnit::parse_translation_unit($parser);
        $this->unload_parser($parser);
    }

    // retrieve a reference to the preprocessor object used
    // to decompose the source file
    function get_preprocessor()
    { return $this->preprocessor; }

    // retrieve a reference to the root parse tree node object
    function get_parse_tree_root()
    { return $this->parseTree; }

    private function unload_parser(TokenParser $parser) {
        // unload the root node of the parser tree from $parser
        $this->parseTree = $parser->pop_element();
    }
}

/* data structure that represents a result frame in a TokenParser's
   stack (a node in the parse-tree) */
class TokenParserResult {
    public $results = array(); // elements that make up the result (should consist of objects derived from Token, null, or true)
    public $context = ''; // name of class that will process 'results' (the type of the construct that 'results' represents)
    public $lineNo = 0; // line where elements in 'results' were found in source

    // this function is for testing purposes
    function __toString() {
        $stuff = "[$this->context";
        if (count($this->results) > 0) {
            $stuff .= ";{";
            $len = count($this->results);
            for ($i=0;$i<$len;++$i) {
                if ( is_array($this->results[$i]) )
                    $stuff .= implode(' ',$this->results[$i]);
                else if ($this->results[$i] === null)
                    $stuff .= 'p-holder';
                else
                    $stuff .= $this->results[$i];
                if ($i+1<$len)
                    $stuff .= ',';
            }
            $stuff .= "}";
        }
        $stuff .= ";$this->lineNo]\n";
        return $stuff;
    }
}

/* provides functionality and an interface for token processing and parse-tree
   representation; the parse-tree is stored as a stack of results of type 
   TokenParserResult */
class TokenParser {
    private $tokens; // array of objects derived from Token
    private $results; // array of 'TokenParserResult' entries (stack)
    private $working; // result currently being compiled
    private $workingFrames; // array of results currently being compiled (stack)
    private $line; // global line accumulator

    function __construct(array $tokens) {
        $this->tokens = $tokens;
        $this->results = array();
        $this->workingFrames = array();
        $this->line = 1; // at least 1 line

        // handle initial whitespace tokens
        while ( $this->check_whitespace() );
    }

    /* prints the state of the parser for debugging purposes */
    function debug() {
        if (current($this->results) !== false) {
            foreach ($this->results as $res)
                echo "RESULT: $res";
            reset($this->results);
        }
        if ( !is_null($this->working) )
            echo "WORK: $this->working";
        if (current($this->workingFrames) !== false) {
            foreach ($this->workingFrames as $res)
                echo "WORK-FRAME: $res";
            reset($this->workingFrames);
        }
    }

    /* returns true if all of the source tokens have been accessed */
    function end_of_file() {
        return current($this->tokens)===false;
    }

    /* gets a reference to the current token under inspection;
       null is returned when the parser is at end-of-stream */
    function get_current_token($lookahead = false) {
        if ($lookahead) {
            $this->advance();
            $tok = current($this->tokens);
            $this->retreat();
        }
        else
            $tok = current($this->tokens);
        if ($tok === false)
            return null;
        return $tok;
    }

    /* gets the value of the current token under inspection;
       null is returned when the parser is at end-of-stream */
    function get_current_token_value() {
        $tok = current($this->tokens);
        if ($tok === false)
            return '';
        return $tok->get_value();
    }

    /* gets the class name of the current token under inspection;
       null is returned when the parser is at end-of-stream */
    function get_current_token_type() {
        $tok = current($this->tokens);
        if ($tok === false)
            return '';
        return get_class($tok);
    }

    /* returns true if the current token matches the specified type
       and value */
    function does_token_match($type,$value,$lookahead = false) {
        $tok = $this->get_current_token($lookahead);
        if ($tok === false)
            return false;
        return get_class($tok)==$type && $tok->get_value()==$value;
    }

    /* returns true if the current token matches the specified type and
       at least one value in the array of possible values */
    function does_token_match_ex($type,array $possibleValues,$lookahead = false) {
        $tok = $this->get_current_token($lookahead);
        if ($tok === false)
            return false;
        return get_class($tok)==$type && in_array($tok->get_value(),$possibleValues);
    }

    // non-member variants
    static function does_token_match_nm($tok,$type,$value) {
        if ($tok === false)
            return false;
        return get_class($tok)==$type && $tok->get_value()==$value;
    }
    static function does_token_match_nm_ex($tok,$type,array $possibleValues) {
        if ($tok === false)
            return false;
        return get_class($tok)==$type && in_array($tok->get_value(),$possibleValues);
    }

    /* let the parser advance internally to the next token */
    function advance() {
        next($this->tokens); // advance past current token

        // check for and advance through any whitespace tokens,
        // counting line numbers
        while ( $this->check_whitespace() );
    }

    function retreat() {
        prev($this->tokens); // go back

        // go back through any whitespace tokens
        while ( $this->check_whitespace(true) );

        if (current($this->tokens) === false)
            reset($this->tokens);
    }

    /* begins a new result frame */
    function begin_working_result($context) {
        // save the current working result on the workings stack
        if ( is_object($this->working) ) {
            $this->workingFrames[] = $this->working;
            $this->working = null;
        }

        $this->working = new TokenParserResult;
        $this->working->context = $context;
        $this->working->lineNo = $this->line;
    }

    /* gets a reference to the current working result structure */
    function get_working_result() {
        return $this->working;
    }

    /* updates the context (class-name/construct-type) of the working result */
    function update_working_result_context($newContext) {
        $this->working->context = $newContext;
    }

    /* returns the number of elements in the working result frame */
    function get_working_result_size() {
        return count($this->working->results);
    }

    /* returns a truth value indicating if the parser's internal working
       result has had any non-null (place-holder) values pushed on it */
    function is_working_result_nonempty() {
        if ( is_object($this->working) ) {
            $cnt = 0;
            foreach ($this->working->results as $res)
                if (!is_null($res) && !is_bool($res))
                    ++$cnt;
            return $cnt > 0;
        }
        return false;
    }

    /* returns a truth value indicating whether or not the working result
       was flagged as containing errors */
    function is_working_result_error() {
        return $this->working->context == 'CPPErrorElement';
    }

    /* commits the current working result to the parser's
       internal list of results */
    function commit_working_result() {
        array_push($this->results,$this->working);
        // discard the reference to the current working result since
        // it was committed
        $this->working = null;
        $this->discard_working_result();
    }

    /* commits the current working result to the parser's
       internal list of results using the specified context */
    function commit_working_result_as($context) {
        $this->working->context = $context;
        array_push($this->results,$this->working);
        $this->working = null;
        $this->discard_working_result();
    }

    /* discards the parser's current internal working result and restores
       any previous working result; if the working result has results,
       they are merged with the previous result; true is returned if an 
       error result was committed; otherwise false is always returned; if 
       true, 'commitErrors' forces a commit if errors were present in the
       current working result; if 'commitErrors' is null, the working result
       (and any results in its frame) are truly discarded (this is useful for
       simplifying the results stack) */
    function discard_working_result($commitErrors = false) {
        // see if the working result has results; the normal behavior is for a discarded working
        // result to be empty in a non-error scenario
        if (is_bool($commitErrors) && is_object($this->working) && count($this->working->results)>0) {
            if (!$commitErrors) {
                if (current($this->workingFrames) === false) // commit element; no frame exists on which to pass back errors
                    $this->commit_working_result_as('CPPErrorElement');
                else {
                    // pass back errors down the working stack
                    $this->merge_working_result();
                    if ( !is_null($this->working) )
                        $this->working->context = 'CPPErrorElement';
                }
            }
            else {
                // manually stop passing errors down the working stack
                $this->commit_working_result_as('CPPErrorElement');
                return true;
            }
            return false;
        }

        // make 'working' refer to the next element off the 'workingFrames' stack
        // or null if the stack is empty
        if (current($this->workingFrames) !== false)
            $this->working = array_pop($this->workingFrames);
        else
           $this->working = null;
        return false;
    }

    /* discards the parser's current internal working result and restores
       any previous working result, merging (by append) the results of the 
       discarded frame to the last working frame (if there was one) */
    function merge_working_result() {
        // make 'working' refer to the next element off the 'workingFrames' stack
        // or null if the stack is empty; if there was a last working frame, merge
        // the current frame's results with it by appending them
        if (current($this->workingFrames) !== false) {
            $tmp = array_pop($this->workingFrames);
            $tmp->results = array_merge($tmp->results,$this->working->results);
            $this->working = $tmp;
        }
        else
            $this->working = null;
    }

    /* adds the current token to the parser's internal working results frame; the
       parser's internal token iterator is advanced; if the parser is at the
       end-of-stream, no action is taken */
    function add_token() {
        $tok = current($this->tokens);
        if ($tok === false)
            return;
        $this->working->results[] = $tok;
        $this->advance(); // need specific functionality of this method
    }

    /* adds an element to the current result of the parser's
       internal results stack that represents a place-holder
       for the last result frame */
    function add_place_holder() {
        $this->working->results[] = null;
    }

    /* returns a reference to a PHP array containing the stack
       of results that have been compiled; each result is a 
       TokenParserResult object */
    function get_results() {
        return $this->results;
    }

    /* gets the contents of the next (most recent) result on the stack;
       the result is then removed from the stack */
    function pop_result(&$lineNo,&$context) {
        $cur = array_pop($this->results);

        if ($cur !== null) {
            $lineNo = $cur->lineNo;
            $context = $cur->context;
            return $cur->results;
        }

        return null; // signal empty stack
    }

    /* gets an object derived from CPPElement that is the
       instantiation of the last committed result; the result
       is then removed from the stack; NULL is returned if no
       results remain */
    function pop_element() {
        $cur = array_pop($this->results);

        if ($cur !== null) {
            $obj = new $cur->context($cur->results,$this);
            if ( is_subclass_of($obj,'CPPElementEx') )
                $obj->set_line_no($cur->lineNo);
            return $obj;
        }

        // signal empty stack
        return null;
    }

    /* returns the context of the last committed result, which is the 
       right branch node of the successful parse tree in the parser's
       internal results stack */
    function get_last_result_context() {
        end($this->results);
        $obj = current($this->results);
        $val = is_object($obj) ? $obj->context : ''; // return empty string upon error
        reset($this->results);
        return $val;
    }

    /* compares the specified context (class-name/construct-type) strings
       with those of results appearing on the parser's working result frame;
       returns an array of boolean or null values; the values indicate for
       every specified context whether or not it exists inside the parser's
       internal results stack and matched the specified pattern; a value of
       null indicates that no result exists at that position; a value of false
       indicates that a result exists but did not match the one specified at
       the position; true indicates an affirmative match */
    function match_working_contexts(/*variable argument list*/) {
        $result = array();

        if (func_num_args()>0 && !is_null($this->working)) {
            $contexts = array();

            end($this->results);
            end($this->working->results);
            while (true) {
                $item = current($this->working->results);
                if ($item === false)
                    break;
                if ( is_null($item) ) {
                    $contexts[] = current($this->results)->context;
                    $this->walk_contexts_recursive();
                    prev($this->results);
                }
                prev($this->working->results);
            }
            reset($this->working->results);
            reset($this->results);

            $contexts = array_reverse($contexts);
            $args = func_get_args();
            $lenA = func_num_args();
            $lenB = count($contexts);
            for ($i = 0;$i<$lenA;$i++) {
                if ($i >= $lenB)
                    $result[] = null;
                else
                    $result[] = $args[$i]===$contexts[$i] || is_subclass_of($args[$i],$contexts[$i]);
            }
        }

        return $result;
    }

    private function walk_contexts_recursive() {
        $results = current($this->results)->results;
        end($results);

        while (true) {
            $item = current($results);
            if ($item === false)
                break;
            if ( is_null($item) ) {
                prev($this->results);
                $this->walk_contexts_recursive();
            }
            prev($results);
        }
    }

    private function check_whitespace($goBack = false) {
        // advance past any whitespace tokens while counting newlines
        if ($this->get_current_token_type() == 'WSpaceToken') {
            $nl = current($this->tokens)->count_newlines();
            if ($goBack) {
                $this->line -= $nl;
                prev($this->tokens);
            }
            else {
                $this->line += $nl;
                next($this->tokens);
            }
            return $this->get_current_token_type() == 'WSpaceToken';
        }

        return false;
    }
}

abstract class CPPElement {
    private $payload; // store elements that make up the element (each element is a subtree in the syntax tree)
    private $constructs; // parallel array of strings representing names of constructs stored in '$this->payload'

    abstract function get_line_no(); // let the implementation decide how to designate the element's line no.
    abstract function get_name(); // let the implementation decide how to name the element

    function __construct(array $elements,TokenParser $parser) {
        /* 'elements' contains the elements of the construct;
         *  typical values:
         *   objects derived from Token - base elements in the construct
         *   objects derived from CPPElement/CPPElementEx - sub-constructs within the construct
         *  special values interpreted here:
         *   null - place-holder that indicates a result frame from the token parser needs to be inserted
         */
        $this->payload = $elements;
        for ($i = count($this->payload) - 1;$i >= 0;--$i)
            if ( is_null($this->payload[$i]) )
                $this->payload[$i] = $parser->pop_element();
        // cache the construct class names
        foreach ($this->payload as $elem)
            $this->constructs[] = get_class($elem);
    }

    function __toString() {
        return $this->toString_alt();
    }
    private function toString_alt($depth = 0) {
        $result = str_repeat("\t",$depth) . '[' . get_class($this) . "]\n";
        foreach ($this->payload as $item) {
            if ( is_subclass_of($item,'Token') )
                $result .= str_repeat("\t",$depth+1) . $item->get_value() . "\n";
            else if ( is_subclass_of($item,'CPPElement') )
                $result .= $item->toString_alt($depth+1);
        }
        return $result;
    }

    /* functions for searching the parse tree */

    /* scans the payload of the element and matches it against a pattern; 'search' is a string pattern
       containing lists of class names (either derivations of CPPElement or derivations of Token)
       separated by whitespace; the following syntax may be used to specify relationships between the
       constructs:
         - CPPElement:payload   --match 'CPPElement' with 'payload' at next level
         - CPPElement-payload   --match 'CPPElement' with 'payload' at any sub-level
         - CPPElement["name"]   --match 'CPPElement.get_name()' with 'name'
         - Token["value"]       --match 'Token.get_value()' with 'value'
         - CPPElement:(payload-item payload-item)     --parentheses provide clarification
        Syntax for "name" and "value":
         - alphanumeric: if "name" and "value" contains only alphanumeric characters, then they are matched
         as described above with element node name's and token values
         - '~' prefix: if a '~' is used to prefix an alphanumeric name, then the name is looked up from a
         table; this table contains some default special symbols; see 'symbol_names' array in this class
         - '~' postfix: if a '~' is used to postfix an alphanumeric name, then a symbol is created in memory
         that maps to the name of the element at that point in the search string; any previous symbol of the 
         same name is overwritten; the symbol is placed into a table with the specified name, however the '~'
         now is prefix; e.g. "name~" is created as "~name" in the table
       the 'how' parameter may specify any of the following modifiers:
         - '!': element kinds in 'elementList' must exactly match the payload in a search element
         - '@': element kinds in 'elementList' can appear in any order at a sub-level (order doesn't matter)
         - '#': perform an initial recursive search (match element at any sub-level)
       return value: returns an array; items in the array correspond to the search parameter; for any index,
       the array element is either an object derived from CPPElement or Token; null is returned if the search
       was unsuccessful */
    function match_elements($search,$how = '') {
        // parse the 'how' parameter
        if (preg_match("/([!|@|#]*)/",$how,$matches) === false)
            throw new Exception("CPPElement::match_elements(): bad value for parameter 'how'");
        return $this->match_elements_recursive($search,strpos($matches[1],"!") === false,
                                               strpos($matches[1],"@") === false,strpos($matches[1],"#") !== false);
    }
    static private $symbol_names = array(
        "oparen" => "(",
        "cparen" => ")",
        "ptrop" => "*",
        "addrofop" => "&"
    );
    static public function map_search_name($name) {
        // utility function to map search name to appropriate
        // form if necessary
        foreach (self::$symbol_names as $key => $value)
            if ($value == $name)
                return "~$key";
        return $name;
    }
    private function match_elements_recursive($search,$partial,$inorder,$recurse) {
        $info = new stdClass;
        $info->keys = array(); // construct key names (e.g. 'CPPAdditiveExpression')
        $info->values = array(); // construct value names (e.g. 'foo')
        $info->searchFlags = array(); // flags: true if search for any sub-level
        $info->recursiveSearches = array(); // search string for lower-levels (substrings of '$search')
        // find current level construct key-value pairs; also find next-frame (recursive) search strings
        while ($search != '') {
            if (preg_match("/([[:alpha:]]+)(?:\[([~_[:alnum:]]+)\])?(?:(:|-| )(.*))?$/",$search,$matches) == 0)
                throw new Exception("CPPElement::match_elements(): bad value for parameter 'search'");
            // add next key-value pair
            $info->keys[] = $matches[1];
            if (!isset($matches[2]) || $matches[2]=='')
                $info->values[] = null;
            else {
                if ($matches[2][0] == '~') { // lookup name in table
                    $matches[2] = substr($matches[2],1);
                    if ( !array_key_exists($matches[2],self::$symbol_names) )
                        throw new Exception("CPPElement::match_elements(): symbol '{$matches[2]}' does not exist");
                    $info->values[] = self::$symbol_names[$matches[2]];
                }
                else
                    $info->values[] = $matches[2];
            }
            if ( isset($matches[4]) ) {
                if ($matches[3] != ' ') {
                    // find the recursive search string
                    $iter = 0; $length = strlen($matches[4]);
                    while ($iter < $length) {
                        if ( ctype_space($matches[4][$iter]) )
                            break;
                        if ($matches[4][$iter] == '(') {
                            $n = 0;
                            do {
                                if ($matches[4][$iter] == ')')
                                    --$n;
                                else if ($matches[4][$iter] == '(')
                                    ++$n;
                                ++$iter;
                            } while ($iter<$length && $n>=1);
                            if ($n != 0)
                                throw new Exception("CPPElement::match_elements(): mismatched parentheses in search pattern");
                        }
                        else
                            ++$iter;
                    }
                    $info->searchFlags[] = $matches[3] == '-';
                    // add next recursive frame search string
                    if ($length>=1 && $matches[4][0]=='(') { // remove parentheses
                        $info->recursiveSearches[] = substr($matches[4],1,$iter-2);
                        ++$iter; // move past close paren
                    }
                    else
                        $info->recursiveSearches[] = substr($matches[4],0,$iter);
                    // search through the rest of the original search string
                    $search = substr($matches[4],$iter);
                }
                else {
                    $info->searchFlags[] = null;
                    $info->recursiveSearches[] = null;
                    $search = $matches[4];
                }
            }
            else {
                $info->searchFlags[] = null;
                $info->recursiveSearches[] = null;
                $search = '';
            }
        }
        $info->inorder = $inorder;
        $info->partial = $partial;
        // find results given specified search parameters
        $results = $this->match_elements_base($info);
        if ( is_null($results) ) {
            if ($recurse) { // try to match at any sub-tree level
                $results = $this->match_elements_base_recursive($info);
                if ( is_null($results) )
                    return null;
            }
            else
                return null;
        }
        return $results;
    }
    private function match_elements_base_recursive($info) {
        /* this is really nasty, but I will search through this entire subtree for the current search string; this
           works by calling 'match_elements_base' for each payload item until a result is found or not found */
        foreach ($this->payload as $item) {
            if ( is_subclass_of($item,'CPPElement') ) { // only call on non-terminal constructs
                $results = $item->match_elements_base($info);
                if ( !is_null($results) )
                    return $results;
                $results = $item->match_elements_base_recursive($info);
                if ( !is_null($results) )
                    return $results;
            }
        }
        return null;
    }
    private function match_elements_base($info) {
        // assert: count($info->keys) == count($info->values) == count($info->recursiveSearches) == count(RETURN-VALUE)

        // check to make sure that sub-constructs exist for this construct
        if ( is_null($this->constructs) )
            return null;

        // check search partiality
        if (!$info->partial && count($info->keys)!=count($this->constructs))
            return null;

        // find all instances of the specified keys in construct array; if none
        // exists for a particular key, fail and return null; map the key to the
        // index in the construct array which is parallel to '$this->payload'
        $findings = array();
        foreach ($info->keys as $k) {
            // Note: I expect array_keys to maintain the ordering of the items it gets back
            $arr = array_keys($this->constructs,$k);

            if (count($arr) == 0)
                return null;
            $findings[] = $arr;
        }

        // test all possible combinations of the findings; if a combination passes all
        // of its recursive searches, it wins the prize and gets returned
        $combo = array_fill(0,count($findings),0);
        while (true) {
            if (!$info->inorder || self::check_inorder_helper($combo,$info->partial)) {
                $matches = true;
                for ($i = 0;$i < count($info->values);++$i) {
                    if (is_string($info->values[$i]) && $info->values[$i]!="") { // values are optional
                        $dex = $findings[$i][$combo[$i]];
                        $value = $info->values[$i]; $pos = strlen($value)-1;
                        if ($value[$pos] == '~') {
                            // create entry in name symbol table
                            $name = substr($value,0,$pos);
                            if ( is_subclass_of($this->payload[$dex],'Token') )
                                self::$symbol_names["$name"] = $this->payload[$dex]->get_value();
                            else if ( is_subclass_of($this->payload[$dex],'CPPElement') )
                                self::$symbol_names["$name"] = $this->payload[$dex]->get_name();
                        }
                        else {
                            // match name with node name or token value
                            if ( is_subclass_of($this->payload[$dex],'Token') ) {
                                if ($this->payload[$dex]->get_value() != $value) {
                                    $matches = false;
                                    break;
                                }
                            }
                            else if ( is_subclass_of($this->payload[$dex],'CPPElement') ) {
                                if ($this->payload[$dex]->get_name() != $value) {
                                    $matches = false;
                                    break;
                                }
                            }
                        }
                    }
                }
                if ($matches) { // did match at current level
                    // assert: count($results) == count($combo) == count($info->recursiveSearches)
                    $results = array_map(array($this,'map_payload'),array_map(function($a,$b){return $a[$b];},$findings,$combo));
                    $length = count($results);
                    for ($i = 0;$i < $length;++$i) {
                        if ( !is_null($info->recursiveSearches[$i]) ) { // recursive searches are optional
                            $iResults = $results[$i]->match_elements_recursive($info->recursiveSearches[$i],$info->partial,
                                                                               $info->inorder,$info->searchFlags[$i]);
                            if ( is_null($iResults) ) {
                                $matches = false;
                                break;
                            }
                            $results = array_merge($results,$iResults);
                        }
                    }
                    if ($matches) // did match at all recursive levels
                        return $results;
                }
            }

            // generate the next combination
            $l = count($combo)-1;
            while ($l >= 0) {
                if ($combo[$l]+1 >= count($findings[$l])) {
                    $combo[$l] = 0;
                    --$l;
                }
                else {
                    ++$combo[$l];
                    break;
                }
            }

            if ($l < 0)
                break;
        }

        // none of the combinations matched the search pattern
        return null;
    }
    static private function check_inorder_helper($a,$partial) {
        $length = count($a);
        for ($i = 0;$i+1 < $length;++$i) {
            if ($partial) {
                if ($a[$i+1] < $a[$i])
                    return false;
            }
            else if ($a[$i+1] - $a[$i] != 1)
                return false;
        }
        return true;
    }
    private function map_payload($index) {
        return $this->payload[$index];
    }

    /* finds the first element of the specified kind; 'find_next' will
       continue the search from that point; NOTE: you must call 'find_next'
       on the object returned from this function, not on the original object;
       null is returned should the search fail */
    function find_first($elementKind) {
        $this->find_elem = null;
        return $this->find_first_recursive($elementKind);
    }

    private function find_first_recursive($elementKind,$reset = true) {
        if ( !isset($this->find_elem) ) // only set to null if not set; this allows 'find_next' to work
            $this->find_elem = null; // store parent node
        if ($reset) {
            $this->find_iter = 0; // store current position in this node's payload
            $this->find_rec = false; // flag whether or not to make a recursive call at the current payload position
        }
        // perform a depth-first in-order traversal
        // search for matches at the current level
        for (;$this->find_iter < count($this->payload);++$this->find_iter) {
            // check element at current level
            if (!$this->find_rec && $this->constructs[$this->find_iter] == $elementKind) {
                $e = $this->payload[$this->find_iter];
                $this->find_rec = true; // flag to hit the recursive case if/when 'find_next' is called
                $e->find_elem = $this;
                return $e;
            }
            // check down a level (recursive case) if the element wasn't found at the current position; this performs the depth-first
            // parse tree traversal
            else if ( is_subclass_of($this->payload[$this->find_iter],'CPPElement') ) {
                $e = $this->payload[$this->find_iter];
                $this->find_rec = false; // need to reset this flag!
                $result = $e->find_first_recursive($elementKind);
                if ( !is_null($result) ) {
                    ++$this->find_iter; // increment 'find_iter' so that next element is seen when/if 'find_next' is called
                    $e->find_elem = $this;
                    return $result;
                }
            }
            // need to reset this flag!
            $this->find_rec = false;
        }
        // not found
        return null;
    }

    /* find the next element of the specified kind; this search takes over from
       where 'find_first' left off; returns NULL when at end of the parse tree */
    function find_next($elementKind) {
        if ( !is_null($this->find_elem) ) {
            $elem = $this->find_elem;
            $result = $elem->find_first_recursive($elementKind,false);
            if ( !is_null($result) )
                return $result;
            return $elem->find_next($elementKind);
        }
        // not found
        return null;
    }

    function get_payload_length() {
        return count($this->payload);
    }
    function get_payload_element($index) {
        if ($index>=0 && $index<count($this->payload))
            return $this->payload[$index];
        return null;
    }
    function get_payload_element_class($index) {
        if ($index>=0 && $index<count($this->payload))
            return get_class($this->payload[$index]);
        return null;
    }
    function match_payload_element($index,$kind,$value = null) {
        if ($index < count($this->payload)) {
            $elem = $this->payload[$index];
            if (get_class($elem) == $kind)
                return is_null($value) || (is_subclass_of($elem,"CPPElement") && $elem->get_name()==$value)
                    || (is_subclass_of($elem,"Token") && $elem->get_value()==$value) || false;
        }
        return false;
    }
    function find_payload_element($kind,&$start = 0) {
        for (;$start < count($this->payload);++$start)
            if (get_class($this->payload[$start]) == $kind)
                return $this->payload[$start];
        return null;
    }
}

/* represents an element in the source code for which it's
   useful to remember a line number but perhaps not a name */
abstract class CPPElementEx extends CPPElement {
    private $lineNo = 0; // the line that contains the beginning of the element

    function get_line_no() { // CPPElement::get_line_no
        return $this->lineNo;
    }
    function set_line_no($lineNo) {
        $this->lineNo = $lineNo;
    }
    function get_name() { // CPPElement::get_name (some derived classes may override this if need be)
        return ''; // no name
    }
}

// The following classes implement the CPPElement interface, either directly or through CPPElementEx;
// any class that inherits from CPPElement directly is said to be complete as it represents a top-level
// language construct; classes that instead inherit from CPPElementEx are incomplete as they serve to
// help represent the constructs that make up higher level constructs

// Begin 'incomplete' C++ element constructs:

abstract class CPPExpressionBase extends CPPElementEx {
    /* payload results: varies depending on subclass kind; 'parent::payload' will contain all the objects
       that make up the expression */

    static protected function try_parse_generic(TokenParser $parser,array $operators,$loperand,$roperand) {
        //if ( $roperand::try_parse($parser) ) { // supported by PHP 5.3 and above
        if ( call_user_func(array($roperand,'try_parse'),$parser) ) { // try to parse the expected right-operand
            $matched = false;

            // see if the next token is one of the desired operators
            while ( $parser->does_token_match_ex('OperatorPunctuatorToken',$operators) ) {
                $parser->add_token(); // add operator to calling result context
                $parser->begin_working_result($loperand); // let the recursive call build on a new result
                //if ( !$roperand::try_parse($parser) ) // supported by PHP 5.3 and above
                if ( !call_user_func(array($roperand,'try_parse'),$parser) )
                    return $parser->discard_working_result();
                $parser->merge_working_result(); // merge the result with the previous
                $matched = true;
            }

            // if one of the operators was found, the result needs
            // to be committed as the left-operand type
            if ($matched)
                self::expression_type_commit($parser,$loperand);
            return true;
        }

        return false;
    }

    // commit current frame as specified expression type; the original working frame
    // context is preserved in a new frame and a placeholder is placed into that frame
    static protected function expression_type_commit(TokenParser $parser,$commitAs) {
        $contxt = $parser->get_working_result()->context;
        $parser->commit_working_result_as($commitAs);
        // maintain the last working frame for the calling context
        $parser->begin_working_result($contxt);
        $parser->add_place_holder(); // insert placeholder for the last committed frame
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}

/* represents an expression, which is a list of assignment-expressions */
class CPPExpression extends CPPExpressionBase {
    static function try_parse(TokenParser $parser) {
        $parser->begin_working_result( get_class() ); // begin original expression frame
        $cnt = 0;

        // read expression; expressions may repeat if separated by a comma
        while (true) {
            $parser->begin_working_result( get_class() ); // let the assignment expression work on this result
            if ( !CPPAssignmentExpression::try_parse($parser) ) {
                $parser->discard_working_result();
                break;
            }
            $parser->merge_working_result(); // merge left-over results with original frame

            ++$cnt;
            if ( !$parser->does_token_match('OperatorPunctuatorToken',',') )
                break;
            $parser->add_token();
        }

        // we want to logically eliminate CPPExpression frames
        // that have only 1 place-holder; they simplify to the
        // next committed frame
        if (!$parser->is_working_result_error() && ($cnt>1 || $parser->is_working_result_nonempty())) {
            // frame contains either more than 1 placeholder and/or at least 1 non-placeholder
            $parser->commit_working_result();
            return true;
        }

        if ($cnt >= 1) {
            $parser->discard_working_result(null); // simplify to the next committed frame
            return true;
        }
        return $parser->discard_working_result();
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}

/* the following classes represent expression sequences that can be recursively parsed;
   they expect the caller to setup a working context */
class CPPAssignmentExpression extends CPPExpressionBase {
    static function try_parse(TokenParser $parser) {
        // attempt conditional-expression
        if ( CPPConditionalExpression::try_parse($parser) ) {
            // expect an assignment operator if the result wasn't a direct conditional-expression
            if ($parser->get_last_result_context()!='CPPConditionalExpression' && $parser->does_token_match_ex('OperatorPunctuatorToken',CPlusPlus::$assignops)) {
                $parser->add_token();
                $parser->begin_working_result(get_class());
                if ( !self::try_parse($parser) ) // recursive call
                    return $parser->discard_working_result();
                $parser->merge_working_result();
                parent::expression_type_commit($parser,get_class());
            }

            return true;
        }

        return false;
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}
class CPPConditionalExpression extends CPPExpressionBase {
    /* attempt to parse a conditional-expression; use the result frame of the caller unless
       the condition operator sequence (? :) was found */
    static function try_parse(TokenParser $parser) {
        // parser operand as logical-or expression
        if ( CPPLogicOR::try_parse($parser) ) {
            // test for conditional operator sequence
            if ( $parser->does_token_match('OperatorPunctuatorToken','?') ) {
                $parser->add_token(); // add operator '?' to result
                if ( !CPPExpression::try_parse($parser) || !$parser->does_token_match('OperatorPunctuatorToken',':') )
                    return false; // error: expect expression followed by ':' operator
                $parser->add_place_holder();
                $parser->add_token(); // add operator ':' to result
                $parser->begin_working_result('CPPConditionalExpression'); // have to create result frame for expression
                if ( !CPPAssignmentExpression::try_parse($parser) )
                    return false;
                $parser->merge_working_result();

                // commit this result as a conditional expression
                parent::expression_type_commit($parser,get_class());
            }
            return true;
        }
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}

class CPPLogicOR extends CPPExpressionBase {
    static function try_parse(TokenParser $parser) {
        return parent::try_parse_generic($parser,array('||','or'),get_class(),'CPPLogicAND');
    }

    function __construct (array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}
class CPPLogicAND extends CPPExpressionBase {
    static function try_parse(TokenParser $parser) {
        return parent::try_parse_generic($parser,array('&&','and'),get_class(),'CPPBitOR');
    }

    function __construct (array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}
class CPPBitOR extends CPPExpressionBase {
    static function try_parse(TokenParser $parser) {
        return parent::try_parse_generic($parser,array('|'),get_class(),'CPPBitXOR');
    }

    function __construct (array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}
class CPPBitXOR extends CPPExpressionBase {
    static function try_parse(TokenParser $parser) {
        return parent::try_parse_generic($parser,array('^'),get_class(),'CPPBitAND');
    }

    function __construct (array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}
class CPPBitAND extends CPPExpressionBase {
    static function try_parse(TokenParser $parser) {
        return parent::try_parse_generic($parser,array('&'),get_class(),'CPPEquality');
    }

    function __construct (array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}
class CPPEquality extends CPPExpressionBase {
    static function try_parse(TokenParser $parser) {
        return parent::try_parse_generic($parser,array('==','!='),get_class(),'CPPRelational');
    }

    function __construct (array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}
class CPPRelational extends CPPExpressionBase {
    static function try_parse(TokenParser $parser) {
        return parent::try_parse_generic($parser,array('<','>','<=','>='),get_class(),'CPPShift');
    }

    function __construct (array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}
class CPPShift extends CPPExpressionBase {
    static function try_parse(TokenParser $parser) {
        return parent::try_parse_generic($parser,array('<<','>>'),get_class(),'CPPAdditive');
    }

    function __construct (array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}
class CPPAdditive extends CPPExpressionBase {
    static function try_parse(TokenParser $parser) {
        return parent::try_parse_generic($parser,array('+','-'),get_class(),'CPPMultiplicative');
    }

    function __construct (array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}
class CPPMultiplicative extends CPPExpressionBase {
    static function try_parse(TokenParser $parser) {
        return parent::try_parse_generic($parser,array('*','/','%'),get_class(),'CPPPtrMember');
    }

    function __construct (array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}
class CPPPtrMember extends CPPExpressionBase {
    static function try_parse(TokenParser $parser) {
        return parent::try_parse_generic($parser,array('.*','->*'),get_class(),'CPPCastExpression');
    }

    function __construct (array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}
class CPPCastExpression extends CPPExpressionBase {
    static function try_parse(TokenParser $parser) {
        $matched = false;

        // attempt to read ( type-id )
        // we must look ahead to make sure that this is indeed a Cast Expression
        while ($parser->does_token_match('OperatorPunctuatorToken','(') && self::is_type_spec_token($parser,true)) {
            $parser->add_token();

            while (self::is_type_spec_token($parser) || self::is_type_pops_token($parser))
                $parser->add_token();

            if (!$parser->does_token_match('OperatorPunctuatorToken',')'))
                return false;

            $parser->add_token();
            $matched = true;
        }

        if ( !CPPUnaryExpression::try_parse($parser) )
            return false;

        if ($matched)
            parent::expression_type_commit($parser,get_class());
        return true;
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }

    // generally determine if the current token could be part of a 'type-id'
    private static function is_type_spec_token(TokenParser $parser,$lookahead = false) { // type specifiers
        $tok = $parser->get_current_token($lookahead);

        return TokenParser::does_token_match_nm_ex($tok,'KeywordToken',CPlusPlus::$simple_type_specifier)
            || TokenParser::does_token_match_nm_ex($tok,'KeywordToken',CPlusPlus::$cv_qualifier)
            || TokenParser::does_token_match_nm_ex($tok,'IdentifierToken',CPlusPlus::$user_defined_type_specifier);
    }
    private static function is_type_pops_token(TokenParser $parser) { // ptr-operators
        // TODO: handle nested name specifiers
        return $parser->does_token_match('OperatorPunctuatorToken','*')
            || $parser->does_token_match('OperatorPunctuatorToken','&');
    }
}
class CPPUnaryExpression extends CPPExpressionBase {
    static function try_parse(TokenParser $parser) {
        // handle all unary expressions except 'new' and 'delete'
        if ( $parser->does_token_match_ex('OperatorPunctuatorToken',CPlusPlus::$unaryops) ) {
            $parser->add_token(); // add operator to result

            if ( !CPPCastExpression::try_parse($parser) )
                return false;
            parent::expression_type_commit($parser,get_class());
        }
        else if ( $parser->does_token_match('KeywordToken','sizeof') ) {
            // 'sizeof' grammar: sizeof unary-expression OR sizeof ( type-id )
            $parser->add_token();

            if ( false ) // attempt to read ( type-id )
                ;
            else if ( !self::try_parse($parser) ) // attempt to read unary-expression
                return false;

            parent::expression_type_commit($parser,get_class());
        }
        else if ( !CPPPostfixExpression::try_parse($parser) )
            return false;

        return true;
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}
class CPPPostfixExpression extends CPPExpressionBase {
    static function try_parse(TokenParser $parser) {
        // base case
        if ( !self::read_primary_expression($parser) ) {
            if ( true ) /* TODO: handle C++-style casts */
                return false;
        }

        $exprcommit = false; // if true, then the result should be committed as a CPPPostfixExpression
        while (true) {
            if ( $parser->does_token_match_ex('OperatorPunctuatorToken',CPlusPlus::$postfixops) )
                $parser->add_token();
            else if ( $parser->does_token_match('OperatorPunctuatorToken','(') ) {
                $parser->add_token(); // keep operator in stream for later analysis
                // attempt to parse optional expression
                if ( CPPExpression::try_parse($parser) )
                    $parser->add_place_holder();
                if ( !$parser->does_token_match('OperatorPunctuatorToken',')') )
                    return false;
                $parser->add_token();
            }
            else if ( $parser->does_token_match('OperatorPunctuatorToken','[') ) {
                $parser->add_token(); // keep operator in stream for later analysis
                // attempt to parse required expression
                if ( !CPPExpression::try_parse($parser) )
                    return false;
                if ( !$parser->does_token_match('OperatorPunctuatorToken',']') )
                    return false;
                $parser->add_place_holder(); // add place-holder for square-bracket expression
                $parser->add_token();
            }
            else
                break;
            $exprcommit = true;
        }

        if ($exprcommit)
            parent::expression_type_commit($parser,get_class());

        return true;
    }

    /* reads a primary expression */
    static private function read_primary_expression(TokenParser $parser) {
        $type = $parser->get_current_token_type();
        if ($type=='StringLiteralToken' || $type=='CharacterLiteralToken' || $type=='NumericLiteralToken') // check literals
            $parser->add_token();
        else if ( $parser->does_token_match_ex('KeywordToken',CPlusPlus::$booleanLiteral) ) // check boolean literal
            $parser->add_token();
        else if ( $parser->does_token_match('KeywordToken','this') ) // check 'this'
            $parser->add_token();
        else if ( $parser->does_token_match('OperatorPunctuatorToken','::') ) { // check globally-qualified primary expressions
            $parser->add_token();
            if ($parser->get_current_token_value() == 'IdentifierToken')
                $parser->add_token();
            else if (!self::read_operator_function_id($parser) && !self::read_qualified_id($parser))
                return false;
        }
        else if ( $parser->does_token_match('OperatorPunctuatorToken','(') ) { // check parenthesized expression
            $parser->add_token();
            if ( !CPPExpression::try_parse($parser) )
                return false;
            $parser->add_place_holder(); // add place-holder for parenthesized expression
            if ( !$parser->does_token_match('OperatorPunctuatorToken',')') )
                return false;
            $parser->add_token();
        }
        else if (!self::read_qualified_id($parser)/*check this first*/ && !self::read_unqualified_id($parser)) // check id-expression
            return false;
        return true;
    }

    static private function read_operator_function_id(TokenParser $parser) {
        if ( $parser->does_token_match('KeywordToken','operator') ) {
            $parser->add_token();
            if ( !$parser->does_token_match_ex('OperatorPunctuator',CPlusPlus::$overloadableops) )
                return false;
            $tok = $parser->get_current_token();
            $parser->add_token();
            // handle 'new[]', 'delete[]', '[]', and '()'
            if (($tok->get_value()=='new' || $tok->get_value()=='delete') && $parser->does_token_match('OperatorPunctuatorToken','[')) {
                $parser->add_token();
                if ( !$parser->does_token_match('OperatorPunctuatorToken',']') )
                    return false;
                $parser->add_token();
            }
            else if ($tok->get_value() == '(') {
                if ( !$parser->does_token_match('OperatorPunctuatorToken',')') )
                    return false;
                $parser->add_token();
            }
            else if ($tok->get_value() == '[') {
                if ( !$parser->does_token_match('OperatorPunctuatorToken',']') )
                    return false;
                $parser->add_token();
            }
            return true;
        }
        return false;
    }

    /* reads an unqualified-id */
    static private function read_unqualified_id(TokenParser $parser) {
        if ( $parser->get_current_token_type() == 'IdentifierToken')
            $parser->add_token();
        else if ( !self::read_operator_function_id($parser) )
            return false;
        return true;
    }

    /* reads a qualified-id */
    static private function read_qualified_id(TokenParser $parser) {
        return self::read_nested_name_recursive($parser) && self::read_unqualified_id($parser);
    }

    /* reads a nested-name-specifier */
    static private function read_nested_name_recursive(TokenParser $parser) {
        if ( $parser->does_token_match_ex('IdentifierToken',CPlusPlus::$user_defined_nested_name_specifier) ) {
            $parser->add_token();

            // see if the identifier is a qualifying identifier
            if ( !$parser->does_token_match('OperatorPunctuatorToken','::') )
                return false;
            $parser->add_token();

            // note: keyword 'template' may occur here followed by a non-optional recursive specifier

            // pattern already matches; try for optional recursive specifier
            self::read_nested_name_recursive($parser);
            return true;
        }

        return false;
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}

/* represents a sequence of declaration specifiers */
class CPPDeclSpecSeq extends CPPElementEx {
    /* payload results:
        KeywordToken:(type-spec|cv-qualifier|storage-class|function-spec|ungrouped-decl-spec) ...
        IdentifierToken:user-type-spec ... */

    /* adds results to 'parser' if valid declarations were found and then
       returns true, else does nothing to 'parser' and returns false */
    static function try_parse(TokenParser $parser) {
        $parser->begin_working_result( get_class() );

        // look through tokens in parser; look for type specifiers,
        // storage-class specifiers, and function-specifiers
        while (true) {
            if ( $parser->does_token_match_ex('KeywordToken',CPlusPlus::$simple_type_specifier)
                || $parser->does_token_match_ex('KeywordToken',CPlusPlus::$cv_qualifier)
                || $parser->does_token_match_ex('IdentifierToken',CPlusPlus::$user_defined_type_specifier)
                || $parser->does_token_match_ex('KeywordToken',CPlusPlus::$storage_class_specifier)
                || $parser->does_token_match_ex('KeywordToken',CPlusPlus::$function_specifier) 
                || $parser->does_token_match_ex('KeywordToken',CPlusPlus::$ungrouped_decl_specifier) )
                $parser->add_token();
            else
                break;
        }

        if ( $parser->is_working_result_nonempty() ) {
            $parser->commit_working_result();
            return true;
        }

        // this decl-specifier-seq is empty
        return $parser->discard_working_result();
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}

/* represents a parameter-declaration */
class CPPParameterDeclaration extends CPPElementEx {
    /* payload contents:
        CPPDeclSpecSeq [CPPAbstractDeclarator | CPPDeclarator] [OperatorPunctuator:'=' CPPAssignmentExpression] */

    static function try_parse(TokenParser $parser) {
        $parser->begin_working_result( get_class() );

        // a parameter declaration must at least have declaration specifiers (e.g. type names)
        if ( CPPDeclSpecSeq::try_parse($parser) ) {
            $parser->add_place_holder();
            
            // attempt to parse a declarator; this will try both CPPDeclarator
            // and CPPAbstractDeclarator
            if ( CPPDeclarator::try_parse($parser,true) )
                $parser->add_place_holder();

            // default argument expression
            if ( $parser->does_token_match('OperatorPunctuatorToken','=') ) {
                $parser->add_token();
                $parser->begin_working_result('CPPAssignmentExpression'); // create a result frame for assignment-expression
                if ( !CPPAssignmentExpression::try_parse($parser) )
                    return $parser->discard_working_result();
                if ( $parser->is_working_result_nonempty() ) {
                    $parser->commit_working_result(); // commit assignment-expression frame
                    $parser->add_place_holder(); 
                }
                else
                    // simplify the stack frame if the working result only contains placeholders
                    $parser->merge_working_result();
            }

            $parser->commit_working_result();
            return true;
        }

        return $parser->discard_working_result();
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}

/* represents a parameter-declaration-clause, which is a list
   of parameter-declarations */
class CPPParameterDeclarationClause extends CPPElementEx {
    /* payload contents:
        CPPParameterDeclaration ... */

    static function try_parse(TokenParser $parser) {
        $parser->begin_working_result( get_class() );

        while (true) {
            if ( !CPPParameterDeclaration::try_parse($parser) ) {
                // if a ')' is the next token, this means that this
                // is an empty parameter declaration; we need to be
                // successful in this case
                if ( $parser->does_token_match('OperatorPunctuatorToken',')') )
                    break;
                return $parser->discard_working_result();
            }
            $parser->add_place_holder(); 
            if ( !$parser->does_token_match('OperatorPunctuatorToken',',') )
                break;
            $parser->add_token();
        }

        $parser->commit_working_result();
        return true;
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}

/* represents an abstract-direct-declarator, which is a declarator that omits a
   name; compare with direct-declarator */
class CPPAbstractDirectDeclarator extends CPPElementEx {
    /* payload contents:
        OperatorPunctuatorToken:"[" CPPExpressionBase: OperatorPunctuatorToken:"]"
        OperatorPunctuatorToken:"(" CPPParameterDeclarationClause OperatorPunctuatorToken:")" [CPPKeywordToken:cv-qualifier ...] */

    static function try_parse(TokenParser $parser) {
        $parser->begin_working_result( get_class() );

        if ( self::parse_direct($parser) ) {
            $parser->commit_working_result();
            return true;
        }

        return $parser->discard_working_result();
    }

    /* parse elements in any direct-declarator */
    static protected function parse_direct(TokenParser $parser) {
        $found = false;

        while (true) {
            if ( $parser->does_token_match('OperatorPunctuatorToken','[') ) {
                $parser->add_token(); // keep operator
                $parser->begin_working_result('CPPExpression'); // create frame for expression parser
                if ( CPPConditionalExpression::try_parse($parser) ) { // the optional conditional-expression is grammatically equivilent to constant-expression
                    $parser->commit_working_result();
                    $parser->add_place_holder();
                }
                else
                    $parser->discard_working_result();
                if ( !$parser->does_token_match('OperatorPunctuatorToken',']') )
                    return false;
                $parser->add_token();
                $found = true;
            }
            else if ( $parser->does_token_match('OperatorPunctuatorToken','(') ) {
                $parser->add_token();
                if ( !CPPParameterDeclarationClause::try_parse($parser) ) {
                    // special ambiguous case: retreat to the '(' token
                    $parser->retreat();
                    return false;
                }
                $parser->add_place_holder();
                // expect optional qualifiers (included for completeness)
                while ( $parser->does_token_match_ex('KeywordToken',CPlusPlus::$cv_qualifier) )
                    $parser->add_token();
                if ( !$parser->does_token_match('OperatorPunctuatorToken',')') )
                    return false;
                $parser->add_token();
                $found = true;
            }
            else
                break;
        }

        return $found;
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}

/* represents a direct-declarator, which is a declarator that has a name (identifier);
   compare with abstract-direct-declarator */
class CPPDirectDeclarator extends CPPAbstractDirectDeclarator {
    /* payload contents:
        IdentifierToken
        IdentifierToken OperatorPunctuatorToken:"[" CPPExpressionBase: OperatorPunctuatorToken:"]"
        IdentifierToken OperatorPunctuatorToken:"(" CPPParameterDeclarationClause OperatorPunctuatorToken:")" [CPPKeywordToken:cv-qualifier ...] */

    static function try_parse(TokenParser $parser) {
        $parser->begin_working_result( get_class() );

        // expect the declarator-id first (to get a complete direct-declarator):
        if ($parser->get_current_token_type() == 'IdentifierToken') {
            $parser->add_token();

            // parse direct-declarator parts that might follow the declarator-id
            parent::parse_direct($parser);
     
            $parser->commit_working_result();
            return true;
        }

        return $parser->discard_working_result();
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }

    function get_name() {
        // return value of first identifier token
        return $this->find_payload_element("IdentifierToken")->get_value();
    }
}

/* represents an abstract-declarator, which is a declarator that omits its name 
   (identifier); compare with declarator */
class CPPAbstractDeclarator extends CPPElementEx {
    /* payload contents:
        OperatorPunctuatorToken:("*"|"&") ...
        OperatorPunctuatorToken:("*"|"&") ... CPPAbstractDirectDeclarator
        CPPAbstractDirectDeclarator */

    static function try_parse(TokenParser $parser) {
        $parser->begin_working_result( get_class() );

        // There must be at least one ptr-op or a direct-abstract-declarator
        // in order for this element to be correct.

        // anticipate ptr-ops:
        $bsuccess = self::parse_ptr_ops($parser);

        // anticipate abstract-direct-declarator: (optional unless no ptr-ops were found)
        if ( CPPAbstractDirectDeclarator::try_parse($parser) )
            $parser->add_place_holder();
        else if (!$bsuccess)
            return $parser->discard_working_result();

        $parser->commit_working_result();
        return true;
    }

    static protected function parse_ptr_ops(TokenParser $parser) {
        $bsuccess = false;

        // iterative implementation of: ptr-operator declarator
        while ($parser->get_current_token_type() == 'OperatorPunctuatorToken') {
            $value = $parser->get_current_token_value();

            if ($value == '*') {
                $parser->add_token();
                $bsuccess = true;
                // check for optional cv-qualifier sequence (they are keywords)
                while ( $parser->does_token_match_ex('KeywordToken',CPlusPlus::$cv_qualifier) )
                    $parser->add_token();
            }
            else if ($value == '&') {
                $parser->add_token();
                $bsuccess = true;
            }
            else
                break;
        }

        return $bsuccess;
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}

/* represents a declarator, which is a series of ptr-operators (optional)
   followed by a direct-declarator; this type of declarator includes a name;
   compare with abstract-declarator */
class CPPDeclarator extends CPPAbstractDeclarator {
    /* payload contents:
        OperatorPunctuatorToken:("*"|"&") CPPDirectDeclarator */

    static function try_parse(TokenParser $parser,$tryAbstract = false) {
        $parser->begin_working_result( get_class() );

        // anticipate optional ptr-operators that preceed the direct-declarator:
        $ptrops = parent::parse_ptr_ops($parser);

        // Since CPPDeclarator and CPPAbstractDeclarator are so similar, we let users
        // attempt to parse them both in one go; they either get CPPDeclarator or
        // CPPAbstractDeclarator as a result (assuming no syntax errors)
        if ($tryAbstract && $parser->get_current_token_type()!='IdentifierToken') {
            // try to parse an optional CPPAbstractDirectDeclarator
            if ( CPPAbstractDirectDeclarator::try_parse($parser) )
                $parser->add_place_holder();
            $parser->commit_working_result_as('CPPAbstractDeclarator');
            return true;
        }

        // anticipate direct-declarator: (base case; not optional)
        if ( !CPPDirectDeclarator::try_parse($parser) )
            return $parser->discard_working_result();

        $parser->add_place_holder();
        $parser->commit_working_result();
        return true;
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }

    function get_name() {
        // return direct declarator name
        return $this->find_payload_element("CPPDirectDeclarator")->get_name();
    }

    function is_variable() {
        $elem = $this->find_payload_element("CPPDirectDeclarator");
        // it is a variable declaration if an open '(' is not found after declarator
        return !$elem->match_payload_element(1,"OperatorPunctuatorToken","(");
    }

    function is_function() {
        $elem = $this->find_payload_element("CPPDirectDeclarator");
        // it is a function declaration if an open '(' is found after the declarator
        return $elem->match_payload_element(1,"OperatorPunctuatorToken","(");
    }
}

class CPPInitializerClause extends CPPElementEx {
    /* payload contents:
        OperatorPunctuatorToken:"{" (CPPInitializerClause [OperatorPunctuatorToken:"," ...]) OperatorPunctuatorToken:"}"
        CPPExpression: */

    static function try_parse(TokenParser $parser) {
        if ( $parser->does_token_match('OperatorPunctuatorToken','{') ) {
            $parser->begin_working_result( get_class() );
            do {
                $parser->add_token(); // keep brace for later syntax analysis
                if ( CPPInitializerClause::try_parse($parser) ) // recursive call
                    $parser->add_place_holder();
                else
                    return $parser->discard_working_result();
            } while ( $parser->does_token_match('OperatorPunctuatorToken',',') );
            if ( $parser->does_token_match('OperatorPunctuatorToken','}') ) {
                $parser->add_token();
                $parser->commit_working_result();
                return true;
            }
        }
        else {
            // a frame must be created on which the assignment-expression can work
            $parser->begin_working_result('CPPExpression');
            if ( CPPAssignmentExpression::try_parse($parser) ) {
                $parser->commit_working_result();
                return true;
            }
        }

        return $parser->discard_working_result();
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}

/* represents a C++ initializer construct used with an init-declarator */
class CPPInitializer extends CPPElementEx {
    /* payload contents:
        OperatorPunctuatorToken:"=" CPPInitializerClause
        OperatorPunctuatorToken:"(" CPPExpression OperatorPunctuatorToken:")" */

    static function try_parse(TokenParser $parser) {
        $parser->begin_working_result( get_class() );
        if ( $parser->does_token_match('OperatorPunctuatorToken','=') ) {
            $parser->add_token(); // include this operator; it's important
            if ( CPPInitializerClause::try_parse($parser) ) { // parse initializer statement
                $parser->add_place_holder();
                $parser->commit_working_result();
                return true;
            }
        }
        else if ( $parser->does_token_match('OperatorPunctuatorToken','(') ) {
            $parser->add_token();
            if ( CPPExpression::try_parse($parser) ) {
                if ( $parser->does_token_match('OperatorPunctuatorToken',')') ) {
                    $parser->add_token();
                    $parser->add_place_holder();
                    $parser->commit_working_result();
                    return true;
                }
            }
        }

        return $parser->discard_working_result();
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}

/* represents an init-declarator construct */
class CPPInitDeclarator extends CPPDeclarator {
    /* payload contents: (this construct resolves to CPPDeclarator if no initializer is specified)
        CPPDeclarator CPPInitializer */

    static function try_parse(TokenParser $parser, $tryAbstract = false) {
        $parser->begin_working_result( get_class() );

        // expect a declarator
        if ( CPPDeclarator::try_parse($parser) ) {
            $parser->add_place_holder(); 
            if ( CPPInitializer::try_parse($parser) ) { // attempt to parse optional initializer
                $parser->add_place_holder(); 
                $parser->commit_working_result(); // commit as CPPInitDeclarator
            }
            else
                $parser->discard_working_result(null); // commit only CPPDeclarator (essentially simplify to next committed frame)

            return !$parser->is_working_result_error();
        }

        return $parser->discard_working_result();
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }

    function get_name() {
        // name an init-declarator after its declarator
        return $this->find_payload_element("CPPDeclarator")->get_name();
    }

    function is_variable() {
        $elem = $this->find_payload_element("CPPDeclarator");
        return $elem->is_variable();
    }

    function is_function() {
        $elem = $this->find_payload_element("CPPDeclarator");
        return $elem->is_function();
    }
}

/* represents any generic statement; shouldn't be instantiated */
abstract class CPPStatement extends CPPElementEx {
    /* This construct will resolve to one of the following (assuming no errors):
        CPPSimpleDeclaration   CPPExpressionStatement  CPPCompoundStatement
        CPPSelectionStatement  CPPIterationStatement   CPPJumpStatement
        CPPLabeledStatement */

    static function try_parse(TokenParser $parser, $tryAbstract = false) {
        static $statementTypes = array('CPPSimpleDeclaration','CPPExpressionStatement','CPPCompoundStatement',
            'CPPSelectionStatement','CPPIterationStatement','CPPJumpStatement','CPPLabeledStatement');

        $parser->begin_working_result( get_class() ); // this working result is for errors
        $lastKind = '';
        foreach ($statementTypes as $kind) {
            $lastKind = $kind; // remember last kind
            //if ( $kind::try_parse($parser) ) { // supported by PHP 5.3 and above
            if ( call_user_func(array($kind,'try_parse'),$parser) ) {
                $parser->discard_working_result(); // discard error result
                return true;
            }
            else if ( $parser->is_working_result_error() ) {
                // error element 'successfully' parsed
                break;
            }
            $lastKind = '';
        }

        // seek to the next lexical token that could possibly be correctly parsed;
        // under normal operation this loop will quit without adding elements to 
        // the current working frame, thus letting this routine return false
        while ( !$parser->end_of_file() ) {
            if ($lastKind == 'CPPCompoundStatement') {
                // there was a malformed compound statement flagged as error;
                // seek to the end of the compound statement
                if ( $parser->does_token_match('OperatorPunctuatorToken','}') ) {
                    $parser->add_token();
                    break;
                }
            }
            else if ( $parser->does_token_match('OperatorPunctuatorToken',';') ) {
                // seek past terminating ';' token of malformed statement
                $parser->add_token();
                // we can try to find a better starting place by finding a token that predicts
                // the next possible valid statement (for keyword tokens we can rule out statements like 'else')
                static $predict = array("if","while","do","switch","goto","return","int","double","char","float","long","unsigned");
                while (!$parser->end_of_file() && $parser->get_current_token_type() == "KeywordToken"
                       && !in_array($parser->get_current_token()->get_value(),$predict))
                    $parser->add_token();
                break;
            }
            // check for either beginning or end of compound statement; it may
            // be needed by a calling or subsequent context so don't add it
            if ($parser->does_token_match('OperatorPunctuatorToken','{') ||
                $parser->does_token_match('OperatorPunctuatorToken','}') )
                break;
            $parser->add_token();
        }

        // will return true if the current working frame is non-empty
        return $parser->discard_working_result(true);
    }
}
class CPPExpressionStatement extends CPPStatement {
    /* payload contents:
        CPPExpression: OperatorPunctuatorToken:";" */

    static function try_parse(TokenParser $parser, $tryAbstract = false) {
        $parser->begin_working_result( get_class() );

        if ( CPPExpression::try_parse($parser) ) // the expression is optional (this allows for empty statements!)
            $parser->add_place_holder();
        if ( $parser->does_token_match('OperatorPunctuatorToken',';') ) {
            $parser->add_token();
            $parser->commit_working_result();
            return true;
        }

        return $parser->discard_working_result();
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}
class CPPCompoundStatement extends CPPStatement {
    /* payload contents:
        CPPStatement ...
     */

    static function try_parse(TokenParser $parser, $tryAbstract = false) {
        if ( $parser->does_token_match('OperatorPunctuatorToken','{') ) {
            $parser->begin_working_result( get_class() );
            $parser->add_token();

            // expect sequence of statements
            while (true) {
                if ( CPPStatement::try_parse($parser) )
                    $parser->add_place_holder();
                else if ( $parser->does_token_match('OperatorPunctuatorToken','}') )
                    break;
                else if ( $parser->end_of_file() )
                    return $parser->discard_working_result();
                // else we need to continue trying to read statements; the CPPStatement functionality
                // will return false if a frame wasn't committed; we only fail at CPPCompoundStatement
                // when we reach the end of input
            }

            $parser->add_token();
            $parser->commit_working_result();
            return true;
        }

        return false;
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}
class CPPSelectionStatement extends CPPStatement {
    /* payload contents:
        if ( CPPExpression ) CPPStatement: [else CPPStatement:]
        switch ( CPPExpression ) CPPStatement: */

    static function try_parse(TokenParser $parser, $tryAbstract = false) {
        if ($parser->get_current_token_type() == 'KeywordToken') {
            $failure = true;
            $parser->begin_working_result( get_class() );
            $value = $parser->get_current_token_value();

            if ($value == 'if') {
                $parser->add_token();
                if ( $parser->does_token_match('OperatorPunctuatorToken','(') ) {
                    $parser->add_token();
                    // treat the condition like an expression for now
                    if (CPPExpression::try_parse($parser) && $parser->does_token_match('OperatorPunctuatorToken',')')) {
                        $parser->add_place_holder(); 
                        $parser->add_token();
                        if ( CPPStatement::try_parse($parser) ) {
                            $parser->add_place_holder(); 
                            // handle optional else clause
                            if ( $parser->does_token_match('KeywordToken','else') ) {
                                $parser->add_token();
                                if ( CPPStatement::try_parse($parser) ) {
                                    $parser->add_place_holder();
                                    $failure = false;
                                }
                            }
                            else
                                $failure = false;
                        }
                    }
                }
            }
            else if ($value == 'switch') {
                $parser->add_token();
                if ( $parser->does_token_match('OperatorPunctuatorToken','(') ) {
                    $parser->add_token();
                    // treat the condition like an expression for now
                    if (CPPExpression::try_parse($parser) && $parser->does_token_match('OperatorPunctuatorToken',')')) {
                        $parser->add_place_holder();
                        $parser->add_token();
                        if ( CPPStatement::try_parse($parser) ) {
                            $parser->add_place_holder();
                            $failure = false;
                        }
                    }
                }
            }

            if ($failure)
                return $parser->discard_working_result();

            $parser->commit_working_result();
            return true;
        }

        return false;
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}
class CPPIterationStatement extends CPPStatement {
    /* payload contents:
        "for" "(" simple-declaration|expression-statement [expression] ";" [expression] ")" statement
        "do" statement "while" "(" expression ")" ";"
        "while" "(" expression ") statement */

    static function try_parse(TokenParser $parser, $tryAbstract = false) {
        if ($parser->get_current_token_type() == 'KeywordToken') {
            $parser->begin_working_result( get_class() );
            $value = $parser->get_current_token_value();
            $failure = true;

            if ($value == 'for') {
                $parser->add_token();

                if ( $parser->does_token_match('OperatorPunctuatorToken','(') ) {
                    $parser->add_token();
                    if (CPPSimpleDeclaration::try_parse($parser) || CPPExpressionStatement::try_parse($parser)) {
                        $parser->add_place_holder(); 
                        if ( CPPExpression::try_parse($parser) ) // try to parse optional condition (treat it like an expression)
                            $parser->add_place_holder(); 
                        if ( $parser->does_token_match('OperatorPunctuatorToken',';') ) {
                            $parser->add_token();
                            if ( CPPExpression::try_parse($parser) ) // try to parse optional expression
                                $parser->add_place_holder(); 
                            if ( $parser->does_token_match('OperatorPunctuatorToken',')') ) {
                                $parser->add_token();
                                if ( CPPStatement::try_parse($parser) ) {
                                    $parser->add_place_holder(); 
                                    $failure = false;
                                }
                            }
                        }
                    }
                }
            }
            else if ($value == 'while') {
                $parser->add_token();

                if ( $parser->does_token_match('OperatorPunctuatorToken','(') ) {
                    $parser->add_token();
                    // treat the condition like an expression for now
                    if ( CPPExpression::try_parse($parser) ) {
                        $parser->add_place_holder(); 
                        if ( $parser->does_token_match('OperatorPunctuatorToken',')') ) {
                            $parser->add_token();
                            if ( CPPStatement::try_parse($parser) ) {
                                $parser->add_place_holder(); 
                                $failure = false;
                            }
                        }
                    }
                }
            }
            else if ($value == 'do') {
                $parser->add_token();

                if ( CPPStatement::try_parse($parser) ) {
                    $parser->add_place_holder(); 
                    if ( $parser->does_token_match('KeywordToken','while') ) {
                        $parser->add_token();
                        if ( $parser->does_token_match('OperatorPunctuatorToken','(') ) {
                            $parser->add_token();
                            if ( CPPExpression::try_parse($parser) ) {
                                $parser->add_place_holder();
                                if ( $parser->does_token_match('OperatorPunctuatorToken',')') ) {
                                    $parser->add_token();
                                    if ( $parser->does_token_match('OperatorPunctuatorToken',';') ) {
                                        $parser->add_token();
                                        $failure = false;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if ($failure)
                return $parser->discard_working_result();
 
            $parser->commit_working_result();
            return true;
        }

        return false;
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}
class CPPJumpStatement extends CPPStatement {
    /* payload contents:
        KeywordToken:"continue" OperatorPunctuatorToken:";"
        KeywordToken:"break" OperatorPunctuatorToken:";"
        KeywordToken:"return" CPPExpression: OperatorPunctuatorToken:";" */

    static function try_parse(TokenParser $parser, $tryAbstract = false) {
        if ($parser->get_current_token_type() == 'KeywordToken') {
            $parser->begin_working_result( get_class() );
            $value = $parser->get_current_token_value();
            $failure = true;

            if ($value=='break' || $value=='continue') {
                $parser->add_token();
                $failure = false;
            }
            else if ($value == 'return') {
                $parser->add_token();
                if ( CPPExpression::try_parse($parser) )
                    $parser->add_place_holder();
                $failure = false;
            }
            // TODO: handle 'goto' statement

            if ($failure || !$parser->does_token_match('OperatorPunctuatorToken',';'))
                return $parser->discard_working_result();

            $parser->add_token();
            $parser->commit_working_result();
            return true;
        }

        return false;
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}
class CPPLabeledStatement extends CPPStatement {
    /* payload contents:
        KeywordToken:"case" CPPExpression: OperatorPunctuatorToken:":"
        KeywordToken:"default" OperatorPunctuatorToken:":" */

    static function try_parse(TokenParser $parser, $tryAbstract = false) {
        $parser->begin_working_result( get_class() );

        if ( $parser->does_token_match('KeywordToken','case') ) {
            $parser->add_token();

            // try to parse assignment expression; create a working
            // frame for the assignment expression
            $parser->begin_working_result('CPPAssignmentExpression');
            if ( CPPAssignmentExpression::try_parse($parser) ) {
                if ($parser->get_working_result_size() <= 1)
                    $parser->merge_working_result();
                else {
                    $parser->commit_working_result();
                    $parser->add_place_holder();
                }
                if ( $parser->does_token_match('OperatorPunctuatorToken',':') ) {
                    $parser->add_token();
                    if ( CPPStatement::try_parse($parser) ) {
                        $parser->add_place_holder();
                        $parser->commit_working_result();
                        return true;
                    }
                }
            }
        }
        else if ( $parser->does_token_match('KeywordToken','default') ) {
            $parser->add_token();

            if ( $parser->does_token_match('OperatorPunctuatorToken',':') ) {
                $parser->add_token();
                if ( CPPStatement::try_parse($parser) ) {
                    $parser->add_place_holder();
                    $parser->commit_working_result();
                    return true;
                }
            }
        }

        return $parser->discard_working_result();
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }
}

// Begin 'complete' C++ element constructs:

/* represents the simple-declaration C++ construct; due to its similarity to 
   CPPFunctionDefinition, this construct requires checks */
class CPPSimpleDeclaration extends CPPElement {
    /* payload contents:
        CPPDeclSpecSeq (CPPInitDeclarator|CPPDeclarator [OperatorPunctuator:","] ...) OperatorPunctuator:";" */

    /* try to parse a simple-declaration construct; assume the caller
       has created a working result frame on which to operate; if successful,
       the frame will be committed as a 'CPPSimpleDeclration' */
    static function try_parse(TokenParser $parser,$makeFrame = true) {
        $checkA = null; // check working context for decl-spec-seq
        $checkB = null; // check working context for init-declarator (assume only one)
        $checkC = null; // check working context for anything else past checkB

        if ($makeFrame)
            $parser->begin_working_result( get_class() );
        else
            // we must check any parse frames in 'parser' that failed to complete a previous construct
            list($checkA,$checkB,$checkC) = $parser->match_working_contexts('CPPDeclSpecSeq','CPPInitDeclarator','dummy');

        // anticipate a decl-specifier-seq:
        if (is_null($checkC) && $checkA!==false && ($checkA===true || CPPDeclSpecSeq::try_parse($parser))) {
            if ( is_null($checkA) )
                // just parsed CPPDeclSpecSeq; add a place holder for the construct on the working result
                $parser->add_place_holder();

            // anticipate a list of init-declarators; they are separated by comma separators:
            $found = $checkB===true;
            if ( is_null($checkB) ) {
                while (true) {
                    if ( CPPInitDeclarator::try_parse($parser) ) {
                        $parser->add_place_holder();
                        $found = true;
                        if ( $parser->does_token_match('OperatorPunctuatorToken',',') )
                            $parser->add_token();
                        else
                            break;
                    }
                    else {
                        if ( $parser->is_working_result_error() )
                            $found = false; // previous declarators in the list may have been successful
                        break;
                    }
                }
            }

            // the only required part of a simple-declaration is the statement terminator: the semi-colon (;)
            if ($found && $parser->does_token_match('OperatorPunctuatorToken',';')) {
                $parser->add_token();
                $parser->commit_working_result_as( get_class() );
                return true;
            }
        }

        if ($makeFrame)
            return $parser->discard_working_result();
        return false;
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }

    function get_name() { // CPPElement::get_name
        // use the declarator ids separated by whitespace
        $result = "";
        for ($i = 0;$i < $this->get_payload_length();++$i) {
            $elem = $this->get_payload_element($i);
            if (get_class($elem)=="CPPInitDeclarator" || get_class($elem)=="CPPDeclarator") {
                if ($result != "")
                    $result .= " ";
                $result .= $elem->get_name();
            }
        }
        return $result;
    }
    function get_variable_names() {
        // get an array containing variable names declared in the simple-declaration
        $arr = array();
        for ($i = 0;$i < $this->get_payload_length();++$i) {
            $elem = $this->get_payload_element($i);
            if ((get_class($elem)=="CPPInitDeclarator" || get_class($elem)=="CPPDeclarator") && $elem->is_variable()) {
                $arr[] = $elem->get_name();
            }
        }
        return $arr;
    }

    function get_function_names() {
        // get an array containing function names declared in the simple-declaration
        $arr = array();
        for ($i = 0;$i < $this->get_payload_length();++$i) {
            $elem = $this->get_payload_element($i);
            if ((get_class($elem)=="CPPInitDeclarator" || get_class($elem)=="CPPDeclarator") && $elem->is_function()) {
                $arr[] = $elem->get_name();
            }
        }
        return $arr;
    }

    function get_line_no() { // CPPElement::get_line_no
        // point the line no to the start of the declaration
        // by using the decl-specifier's line number (first element in payload)
        return $this->get_payload_element(0)->get_line_no();
    }
}

/* represents a C++ function-definition construct; checks are needed on this construct
   due to its similarity to CPPSimpleDeclaration */
class CPPFunctionDefinition extends CPPElement {
    /* payload contents:
        CPPDeclSpecSeq CPPDeclarator CPPCompoundStatement */

    /* try to parse a function-definition construct; assume the caller
       has created a working result frame on which to operate; if successful,
       the frame will be committed as a 'CPPFunctionDefinition' */
    static function try_parse(TokenParser $parser,$makeResult = true) {
        $checkA = null; // check working context for decl-spec-seq
        $checkB = null; // check working context for declarator
        $checkC = null; // check working context for compound-statement

        if ($makeResult)
            $parser->begin_working_result( get_class() );
        else
            // we must check any parse frames in 'parser' that failed to complete a previous construct
            list($checkA,$checkB,$checkC) = $parser->match_working_contexts('CPPDeclSpecSeq','CPPDeclarator','CPPCompoundStatement');

        if ($checkA!==false && ($checkA===true || CPPDeclSpecSeq::try_parse($parser))) {
            if ( is_null($checkA) )
                $parser->add_place_holder();
            if ($checkB!==false && ($checkB===true || CPPDeclarator::try_parse($parser))) {
                if ( is_null($checkB) )
                    $parser->add_place_holder();
                if ($checkC!==false && ($checkC===true || CPPCompoundStatement::try_parse($parser))) {
                    if ( is_null($checkC) )
                        $parser->add_place_holder();
                    $parser->commit_working_result_as( get_class() );
                    return true;
                }
            }
        }

        if ($makeResult)
            return $parser->discard_working_result();
        return false;
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }

    function get_name() { // CPPElement::get_name
        if ( !isset($this->get_name_cache) ) {
            // It is not safe to do this (can lead to undefined behavior)
            //$result = $this->find_first('IdentifierToken');
            list($result) = $this->match_elements("IdentifierToken",'#'); // match first IdentifierToken
            if ( !is_null($result) )
                $this->get_name_cache = $result->get_value();
            else
                $this->get_name_cache = get_class();
        }
        return $this->get_name_cache;
    }
    function get_line_no() { // CPPElement::get_line_no
        if ( !isset($this->get_line_no_cache) ) {
            // It is not safe to do this (can lead to undefined behavior)
            //$result = $this->find_first('CPPDirectDeclarator');
            list($result) = $this->match_elements("CPPDirectDeclarator",'#'); // match first CPPDirectDeclarator
            if ( !is_null($result) )
                $this->get_line_no_cache = $result->get_line_no();
            else
                $this->get_line_no_cache = -1;
        }
        return $this->get_line_no_cache;
    }
}

/* represents a using-directive construct */
class CPPUsingDirective extends CPPElement {
    /* payload contents:
        KeywordToken:"using" KeywordToken:"namespace" IdentifierToken OperatorPunctuator:";"
        KeywordToken:"using" KeywordToken:"namespace" (OperatorPunctuatorToken:"::" IdentifierToken) OperatorPunctuator:";" ... */

    static function try_parse(TokenParser $parser,$makeResult = true) {
        if ($makeResult)
            $parser->begin_working_result( get_class() );

        if ( $parser->does_token_match('KeywordToken','using') ) {
            $parser->add_token();
            if ( $parser->does_token_match('KeywordToken','namespace') ) {
                $parser->add_token();
                if ( $parser->does_token_match('OperatorPunctuatorToken','::') )
                    $parser->add_token();
                if ( $parser->does_token_match_ex('IdentifierToken',CPlusPlus::$user_defined_nested_name_specifier) ) {
                    do {
                        $parser->add_token();
                        if ( !$parser->does_token_match('OperatorPunctuatorToken','::') )
                            break;
                        $parser->add_token();
                    } while ( $parser->does_token_match_ex('IdentifierToken',CPlusPlus::$user_defined_nested_name_specifier) );
                    if ( $parser->does_token_match('OperatorPunctuatorToken',';') ) {
                        $parser->add_token();
                        $parser->commit_working_result_as( get_class() );
                        return true;
                    }
                }
            }
        }

        if ($makeResult)
            return $parser->discard_working_result();
        return false;
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }

    function get_name() { // CPPElement::get_name

    }
    function get_line_no() { // CPPElement::get_line_no

    }
}

/* represents a sequence of elements that did not conform properly to the grammar */
class CPPErrorElement extends CPPElement {
    /* payload contents:
        CPPElement: ... */

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }

    function get_name() { // CPPElement::get_name
        if ( !is_null($e = $this->get_payload_element(0)) )
            return $e->get_name();
        return "error";
    }
    function get_line_no() { // CPPElement::get_line_no
        if ( !is_null($e = $this->get_payload_element(0)) )
            return $e->get_line_no();
        return -1;
    }
}

/* represents a C++ source file (translation unit) */
class CPPTranslationUnit extends CPPElement {
    /* payload contents:
        (CPPSimpleDeclaration | CPPFunctionDefinition | CPPUsingDirective) ... */

    static function parse_translation_unit(TokenParser $parser) {
        // complete constructs are those commonly found in scope of a translation unit (for the P1 course level)
        static $completeConstructs = array('CPPSimpleDeclaration','CPPFunctionDefinition','CPPUsingDirective');
        $parser->begin_working_result(get_class());

        // attempt top-down parse of the translation unit; use the token stream that was passed
        // to the TokenParser when it was instantiated
        while ( !$parser->end_of_file() ) {
            // we create the working frame here so that if it fails we can still pass
            // any intermediate results on to another high-level construct routine
            $parser->begin_working_result('CPPElement');

            $success = false;
            foreach ($completeConstructs as $kind) {
                // try to parse high-level complete construct; pass false to indicate that
                // the operation should be done on the working result that we provide
                //if ( $kind::try_parse($parser,false) ) { // supported by PHP 5.3 and above
                if ( call_user_func(array($kind,'try_parse'),$parser,false) ) {
                    $success = true;
                    break;
                }
            }

            if (!$success) {
                // if a parse fails, then try to continue from the next token
                $parser->advance();
                $parser->discard_working_result();
            }
            else
                $parser->add_place_holder();
        }

        $parser->commit_working_result();
        return true; // allow zero or more constructs at translation-unit level
    }

    function __construct(array $elements,TokenParser $parser) {
        parent::__construct($elements,$parser);
    }

    function get_name() { // CPPElement::get_name
        return "translation-unit";
    }
    function get_line_no() { // CPPElement::get_line_no
        return 1;
    }
}

?>
