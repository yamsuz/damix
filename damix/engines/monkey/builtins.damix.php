<?php
/*
* Copyright : https://github.com/ronnieholm/Monkey-CSharp
*/

declare(strict_types=1);
namespace damix\engines\monkey;

MonkeyBuiltins::$Builtins[ "fetch" ] = new MonkeyBuiltin( array( __NAMESPACE__ . '\MonkeyBuiltins', 'fetch' ) );

class MonkeyBuiltins
{
	static public $Builtins = array();


	// function __construct()
	// {
		//Builtins.Add("len", new MonkeyBuiltin { Fn = Len });
		//Builtins.Add("first", new MonkeyBuiltin { Fn = First });
		//Builtins.Add("last", new MonkeyBuiltin { Fn = Last });
		//Builtins.Add("rest", new MonkeyBuiltin { Fn = Rest });
		//Builtins.Add("push", new MonkeyBuiltin { Fn = Push });
		//Builtins.Add("puts", new MonkeyBuiltin { Fn = Puts });
	// }

	static function fetch(array $args) : IMonkeyObject
	{
		$record = \datatable\drivers\DatatableDriver::$parameters;
		
		if (count($args) != 1)
			return Evaluator::NewError("Wrong number of arguments. Got={count($args)}, want=1");

		if ($args[0] instanceof MonkeyString)
		{
			$col = $args[0]->Value;
			if( isset( $record->$col ) )
			{
				return new MonkeyInteger ( $record->$col );
			}
		}

		return Evaluator::NewError("Argument to 'Fetch' not supported. Got " . $args[0]->Type);
	}
	
	// static function Len(array $args) : IMonkeyObject
	// {
		// if (count($args) != 1)
			// return Evaluator::NewError("Wrong number of arguments. Got={count($args)}, want=1");

		// if ($args[0] instanceof MonkeyString)
			// return new MonkeyInteger ( count($args[0]->Value) );
		// else if ($args[0] instanceof MonkeyArray)
			// return new MonkeyInteger (count($args[0]->Elements) );
		// else
			// return Evaluator::NewError("Argument to 'len' not supported. Got $args[0].Type");
	// }

	// static IMonkeyObject First(List<IMonkeyObject> args)
	// {
		// if (args.Count != 1)
			// return Evaluator::NewError("Wrong number of arguments. Got={args.Count}, want=1");

		// if (args[0] is MonkeyArray arr)
			// return arr.Elements.Count > 0 ? arr.Elements[0] : Evaluator::$Null;
		// else
			// return Evaluator::NewError("Argument to 'first' must be Array. Got {args[0].Type}");
	// }

	// static IMonkeyObject Last(List<IMonkeyObject> args)
	// {
		// if (args.Count != 1)
			// return Evaluator::NewError("Wrong number of arguments. Got={args.Count}, want=1");

		// if (args[0] is MonkeyArray arr)
		// {
			// var length = arr.Elements.Count;
			// return length > 0 ? arr.Elements[length - 1] : Evaluator::$Null;
		// }
		// else
			// return Evaluator.NewError("Argument to 'last' must be Array. Got {args[0].Type}");
	// }

	// static IMonkeyObject Rest(List<IMonkeyObject> args)
	// {
		// if (args.Count != 1)
			// return Evaluator::NewError("Wrong number of arguments. Got={args.Count}, want=1");

		// if (args[0] is MonkeyArray arr)
		// {
			// var length = arr.Elements.Count;
			// if (length > 0)
				// return new MonkeyArray { Elements = arr.Elements.Skip(1).ToList() };

			// return Evaluator::$Null;
		// }
		// else
			// return Evaluator::NewError("Argument to 'last' must be Array. Got {args[0].Type}");
	// }
	
	// static IMonkeyObject Push(List<IMonkeyObject> args)
	// {
		// if (args.Count != 2)
			// return Evaluator::NewError("Wrong number of arguments. Got={args.Count}, want=2");

		// if (args[0] is MonkeyArray arr)
		// {
			// var length = arr.Elements.Count;
			// var newElements = arr.Elements.Skip(0).ToList();
			// newElements.Add(args[1]);
			// return new MonkeyArray { Elements = newElements };
		// }
		// else
			// return Evaluator::NewError("Argument to 'push' must be Array. Got {args[0].Type}");
	// }        

	// static IMonkeyObject Puts(List<IMonkeyObject> args)
	// {
		// foreach (var arg in args)
			// Console.WriteLine(arg.Inspect());

		// return Evaluator::$Null;
	// }
}
