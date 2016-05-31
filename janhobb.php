<?php
/**
JanhoBB is a class used to replace predefined tags with html elements.
You can define your own tags. There are 2 types of tags, BB and replace.

BB tags have a open and a close tag fe. [b] and [/b] (you can define the format yourself). The data to be put inside the html element is put between these tags. You can also add html attributes to the elements by seperating them with the divider mark. Fe, you can define [b]Hello|#442512[/b] to translate to <b color="#442512">Hello</b>

Replace tags are simple tags that are to be replaced with the define html element replacement. These use the same format as the opening tag for the bb tags. fe [br] => <br>

@author Mikael Janhonen
@version 1.0
*/


/**
Hold information about the attributes assosiated with a bb rule.
*/
class JanhoHtmlAttribute{
	/**
	@var string $tag the html tag of the attribute
	@var int $ordernum attributes order number
	@var int $missing the num to be used if the attribute with order number is missing
	*/
	public $tag;
	public $ordernum;
	public $missing;

	/**
	@param string $tag the attributes html tag
	@param int $ordernum defines in what order should this attribute appear in the code. Fe if the order number is 2, the value of this attribute should be the second one (in the third palce) defined in the code. (If its a color fe => [b]The content|12px|#551166[/b]).
	@param int $missing defines what attribute should replace this one if this one is missing. -1 => nothing should replace => remove this attribute
	*/
	public function __construct($tag, $ordernum, $missing){
		$this->tag = $tag;
		$this->ordernum = $ordernum;
		$this->missing = $missing;
	}
}

/**
Rule about bb tags and what they correspond to
*/
class JanhoBBRule{
	/**
	@var string $bbTag bbtag to be replaced
	@var string $htmlTag htmlTag that will replace the bb
	@var JanhoHtmlAttribute[] $htmlAttributes html attributes that are related to this rule 
	*/
	public $bbTag;
	public $htmlTag;
	public $htmlAttributes = array();

	/**
	@param string $bbTag bb tag to look for
	@param string $htmlTag html tag to replace with
	*/
	public function __construct($bbTag, $htmlTag){
		$this->bbTag = $bbTag;
		$this->htmlTag = $htmlTag;
	}
	/**
	Creates a JanhoHtmlAttribute object and adds it to the rules attribute list
	@param string $htmlAttribute html attribute name
	@param int $ordernum passed to the JanhoHtmlAttribute object
	@param int $missing passed to the JanhoHtmlAttribute object
	*/
	public function initAttribute($htmlAttribute, $ordernum, $missing){
		$this->htmlAttributes[] = new JanhoHtmlAttribute($htmlAttribute, $ordernum, $missing);
	}
	/**
	Adds an JanhoHtmlAttribute object to the attributes list
	@param JanhoHtmlAttribute $janhohtmlattribute attribute to be added
	*/
	public function initJanhoAttribute($janhohtmlattribute){
		if(!is_a($janhohtmlattribute, 'JanhoHtmlAttribute')){
			throw new InvalidArgumentException('Invalid argument');
		}
		$this->htmlAttributes[] = $janhohtmlattribute;
	}
}
/**
Rule about replacement
*/
class JanhoReplaceRule{
	/**
	@var string $needle the tag that is searched and replaced in the string
	@var string $replace the html tag replacement
	*/
	public $needle;
	public $replace;

	/**
	@param string $needle string to be looked for
	@param string $replace string that repalces found needles
	*/
	public function __construct($needle, $replace){
		$this->needle = $needle;
		$this->replace = $replace;
	}
}


/**
Class that has the functionality to handle bb rules and replacements rules and replace them with the right html content
*/
class JanhoBB {
	/**
	@var JanhoBBRule[] $bbRules the list that holds all the bb rules of the system
	@var JanhoReplaceRule[] $replaceRules the list that holds all the replace rules of the system
	*/
	private $bbRules = array();
	private $replaceRules = array();

	/**
	@var string $bbOpen1 The before the actual tag part of the opening syntax (default '[')
	@var string $bbOpen2 The after the actual tag part of the opening syntax (default ']')
	@var string $bbClose1 The before the actual tag part of the closing syntax (default '[/')
	@var string $bbClose2 The after the actual tag part of the closing syntax (default ']')
	@var string $bbDivider The syntax for dividing the attributes (default '|')
	*/
	private $bbOpen1 = '[';
	private $bbOpen2 = ']';
	private $bbClose1 = '[/';
	private $bbClose2 = ']';
	private $bbDivider = '|';

