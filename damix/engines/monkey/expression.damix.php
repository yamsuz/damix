<?php
/*
* Copyright : https://github.com/ronnieholm/Monkey-CSharp
*/
declare(strict_types=1);
namespace damix\engines\monkey;

class ExpressionTest 
	extends CallExpression
{
	public $Param; //string
	
	public function Execute($args) 
	{
		$result = 0;

		foreach( $args as $val)
		{
			if ($val->Type == \Monkey\Core\ObjectType::INTEGER)
			{
				$i = intval($val->Inspect());
				$result += $i;
			}
		}
		$v = new \Monkey\Core\MonkeyInteger();
		$v->Value = $result;
		return $v;
	}
}