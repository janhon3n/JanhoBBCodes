<?php
class JanhoHtmlAttribute{
	public $tag;
	public $ordernum;
	public $missing;

	public function __construct($tag, $ordernum, $missing){
		$this->tag = $tag;
		$this->ordernum = $ordernum;
		$this->missing = $missing;
	}
}

class JanhoBBRule{
	public $bbTag;
	public $htmlTag;
	public $htmlAttributes = array();

	public function __construct($bbTag, $htmlTag){
		$this->bbTag = $bbTag;
		$this->htmlTag = $htmlTag;
	}

	public function initAttribute($htmlAttribute, $ordernum, $missing){
		$this->htmlAttributes[] = new JanhoHtmlAttribute($htmlAttribute, $ordernum, $missing);
	}
	public function initJanhoAttribute($janhohtmlattribute){
		if(!is_a($janhohtmlattribute, 'JanhoHtmlAttribute')){
			throw new InvalidArgumentException('Invalid argument');
		}
		$this->htmlAttributes[] = $janhohtmlattribute;
	}
}
class JanhoReplaceRule{
	public $needle;
	public $replace;

	public function __construct($needle, $replace){
		$this->needle = $needle;
		$this->replace = $replace;
	}
}

class JanhoBB {

	private $bbRules = array();
	private $replaceRules = array();

	private $bbOpen1 = '[';
	private $bbOpen2 = ']';
	private $bbClose1 = '[/';
	private $bbClose2 = ']';
	private $bbDivider = '|';

	public function __construct(){

	}

	public function setBBTags($o1, $o2, $c1, $c2, $d){
		$this->bbOpen1 = $o1;
		$this->bbOpen2 = $o2;
		$this->bbClose1 = $c1;
		$this->bbClose2 = $c2;
		$this->bbDivider = $d;
	}

	public function initBBRule($rule){
		if(!is_a($rule, 'JanhoBBRule')){
			throw new InvalidArgumentException('Invalid rule type');
		}
		$this->bbRules[] = $rule;
	}
	public function initReplaceRule($rule){
		if(!is_a($rule, 'JanhoReplaceRule')){
			throw new InvalidArgumentException('Invalid rule type');
		}
		$this->replaceRules[] = $rule;
	}



	public function insertBB($str){
		print_r($this->bbRules);
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
        	                $newcontent = $this->createBBContent($row->bbTag, $row->htmlTag, $row->htmlAttributes, $contentstring);
        	                $str = substr($str, 0, $startindex) . $newcontent . substr($str, $endindex + strlen($bb_end));
                	}
		}
	        return $str;
	}

	private function createBBContent($bbcode, $htmlcode, $htmlAttributes, $contentstring){
	        $html = '';
	        $contentlist = explode($this->bbDivider, $contentstring);

	        $html .= '<' .$htmlcode . ' ';

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

	        $html .= '>' . $contentlist[0] . '</'.$htmlcode.'>';
	        return $html;

	}

	public function insertReplace($str){
		return $str;
	}

	public function modifyString($str){
		$newstr = $this->insertBB($str);
		$newstr = $this->insertReplace($newstr);
		return $newstr;
	}
}



$janhobb = new JanhoBB();
$newrule = new JanhoBBRule('b','b');
$janhobb->initBBRule($newrule);

$newrule = new JanhoBBRule('br','br');
$janhobb->initBBRule($newrule);

$newrule = new JanhoBBRule('url','a');
$newrule->initAttribute('href', 1, 0);
$janhobb->initBBRule($newrule);

echo $janhobb->modifyString($argv[1]);
echo '
';
