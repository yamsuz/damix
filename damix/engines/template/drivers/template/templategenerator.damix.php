<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\template\drivers\template;

class TemplateGenerator
	extends \damix\engines\template\TemplateBaseGenerator
{
	private $_literals;
	private  $_vartype=array(T_CONSTANT_ENCAPSED_STRING,T_DNUMBER,
			T_ENCAPSED_AND_WHITESPACE,T_LNUMBER,T_OBJECT_OPERATOR,T_STRING,
			T_WHITESPACE,T_ARRAY);
	private array $_assignOp=array(T_AND_EQUAL,T_DIV_EQUAL,T_MINUS_EQUAL,
			T_MOD_EQUAL,T_MUL_EQUAL,T_OR_EQUAL,T_PLUS_EQUAL,T_PLUS_EQUAL,
			T_SL_EQUAL,T_SR_EQUAL,T_XOR_EQUAL);
	private array $_op=array(T_BOOLEAN_AND,T_BOOLEAN_OR,T_EMPTY,T_INC,T_DEC,
			T_ISSET,T_IS_EQUAL,T_IS_GREATER_OR_EQUAL,T_IS_IDENTICAL,
			T_IS_NOT_EQUAL,T_IS_NOT_IDENTICAL,T_IS_SMALLER_OR_EQUAL,
			T_LOGICAL_AND,T_LOGICAL_OR,T_LOGICAL_XOR,T_SR,T_SL,
			T_DOUBLE_ARROW);
	private array $_inLocaleOk=array(T_STRING,T_ABSTRACT,T_AS,T_BREAK,T_CASE,
			T_CATCH,T_CLASS,T_CLONE,T_CONST,T_CONTINUE,T_DECLARE,T_DEFAULT,
			T_DNUMBER,T_DO,T_ECHO,T_ELSE,T_ELSEIF,T_EMPTY,T_ENDIF,T_ENDFOR,
			T_EVAL,T_EXIT,T_EXTENDS,T_FINAL,T_FOR,T_FOREACH,T_FUNCTION,
			T_GLOBAL,T_GOTO,T_IF,T_IMPLEMENTS,T_INCLUDE,T_INSTANCEOF,T_INTERFACE,
			T_LIST,T_LNUMBER,T_LOGICAL_AND,T_LOGICAL_OR,T_LOGICAL_XOR,
			T_NAMESPACE,T_NEW,T_PRINT,T_PRIVATE,T_PUBLIC,T_PROTECTED,T_REQUIRE,
			T_RETURN,T_STATIC,T_SWITCH,T_THROW,T_TRY,T_USE,T_VAR,T_WHILE);
	protected array $_allowedInVar;
	protected array $_excludedInVar=array(';','=');
	protected array $_allowedInExpr;
	protected array $_allowedInForeach;
	protected array $_allowedConstants=array('TRUE','FALSE','NULL','M_1_PI',
			'M_2_PI','M_2_SQRTPI','M_E','M_LN10','M_LN2','M_LOG10E',
			'M_LOG2E','M_PI','M_PI_2','M_PI_4','M_SQRT1_2','M_SQRT2');
	private array $_pluginPath=array();
	protected string $_metaBody='';
	protected array $_modifier=array('upper'=>'strtoupper','lower'=>'strtolower',
			'escxml'=>'htmlspecialchars','eschtml'=>'htmlspecialchars',
			'strip_tags'=>'strip_tags','escurl'=>'rawurlencode',
			'capitalize'=>'ucwords','stripslashes'=>'stripslashes',
			'upperfirst'=>'ucfirst','json_encode'=>'json_encode');
	private array $_blockStack=array();
	private array $_allowedAssign=array();
	private string $_sourceFile;
	private string $_currentTag;
	public string $outputType='';
	public bool $trusted=true;
	protected array $_userFunctions=array();
	protected array $headtemplate=array();
	protected ?array $templateDrivers=null;
	protected bool $removeASPtags=true;
	
	public function __construct(){
		if(defined('T_CHARACTER')){
			$this->_vartype[]=T_CHARACTER;
		}
		$this->_allowedInVar=array_merge($this->_vartype,array(T_INC,T_DEC,T_DOUBLE_ARROW));
		$this->_allowedInExpr=array_merge($this->_vartype,$this->_op);
		$this->_allowedAssign=array_merge($this->_vartype,$this->_assignOp,$this->_op);
		$this->_allowedInForeach=array_merge($this->_vartype,array(T_AS,T_DOUBLE_ARROW));
		$this->removeASPtags=(ini_get("asp_tags")=="1");		
	}
	public function generate( \damix\engines\template\TemplateSelector $selector ) : bool
    {
		$this->_sourceFile = $selector->getFileDefault();
		
		if( file_exists( $this->_sourceFile ) )
		{
			$content = \damix\engines\tools\xFile::read( $this->_sourceFile );
		
			$contentfct = array();
			
			$contentfct[] = '<?php ';
			$contentfct[] = 'function template_' . $selector->getHashCode() . '($t){';
			$contentfct[] = '?>';
			$contentfct[] = $this->compileContent($content);
			
			$contentfct[] = '<?php ';
			$contentfct[] = 'return true;';
			$contentfct[] = '}';
			// if( $this->monkeycompile )
			// {
				// $contentfct[] = $this->CompileMonkey();
			// }
			$contentfct[] = '?>';
		
			
			$tpl = $selector->getTempPath();
			
			\damix\engines\tools\xFile::write( $tpl, implode("\r\n", $contentfct) );
			
			return true;
		}
		else
		{
			throw new \damix\core\exception\CoreException('Le fichier du template n\'existe pas ' . $this->_sourceFile);
		}
		
		return false;
    }
	
	private function loadDriver()
	{
		$dir = __DIR__  ;
		$dir2 = \damix\application::getPathApp() . 'plugins' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'template' ;
       
        $this->templateDrivers = array_merge(self::loadTemplateDriver($dir), self::loadTemplateDriver($dir2));
	}
	
	private static function loadTemplateDriver( $dir ) : array
    {
		if( ! is_dir( $dir ) )
		{
			return array();
		}
        $directories = scandir( $dir );
        $out = array();
        foreach( $directories as $elt )
        {
            if( $elt != '.' && $elt != '..' )
            {
                if( is_dir( $dir . DIRECTORY_SEPARATOR . $elt ) )
                {
                    $out = array_merge( $out, self::loadTemplateDriver( $dir . DIRECTORY_SEPARATOR . $elt . DIRECTORY_SEPARATOR ) );
                }
                else
                {
                    if( preg_match( '/^([a-zA-Z0-9]*)\.plugin\.php$/', $elt, $match ) )
                    {
                        $name = $match[1];
						$basename = basename($dir);
                        $out[ $basename ][ $name ] = array( 
                            'classname' => '\damix\engines\template\drivers\TemplatesFunction' . ucfirst($name),
                            'name' => $name, 
                            'load' => false, 
                            'fullpath' => $dir . $elt,
                            );
                    }
                }
            }
        }
        
        return $out;
    }
	
	/*
	public function compile($selector){
		$this->_sourceFile=$selector->getPath();
		$this->outputType=$selector->outputType;
		$this->trusted=$selector->trusted;
		$md5=md5($selector->module.'_'.$selector->resource.'_'.$this->outputType.($this->trusted?'_t':''));
		jApp::pushCurrentModule($selector->module);
		if(!file_exists($this->_sourceFile)){
			$this->doError0('errors.tpl.not.found');
		}
		$header="if (jApp::config()->compilation['checkCacheFiletime'] &&\n";
		$header.="filemtime('".$this->_sourceFile.'\') > '.filemtime($this->_sourceFile)."){ return false;\n} else {\n";
		$footer="return true;}\n";
		$this->compileString(file_get_contents($this->_sourceFile),$selector->getCompiledFilePath(),
			$selector->userModifiers,$selector->userFunctions,$md5,$header,$footer);
		jApp::popCurrentModule();
		return true;
	}
	public function compileString($templatecontent,$cachefile,$userModifiers,$userFunctions,$md5,$header='',$footer=''){
		$this->_modifier=array_merge($this->_modifier,$userModifiers);
		$this->_userFunctions=$userFunctions;
		$result=$this->compileContent($templatecontent);
		$header="<?php \n".$header;
		foreach($this->_pluginPath as $path=>$ok){
			$header.=' require_once(\''.$path."');\n";
		}
		$header.='function template_meta_'.$md5.'($t){';
		$header.="\n".$this->_metaBody."\n}\n";
		$header.='function template_'.$md5.'($t){'."\n?>";
		$result=$header.$result."<?php \n}\n".$footer;
		jFile::write($cachefile,$result);
		return true;
	}
	*/
	protected function compileContent($tplcontent){
		$this->_metaBody='';
		$this->_blockStack=array();
		$tplcontent=preg_replace("!<\?((?:php|=|\s).*)\?>!s",'',$tplcontent);
		$tplcontent=preg_replace("!{\*(.*?)\*}!s",'',$tplcontent);
		$tplcontent=preg_replace_callback("!(<\?.*\?>)!sm",function($matches){
			return '<?php echo \''.str_replace("'","\\'",$matches[1]).'\'?>';
		},$tplcontent);
		if($this->removeASPtags){
		$tplcontent=preg_replace("!<%.*%>!s",'',$tplcontent);
		}
		preg_match_all("!{literal}(.*?){/literal}!s",$tplcontent,$_match);
		$this->_literals=$_match[1];
		$tplcontent=preg_replace("!{literal}(.*?){/literal}!s",'{literal}',$tplcontent);
		$tplcontent=preg_replace_callback("/{((.).*?)}(\n)/sm",function($matches){
				list($full,,$firstcar,$lastcar)=$matches;
				if($firstcar=='='||$firstcar=='$'||$firstcar=='@'){
					return "$full\n";
				}
				else return $full;
			},$tplcontent);
		$tplcontent=preg_replace_callback("/{((.).*?)}/sm",array($this,'_callback'),$tplcontent);
		$tplcontent=preg_replace('/<\?php\\s+\?>/','',$tplcontent);
		if(count($this->_blockStack))
			$this->doError1('errors.tpl.tag.block.end.missing',end($this->_blockStack));
			
		$head = '<?php' . PHP_EOL;
		$head .= implode(PHP_EOL, $this->headtemplate);
		$head .= '?>' . PHP_EOL;
		return $head . $tplcontent;
	}
	public function _callback($matches){
		list(,$tag,$firstcar)=$matches;
		if(!preg_match('/^\$|@|=|[a-zA-Z\/]$/',$firstcar)){
			// throw new jException('jelix~errors.tpl.tag.syntax.invalid',array($tag,$this->_sourceFile));
		}
		$this->_currentTag=$tag;
		if($firstcar=='='){
			return  '<?php echo '.$this->_parseVariable(substr($tag,1)).'; ?>';
		}else if($firstcar=='$'||$firstcar=='@'){
			return  '<?php echo '.$this->_parseVariable($tag).'; ?>';
		}else{
			if(!preg_match('/^(\/?[a-zA-Z0-9_]+)(?:(?:\s+(.*))|(?:\((.*)\)))?$/ms',$tag,$m)){
				// throw new jException('jelix~errors.tpl.tag.function.invalid',array($tag,$this->_sourceFile));
			}
			if(count($m)==4){
				$m[2]=$m[3];
			}
			if(!isset($m[2]))$m[2]='';
			if($m[1]=='ldelim')return '{';
			if($m[1]=='rdelim')return '}';
			
			return '<?php '.$this->_parseFunction($m[1],$m[2]).'?>';
		}
	}
	protected function _parseVariable($expr){
		$tok=explode('|',$expr);
		$res=$this->_parseFinal(array_shift($tok),$this->_allowedInVar,$this->_excludedInVar);
		foreach($tok as $modifier){
			if(!preg_match('/^(\w+)(?:\:(.*))?$/',$modifier,$m)){
				$this->doError2('errors.tpl.tag.modifier.invalid',$this->_currentTag,$modifier);
			}
			if(isset($m[2])){
				$targs=$this->_parseFinal($m[2],$this->_allowedInVar,$this->_excludedInVar,true,',',':');
				array_unshift($targs,$res);
			}else{
				$targs=array($res);
			}
			if($path=$this->_getPlugin('cmodifier',$m[1])){
				require_once($path[0]);
				$fct=$path[1];
				$res=$fct($this,$targs);
			}else if($path=$this->_getPlugin('modifier',$m[1])){
				$res=$path[1].'('.implode(',',$targs).')';
				$this->_pluginPath[$path[0]]=true;
			}else{
				if(isset($this->_modifier[$m[1]])){
					$res=$this->_modifier[$m[1]].'('.$res.')';
				}else{
					$this->doError2('errors.tpl.tag.modifier.unknown',$this->_currentTag,$m[1]);
				}
			}
		}
		return $res;
	}
	protected function _parseFunction($name,$args){
		$res='';
		
		switch($name){
			case 'if':
				$res='if('.$this->_parseFinal($args,$this->_allowedInExpr).'):';
				array_push($this->_blockStack,'if');
				break;
			case 'else':
				if(substr(end($this->_blockStack),0,2)!='if')
					$this->doError1('errors.tpl.tag.block.end.missing',end($this->_blockStack));
				else
					$res='else:';
				break;
			case 'elseif':
				if(end($this->_blockStack)!='if')
					$this->doError1('errors.tpl.tag.block.end.missing',end($this->_blockStack));
				else
					$res='elseif('.$this->_parseFinal($args,$this->_allowedInExpr).'):';
				break;
			case 'foreach':
				if($this->trusted)
					$notallowed=array(';','!');
				else
					$notallowed=array(';','!','(');
				if(preg_match("/^\s*\((.*)\)\s*$/",$args,$m))
					$args=$m[1];
				$res='foreach('.$this->_parseFinal($args,$this->_allowedInForeach,$notallowed).'):';
				array_push($this->_blockStack,'foreach');
				break;
			case 'while':
				$res='while('.$this->_parseFinal($args,$this->_allowedInExpr).'):';
				array_push($this->_blockStack,'while');
				break;
			case 'for':
				if($this->trusted)
					$notallowed=array();
				else
					$notallowed=array('(');
				if(preg_match("/^\s*\((.*)\)\s*$/",$args,$m))
					$args=$m[1];
				$res='for('. $this->_parseFinal($args,$this->_allowedInExpr,$notallowed).'):';
				array_push($this->_blockStack,'for');
				break;
			case '/foreach':
			case '/for':
			case '/if':
			case '/while':
				$short=substr($name,1);
				if(end($this->_blockStack)!=$short){
					$this->doError1('errors.tpl.tag.block.end.missing',end($this->_blockStack));
				}else{
					array_pop($this->_blockStack);
					$res='end'.$short.';';
				}
				break;
			case 'assign':
			case 'eval':
				$res=$this->_parseFinal($args,$this->_allowedAssign).';';
				break;
			case 'literal':
				if(count($this->_literals))
					$res='?>'.array_shift($this->_literals).'<?php ';
				else
					$this->doError1('errors.tpl.tag.block.end.missing','literal');
				break;
			case '/literal':
				$this->doError1('errors.tpl.tag.block.begin.missing','literal');
				break;
			case 'meta':
				$this->_parseMeta($args);
				break;
			case 'meta_if':
				$metaIfArgs=$this->_parseFinal($args,$this->_allowedInExpr);
				$this->_metaBody.='if('.$metaIfArgs.'):'."\n";
				array_push($this->_blockStack,'meta_if');
				break;
			case 'meta_else':
				if(substr(end($this->_blockStack),0,7)!='meta_if'){
					$this->doError1('errors.tpl.tag.block.end.missing',end($this->_blockStack));
				}else{
					$this->_metaBody.="else:\n";
				}
				break;
			case 'meta_elseif':
				if(end($this->_blockStack)!='meta_if'){
					$this->doError1('errors.tpl.tag.block.end.missing',end($this->_blockStack));
				}else{
					$elseIfArgs=$this->_parseFinal($args,$this->_allowedInExpr);
					$this->_metaBody.='elseif('.$elseIfArgs."):\n";
				}
				break;
			case '/meta_if':
				$short=substr($name,1);
				if(end($this->_blockStack)!=$short){
					$this->doError1('errors.tpl.tag.block.end.missing',end($this->_blockStack));
				}else{
					array_pop($this->_blockStack);
					$this->_metaBody.="endif;\n";
				}
				break;
			default:
				$endBlock = false;
				if( preg_match( '/^(\/){0,1}(\w+)$/', $name, $out ) )
				{
					if( $out[1] == '/' )
					{
						$endBlock = true;
						$name = $out[2];
					}
				}
				
				if(preg_match('!^/(\w+)$!',$name,$m)){
					if(end($this->_blockStack)!=$m[1]){
						$this->doError1('errors.tpl.tag.block.end.missing',end($this->_blockStack));
					}else{
						array_pop($this->_blockStack);
						if(function_exists($fct='jtpl_block_'.$this->outputType.'_'.$m[1])){
							$res=$fct($this,false,null);
						}else if(function_exists($fct='jtpl_block_common_'.$m[1])){
							$res=$fct($this,false,null);
						}else
							$this->doError1('errors.tpl.tag.block.begin.missing',$m[1]);
					}
				}else if(preg_match('/^meta_(\w+)$/',$name,$m)){
					if($path=$this->_getPlugin('meta',$m[1])){
						$this->_parseMeta($args,$path[1]);
						$this->_pluginPath[$path[0]]=true;
					}else{
						$this->doError1('errors.tpl.tag.meta.unknown',$m[1]);
					}
					$res='';
				}else if($path=$this->_getPlugin('block',$name)){
					require_once($path[0]);
					$argfct=$this->_parseFinal($args,$this->_allowedAssign,array(';'),true);
					$fct=$path[1];
					$res=$fct($this,true,$argfct);
					array_push($this->_blockStack,$name);
				}else if($path=$this->_getPlugin('cfunction',$name)){
					$argfct=$this->_parseFinal($args,$this->_allowedAssign,array(';'),false);
					
					if( ! $path['load'])
					{
						require_once($path['fullpath']);
					}
					$classname = $path['classname'];
					$obj = new $classname();
					$obj->endBlock = $endBlock;
					$res=$obj->Execute($argfct);
					$this->templateDrivers['function'][$name]['load']=true;
				}else if($path=$this->_getPlugin('function',$name)){
					
					$argfct=$this->_parseFinal($args,$this->_allowedAssign);
					$res='';
					$this->headtemplate[$name] = 'require_once(\'' . $path['fullpath'].'\');';
					$res.='$obj = new ' . $path['classname'].'();';
					$res.='print $obj->Execute( '.(trim($argfct)!=''? $argfct:'').');';
					$this->templateDrivers['function'][$name]['load']=true;
				}else if(isset($this->_userFunctions[$name])){
					$argfct=$this->_parseFinal($args,$this->_allowedAssign);
					$res=$this->_userFunctions[$name].'( $t'.(trim($argfct)!=''?','.$argfct:'').');';
				}else{
					$this->doError1('errors.tpl.tag.function.unknown',$name);
				}
		}
		return $res;
	}
	public function isInsideBlock($blockName,$onlyUpper=false){
		if($onlyUpper)
			return(end($this->_blockStack)==$blockName);
		for($i=count($this->_blockStack)-1;$i>=0;$i--){
			if($this->_blockStack[$i]==$blockName)
				return true;
		}
		return false;
	}
	protected function _parseFinal($string,$allowed=array(),$exceptchar=array(';'),
									$splitArgIntoArray=false,$sep1=',',$sep2=','){
		$tokens=token_get_all('<?php '.$string.'?>');
		$results=array();
		$result='';
		$first=true;
		$inLocale=false;
		$locale='';
		$bracketcount=$sqbracketcount=0;
		$firstok=array_shift($tokens);
		if($firstok=='<'&&$tokens[0]=='?'&&is_array($tokens[1])
			&&$tokens[1][0]==T_STRING&&$tokens[1][1]=='php'){
			array_shift($tokens);
			array_shift($tokens);
		}
		$previousTok=null;
		foreach($tokens as $tok){
			if(is_array($tok)){
				list($type,$str)=$tok;
				$first=false;
				if($type==T_CLOSE_TAG){
					$previousTok=$tok;
					continue;
				}
				if($inLocale&&in_array($type,$this->_inLocaleOk)){
					$locale.=$str;
				}elseif($type==T_VARIABLE&&$inLocale){
					$locale.='\'.$t->parameters[\''.substr($str,1).'\'].\'';
				}elseif($type==T_VARIABLE){
					if(is_array($previousTok)&&$previousTok[0]==T_OBJECT_OPERATOR)
						$result.='{$t->parameters[\''.substr($str,1).'\']}';
					else
						$result.='$t->parameters[\''.substr($str,1).'\']';
				}elseif($type==T_WHITESPACE||in_array($type,$allowed)){
					if(!$this->trusted&&$type==T_STRING&&defined($str)
						&&!in_array(strtoupper($str),$this->_allowedConstants)){
						$this->doError2('errors.tpl.tag.constant.notallowed',$this->_currentTag,$str);
					}
					if($type==T_WHITESPACE)
						$str=preg_replace("/(\s+)/ms"," ",$str);
					$result.=$str;
				}else{
					$this->doError2('errors.tpl.tag.phpsyntax.invalid',$this->_currentTag,$str);
				}
			}else{
				if($tok=='@'){
					if($inLocale){
						$inLocale=false;
						if($locale==''){
							$this->doError1('errors.tpl.tag.locale.invalid',$this->_currentTag);
						}else{
							$result.='\damix\engines\locales\Locale::get(\''.$locale.'\')';
							$locale='';
						}
					}else{
						$inLocale=true;
					}
				}elseif($inLocale&&($tok=='.'||$tok=='~')){
					$locale.=$tok;
				}elseif($inLocale||in_array($tok,$exceptchar)
						||($first&&$tok!='!'&&$tok!='(')){
					$this->doError2('errors.tpl.tag.character.invalid',$this->_currentTag,$tok);
				}elseif($tok=='('){
					$bracketcount++;
					$result.=$tok;
				}elseif($tok==')'){
					$bracketcount--;
					$result.=$tok;
				}elseif($tok=='['){
					$sqbracketcount++;
					$result.=$tok;
				}elseif($tok==']'){
					$sqbracketcount--;
					$result.=$tok;
				}elseif($splitArgIntoArray&&($tok==$sep1||$tok==$sep2)
						&&$bracketcount==0&&$sqbracketcount==0){
					$results[]=$result;
					$result='';
				}else{
					$result.=$tok;
				}
				$first=false;
			}
			$previousTok=$tok;
		}
		if($inLocale){
			$this->doError1('errors.tpl.tag.locale.end.missing',$this->_currentTag);
		}
		if($bracketcount!=0||$sqbracketcount!=0){
			$this->doError1('errors.tpl.tag.bracket.error',$this->_currentTag);
		}
		$last=end($tokens);
		if(!is_array($last)||$last[0]!=T_CLOSE_TAG){
			$this->doError1('errors.tpl.tag.syntax.invalid',$this->_currentTag);
		}
		if($splitArgIntoArray){
			if($result!='')$results[]=$result;
			return $results;
		}else{
			return $result;
		}
	}
	protected function _parseMeta($args,$fct=''){
		if(preg_match('/^(\w+)(\s+(.*))?$/',$args,$m)){
			if(isset($m[3]))
				$argfct=$this->_parseFinal($m[3],$this->_allowedInExpr);
			else
				$argfct='null';
			if($fct!=''){
				$this->_metaBody.=$fct.'( $t,'."'".$m[1]."',".$argfct.");\n";
			}else{
				$this->_metaBody.="\$t->_meta['".$m[1]."']=".$argfct.";\n";
			}
		}else{
			$this->doError1('errors.tpl.tag.meta.invalid',$this->_currentTag);
		}
	}
	public function addMetaContent($content){
		$this->_metaBody.=$content."\n";
	}
	protected function _getPlugin($type,$name): ?array
	{
		$endBlock = false;
		

		if( $this->templateDrivers === null )
		{
			$this->loadDriver();
		}
		
		$out = $this->templateDrivers[$type][$name] ?? null;
		return $out;
	}
	public function doError0($err){
		// throw new jException('jelix~'.$err,array($this->_sourceFile));
	}
	public function doError1($err,$arg){
		// throw new jException('jelix~'.$err,array($arg,$this->_sourceFile));
	}
	public function doError2($err,$arg1,$arg2){
		// throw new jException('jelix~'.$err,array($arg1,$arg2,$this->_sourceFile));
	}
}
