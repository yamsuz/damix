<?php
/*
* Copyright : https://github.com/ronnieholm/Monkey-CSharp
*/
declare(strict_types=1);
namespace damix\engines\monkey;

// using BuiltinFunction = Func<List<IMonkeyObject>, IMonkeyObject>;

// Called Object in the book, but renamed to MonkeyObject so as to not
// confuse it with System.Object.
interface IMonkeyObject
{
	function Inspect() : string;
}

interface IHashable
{
	function HashKey() : HashKey;
}

class ObjectType
{
	const NONE = 0;
	const INTEGER = 1;
	const BOOLEAN = 2;
	const NULL = 3;
	const RETURNVALUE = 4;
	const ERROR = 5;
	const FUNCTION = 6;
	const STRING = 7;
	const BUILTIN = 8;
	const ARRAY = 9;
	const CSHARP = 10;
	const HASH = 11;
	const FIELD = 12;
}

// Making it a struct and not a class makes the type readily usable as the
// key within a dictionary and comparable within tests using Assert.Equals.
// Because both the Type and Value within the struct are both value types,
// they're easily comparable and in combination are used as key for a
// Dictionary<,>.
class HashKey
{
	// Scopes the hash to a particular object type.
	public $Type; //ObjectType

	// Because hash key is an integer, we can compare keys using the ==
	// operator without the need to overload Object.GetHashCode() and
	// Object.Equals() with their intricacies.
	public $Value; //long
}

class MonkeyInteger implements IMonkeyObject, IHashable
{
	public function __construct( $val = null )
	{
		if( $val !== null )
		{
			$this->Value = $val;
		}
	}
	public $Type = ObjectType::INTEGER;
	public $Value;
	
	public function Inspect() : string
	{
		return strval($this->Value);
	}

	public function HashKey(): HashKey
	{
		$HashKey = new HashKey(); 
		$HashKey->Type = $this->Type;
		$HashKey->Value = $this->Value;
		return $HashKey;
	}
}

class MonkeyBoolean implements IMonkeyObject, IHashable
{
	public $Type = ObjectType::BOOLEAN;
	public $Value;
	public function __construct($val = null)
	{
		if( $val !== null)
		{
			$this->Value = $val;
		}
	}
	public function Inspect() : string
	{
		return $this->Value->ToString();
	}
	public function HashKey(): HashKey
	{
		$HashKey = new HashKey(); 
		$HashKey->Type = $this->Type;
		$HashKey->Value = tobool($this->Value);
		return $HashKey;
	}
	
}

// MonkeyNull is a type just like MonkeyInteger and MonkeyBoolean except it
// doesn't wrap any value. It represents the absence of any value.
class MonkeyNull implements IMonkeyObject
{
	public $Type = ObjectType::NULL;

	public function Inspect() : string
	{
		return "null";
	}
}

// MonkeyReturnValue is a wrapper around another Monkey object.
class MonkeyReturnValue implements IMonkeyObject
{
	public $Type = ObjectType::RETURNVALUE;
	public $Value; //IMonkeyObject
	public function Inspect() : string
	{
		return $this->Value->Inspect();
	}
}

// MonkeyError is a simple class which wraps a string error message. In a
// production language, we'd want to attach stack trace and line and column
// number to such error object.
class MonkeyError implements IMonkeyObject
{
	public $Type = ObjectType::ERROR;
	public $Message;
	public function Inspect() : string
	{
		return "Error: {$this->Message}";
	}
}

class MonkeyFunction implements IMonkeyObject
{
	public $Type = ObjectType::FUNCTION;

	public $Parameters = array();
	public $Body; //BlockStatement

	// Functions in Monkey carry their own environment. This allows for
	// closures which "close over" the environment they're defined in and
	// allows the function to later access values within the closure.
	public $Env; //MonkeyEnvironment

	public function Inspect() : string
	{
		$sb = "";
		$parameters = array();
		foreach ($this->Parameters as $p)
			$parameters[] = $p->String();                

		$sb .= "fn";
		$sb .= "(";
		$sb .= implode(", ", $parameters);
		$sb .= ") {\n";
		$sb .= $this->Body->String;
		$sb .= "\n}";
		return $sb;
	}
}

class MonkeyString implements IMonkeyObject, IHashable
{
	public $Value; //string
	public function __construct($val = null)
	{
		if( $val !== null)
		{
			$this->Value = $val;
		}
	}
	public function Inspect() : string
	{
		return $this->Value;
	}
	public $Type = ObjectType::STRING;

	public function HashKey() : HashKey
	{
		$HashKey = new HashKey(); 
		$HashKey->Type = $this->Type;
		$HashKey->Value = md5($this->Value);
		return $HashKey;
	}
}

class MonkeyField implements IMonkeyObject, IHashable
{
	public $Value; //string
	public function __construct($val = null)
	{
		if( $val !== null)
		{
			$this->Value = $val;
		}
	}
	public function Inspect() : string
	{
		return $this->Value ?? '';
	}
	public $Type = ObjectType::FIELD;

	public function HashKey() : HashKey
	{
		$HashKey = new HashKey(); 
		$HashKey->Type = $this->Type;
		$HashKey->Value = md5($this->Value);
		return $HashKey;
	}
}

class MonkeyCSharp implements IMonkeyObject
{
	public $Value; //string
	public function Inspect() : string
	{
		return $this->Value;
	}
	public $Type = ObjectType::CSHARP;
}

class MonkeyBuiltin implements IMonkeyObject
{
	public $Type = ObjectType::BUILTIN;
	public $Fn; //BuiltinFunction
    public function __construct($fn = null)
    {
        $this->Fn = $fn;
    }
	public function Inspect() : string
	{
		return "builtin function";
	}
}

class MonkeyArray implements IMonkeyObject
{
	public $Type = ObjectType::ARRAY;

	public $Elements = array(); //List<IMonkeyObject> 

	public function Inspect() : string
	{
		$sb = '';
		$elements = array();

		foreach ($this->Elements as $e)
			$elements[] = $e->Inspect();

		$sb .= "[";
		$sb .= implode(", ", $elements);
		$sb .= "]";
		return $sb;
	}
}

class HashPair
{
	public $Key ; //IMonkeyObject
	public $Value; //IMonkeyObject
}

class MonkeyHash implements IMonkeyObject
{
	public $Type = ObjectType::HASH;

	public $Pairs; //Dictionary<HashKey, HashPair>

	public function Inspect() : string
	{
		$sb = '';
		$pairs = array();

		foreach ($this->Pairs as $key => $value)
			$pairs[] = "{" . $key->Inspect() . ' : {' . $value->Inspect() . ' }';

		$sb .= "{";
		$sb .= implode(", ", $pairs);
		$sb .= "}";
		return $sb;            
	}
}
