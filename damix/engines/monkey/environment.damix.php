<?php
/*
* Copyright : https://github.com/ronnieholm/Monkey-CSharp
*/
declare(strict_types=1);
namespace damix\engines\monkey;

// We call it MonkeyEnvironment to avoid confusion with System.Environment.
class MonkeyEnvironment
{
	public $Store; //Dictionary<string, IMonkeyObject>
	public $Outer; //MonkeyEnvironment

	public function __construct()
	{
		$this->Store = array();
	}

	public static function NewEnvironment() : MonkeyEnvironment
	{
		$me = new MonkeyEnvironment();
		$me->Outer = null;
		return $me;
	}

	public static function NewEnclosedEnvironment(MonkeyEnvironment $outer) : MonkeyEnvironment
	{
		$env = MonkeyEnvironment::NewEnvironment();
		$env->Outer = $outer;
		return $env;
	}

	public function Get(string $name)//(IMonkeyObject, bool)
	{
		$name = strtolower($name);
		$ok = isset( $this->Store[$name] );
		$value = null;
		if( $ok )
		{
			$value = $this->Store[$name];
		}

		// If the current environment doesn't have a value associated with
		// the name, we recursively call Get on the enclosing environment
		// (which the current environment is extending) until either name is
		// found or we can issue a "ERROR: Unknown identifier: foobar"
		// message.
		if (!$ok && $this->Outer != null)
			return $this->Outer->Get($name);

		return array($value, $ok);
	}

	public function Set(string $name, IMonkeyObject $val):IMonkeyObject
	{
		$name = strtolower($name);
		$this->Store[$name] = $val;
		return $val;
	}
}