	/**
	With this function you can determine your own syntax for the bb tags and replace tags
	@param string $o1 The before the actual tag part of the opening syntax (default '[')
	@param string $o2 The after the actual tag part of the opening syntax (default ']')
	@param string $c1 The before the actual tag part of the closing syntax (default '[/')
	@param string $c2 The after the actual tag part of the closing syntax (default ']')
	@param string $d The syntax for dividing the attributes (default '|')
	*/
	public function setBBTags($o1, $o2, $c1, $c2, $d){
		$this->bbOpen1 = $o1;
		$this->bbOpen2 = $o2;
		$this->bbClose1 = $c1;
		$this->bbClose2 = $c2;
		$this->bbDivider = $d;
	}

	/**
	Adds a new bb rule to the system
	@param JanhoBBRule $rule new rule
	@throws InvalidArgumentException if the parameter is not JanhoBBRule
	*/
	public function initBBRule($rule){
		if(!is_a($rule, 'JanhoBBRule')){
			throw new InvalidArgumentException('Invalid rule type');
		}
		$this->bbRules[] = $rule;
	}
	/**
	Adds a new replace rule to the system
	@param JanhoReplaceRule $rule new rule
	@throws InvalidArgumentException if the parameter is not JanhoReplaceRule
	*/
	public function initReplaceRule($rule){
		if(!is_a($rule, 'JanhoReplaceRule')){
			throw new InvalidArgumentException('Invalid rule type');
		}
		$this->replaceRules[] = $rule;
	}


	/**
	Replaces the bb tags with the corresponding html elements
	@param string $str string to be modified
	@return string Returns the modified string
	*/
	public function insertBB($str){
	        foreach($this->bbRules as $row){
	                $bb_start = $this->bbOpen1 . $row->bbTag . $this->bbOpen2;
	                $bb_end = $this->bbClose1 . $row->bbTag . $this->bbClose2;

	                //while open tags exist
	                while(strpos($str, $bb_start) !== false){
	                        $startindex = strpos($str, $bb_start);

	                        //remove all close tags that exist before current open tag
	                        while(strpos($str, $bb_end) !== false){
	                                if(strpos($str, $bb_end) <= $startindex){
	                                        $str = substr($str, 0, strpos($str, $bb_end)) . substr($str, strpos($str, $bb_end) + strlen($bb_end));
	                                        $startindex = strpos($str, $bb_start);
	                                } else {
	                                        break;
	                                }
	                        }

	                        //if no more close tags exist => quit
	                        if(strpos($str, $bb_end) === false){
	                                break;
	                        }

        	                $endindex = strpos($str, $bb_end);
        	                $contentstring = substr($str, $startindex + strlen($bb_start), $endindex - $startindex - strlen($bb_start));
        	                $newcontent = $this->createBBContent($row->htmlTag, $row->htmlAttributes, $contentstring);
        	                $str = substr($str, 0, $startindex) . $newcontent . substr($str, $endindex + strlen($bb_end));
                	}
		}
	        return $str;
	}

	/**
	Creates the html element that is the replacement of the bb tag
	@param string $htmltag html element tag that is the replacement
	@param JanhoHtmlAttribute[] $htmlAttributes attributes of the html element
	@param string $contentstring string with the content of the orginal bb tag
	@return string the html element that was created
	*/
	private function createBBContent($htmltag, $htmlAttributes, $contentstring){
	        $html = '';
	        $contentlist = explode($this->bbDivider, $contentstring);

	        $html .= '<' .$htmltag . ' ';

	        foreach($htmlAttributes as $row2){
	                if(isset($contentlist[$row2->ordernum])){
	                        $html .= $row2->tag . '="' . $contentlist[$row2->ordernum] .'" ';
	                } else {
	                        if($row2->missing >= 0){
	                                if($contentlist[$row2->missing]){
	                                        $html .= $row2->tag . '="' . $contentlist[$row2->missing] .'" ';
	                                }
	                        }
	                }
	        }

	        $html .= '>' . $contentlist[0] . '</'.$htmltag.'>';
	        return $html;

	}

	/**
	Replaces the replace tags with the corresponding html elements
	@param string $str string to be modified
	@return string Returns the modified string
	*/
	public function insertReplace($str){
		if(count($this->replaceRules) > 0){
		        foreach($this->replaceRules as $row3){
				$needle = $this->bbOpen1 . $row3->needle . $this->bbOpen2;
				$replace = '<' . $row3->replace . '/>';
		                $str = str_replace($needle, $replace, $str);
		        }
		}
	        return $str;
	}

	/**
	Does the full modification to the given string. Handles bb tags and replacement tags.
	@param string $str the string to be modified
	@return string the modified string
	*/
	public function modifyString($str){
		$newstr = $this->insertBB($str);
		$newstr = $this->insertReplace($newstr);
		return $newstr;
	}
}

