<?php
/*
* Copyright : https://github.com/ronnieholm/Monkey-CSharp
*/
declare(strict_types=1);
namespace damix\engines\monkey;

interface INode
{
		public function TokenLiteral() : string;

	
	public function String() : string;
}


interface IStatement extends INode
{
}


interface IExpression extends INode
{
}

class Program implements INode
{
	public $Statements = array(); //List<IStatement>
	public function TokenLiteral() : string
	{
		return count($this->Statements) > 0 ? $this->Statements[0]->TokenLiteral() : "";
	}
	public function String() : string
	{
		$sb = '';
		foreach($this->Statements as $s)
		{
			$sb .= $s.String();
		}
		return $sb;
	}
}

class LetStatement implements IStatement
{
	public $Token; //Token
	public $Name; //Identifier
	public $Value; //IExpression
	public function TokenLiteral() : string
	{
		return $this->Token->Literal;
	}
	public function String() : string
	{
		$sb = '';
		$sb .= "{TokenLiteral} {$Name->String()} = ";
		if ($this->Value != null)
		{
			$sb .= $this->Value->String();
		}
		$sb .= ";";
		return $sb;
	}
}

class ReturnStatement implements IStatement
{
	public $Token; //Token
	public $ReturnValue; //IExpression
	public function TokenLiteral() : string
	{
		return $this->Token->Literal;
	}
	public function String() : string
	{
		$sb = '';
		$sb .= "{$this->TokenLiteral()} ";
		if ($this->ReturnValue != null)
		{
			$sb .= $this->ReturnValue->String();
		}
		$sb .= ";";
		return $sb;
	}
}

class ExpressionStatement implements IStatement
{
	public $Token; //Token
	public $Expression; //IExpression
	public function TokenLiteral() : string
	{
		return $this->Token->Literal;
	}
	public function String() : string
	{
		return $this->Expression != null ? $this->Expression->String() : "";
	}
}

class Identifier implements IExpression
{
	public $Token; //Token
	public $Value; //string
	public function TokenLiteral() : string
	{
		return $this->Token->Literal;
	}
	public function String() : string
	{ 
		return $this->Value;
	}
}

class IntegerLiteral implements IExpression
{
	public $Token; //Token
	public $Value; //long
	public function TokenLiteral() : string
	{
		return $this->Token->Literal;
	}
	public function String() : string
	{ 
		return $this->Token->Literal; 
	}
}

class PrefixExpression implements IExpression
{
	public $Token; //Token
	public $Operator; //string
	public $Right; //IExpression
	public function TokenLiteral() : string
	{
		return $this->Token->Literal;
	}
	public function String() : string
	{
		return "({$this->Operator}{$this->Right->String()})"; 
	}
}

class InfixExpression implements IExpression
{
	public $Token; //Token
	public $Left; //IExpression
	public $Operator; //string
	public $Right; //IExpression
	public function TokenLiteral() : string
	{
		return $this->Token->Literal;
	}
	public function String() : string
	{ 
		return "({$this->Left->String()} {$this->Operator} {$this->Right->String()})"; 
	}
}

class Boolean_ implements IExpression
{
	public $Token; //Token
	public $Value; //bool
	public function TokenLiteral() : string
	{
		return $this->Token->Literal;
	}
	public function String() : string
	{ 
		return strtolower($this->Token->Literal); 
	}
	
}

class BlockStatement implements IStatement
{
	public $Token; //Token
	public $Statements; //List<IStatement>
	public function TokenLiteral() : string
	{
		return $this->Token->Literal;
	}
	public function String() : string
	{
		$sb = '';
		foreach($this->Statements as $stmt)
		{
			$sb .= $stmt->String();
		}
		return $sb;
	}
}

class IfExpression implements IExpression
{
	public $Token; //Token
	public $Condition; //IExpression
	public $Consequence; //BlockStatement 
	public $Alternative; //BlockStatement
	public function TokenLiteral() : string
	{
		return $this->Token->Literal;
	}
	public function String() : string
	{
		$sb = '';
		$sb .= "if";
		$sb .= $this->Condition->String();
		$sb .= " ";
		$sb .= $this->Consequence->String();

		if ($this->Alternative != null)
		{
			$sb .= "else ";
			$sb .= $this->Alternative->String();
		}                
		return $sb;
	}
}

class FunctionLiteral implements IExpression
{
	public $Token; //Token
	public $Parameters; //List<Identifier>
	public $Body; //BlockStatement
	public function TokenLiteral() : string
	{
		return $this->Token->Literal;
	}
	public function String() : string
	{
		$sb = '';
		$params_ = array();

		foreach ($this->Parameters as $p)
		{
			$params_[] = $p->String();
		}

		$sb .= $this->TokenLiteral();
		$sb .= "(";
		$sb .= implode(", ", $params_);
		$sb .= ") ";
		$sb .= $this->Body->String();
		return $sb;
	}
}

class CallExpression implements IExpression
{
	public $Token; //Token
	public $Function; //IExpression
	public $Arguments; //List<IExpression>
	public function TokenLiteral() : string
	{
		return $this->Token->Literal;
	}
	public function String() : string
	{
		$sb = '';
		$args = array();

		foreach ($this->Arguments as $a)
		{
			$args[] = $a->String();
		}

		$sb .= $this->Function->String();
		$sb .= "(";
		$sb .= implode(", ", $args);
		$sb .= ")";
		return $sb;
	}
}

class StringLiteral implements IExpression
{
	public $Token; //Token
	public $Value; //string
	public function TokenLiteral() : string
	{
		return $this->Token->Literal;
	}
	public function String() : string
	{ 
		return $this->Token->Literal;
	}
}

class ArrayLiteral implements IExpression
{
	public $Token; //Token
	public $Elements; //List<IExpression>
	public function TokenLiteral() : string
	{
		return $this->Token->Literal;
	}
	public function String() : string
	{
		$sb = '';
		$elements = array();

		foreach ($this->Elements as $e)
		{
			$elements[] = $e->String();
		}
		
		$sb .= "[";
		$sb .= implode(", ", $elements);
		$sb .= "]";
		return $sb;
	}
}

class IndexExpression implements IExpression
{
	public $Token; //Token

	public $Left; //IExpression
	public $Index ; //IExpression
	public function TokenLiteral() : string
	{
		return $this->Token->Literal;
	}
	public function String() : string
	{
		$sb = "(";
		$sb .= $this->Left->String();
		$sb .= "[";
		$sb .= $this->Index->String();
		$sb .= "])";
		return $sb;
	}
}

class HashLiteral implements IExpression
{
	public $Token; //Token
	public $Pairs; // Dictionary<IExpression, IExpression>
	public function TokenLiteral() : string
	{
		return $this->Token->Literal;
	}
	public function String() : string
	{
		$pairs = array();
		
		foreach ($this->Pairs as $kv)
			$pairs[] = "{$kv->Key->String()}:{$kv->Value->String()}";

		$sb = "{";
		$sb .= implode(", ", $pairs);
		$sb .= "}";    
		return $sb;
	}
}

