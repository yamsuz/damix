<?php
/*
* Copyright : https://github.com/ronnieholm/Monkey-CSharp
*/
declare(strict_types=1);
namespace damix\engines\monkey;

Evaluator::$True = new MonkeyBoolean(true); //MonkeyBoolean
Evaluator::$False = new MonkeyBoolean(false); //MonkeyBoolean
Evaluator::$Null = new MonkeyNull(); //MonkeyBoolean
class Evaluator
{
	// There's only ever a need for one instance of these values so as an
	// optimization we create a single instance of each to return during
	// evaluation.
	public static $True; //MonkeyBoolean
	public static $False; //MonkeyBoolean
	public static $Null; //MonkeyNull

	public static function Eval(INode $node, MonkeyEnvironment $env) 
	{
		$class = get_class($node);
		
		switch ($class)
		{
			// Statements
			case 'damix\engines\monkey\Program':
				return Evaluator::EvalProgram($node->Statements, $env);
			case 'damix\engines\monkey\ExpressionStatement':
				return Evaluator::Eval($node->Expression, $env);
			case 'damix\engines\monkey\BlockStatement':
				return Evaluator::EvalBlockStatement($node->Statements, $env);
			case 'damix\engines\monkey\ReturnStatement':
				$val = Evaluator::Eval($node->ReturnValue, $env);

				// Check for errors whenever Eval is called inside Eval in
				// order to stop errors from being passed around and
				// bubbling up far from their origin.
				if (Evaluator::IsError($val))
					return $val;
				$v = new MonkeyReturnValue();
				$v->Value = $val;
				return $v;
			case 'damix\engines\monkey\LetStatement':
				$val = Evaluator::Eval($node->Value, $env);
				if (Evaluator::IsError($val))
					return $val;
				return $env->Set($node->Name->Value, $val);
			// Expressions
			case 'damix\engines\monkey\IntegerLiteral':
				$v = new MonkeyInteger();
				$v->Value = $node->Value;
				return $v;
			case 'damix\engines\monkey\Boolean_':
				return Evaluator::NativeBoolToBooleanObject($node->Value);
			case 'damix\engines\monkey\PrefixExpression':
				$right = Evaluator::Eval($node->Right, $env);
				if (Evaluator::IsError($right))
					return $right;
				return Evaluator::EvalPrefixExpression($node->Operator, $right);
			case 'damix\engines\monkey\InfixExpression':
				$left = Evaluator::Eval($node->Left, $env);
				if (Evaluator::IsError($left))
					return $left;

				$right = Evaluator::Eval($node->Right, $env);
				if (Evaluator::IsError($right))
					return $right;

				return Evaluator::EvalInfixExpression($node->Operator, $left, $right);
			case 'damix\engines\monkey\IfExpression':
				return Evaluator::EvalIfExpression($node, $env);
			case 'damix\engines\monkey\Identifier':
				return Evaluator::EvalIdentifier($node, $env);
			case 'damix\engines\monkey\FunctionLiteral':
				$parameters = $node->Parameters;   
				$body = $node->Body;
				$v = new MonkeyFunction();
				$v->Parameters = $parameters;
				$v->Env = $env;
				$v->Body = $node->Body;
				return $v;
			case 'damix\engines\monkey\CallExpression':
				$function = Evaluator::Eval($node->Function, $env);
				if (Evaluator::IsError($function))
					return $function;
				
				$args = Evaluator::EvalExpressions($node->Arguments, $env);
				if (count($args) == 1 && Evaluator::IsError($args[0]))
					return $args[0];

				return Evaluator::ApplyFunction($function, $args);
			case 'damix\engines\monkey\ArrayLiteral':
				$elements = Evaluator::EvalExpressions($node->Elements, $env);
				if (count($elements) == 1 && Evaluator::IsError($elements[0]))
					return $elements[0];

				$v = new MonkeyArray();
				$v->Elements = $elements;
				return $v;
			case 'damix\engines\monkey\IndexExpression':
				$left = Evaluator::Eval($node->Left, $env);
				if (Evaluator::IsError($left))
					return $left;

				$index = Evaluator::Eval($node->Index, $env);
				if (Evaluator::IsError($index))
					return $index;

				return Evaluator::EvalIndexExpression($left, $index);
			case 'damix\engines\monkey\StringLiteral':
				$v = new MonkeyString();
				$v->Value = $node->Value;
				return $v;
			case 'damix\engines\monkey\HashLiteral':
				return Evaluator::EvalHashLiteral($node, $env);
			default:
				throw new \Exception("Invalid node type: get_class($node)");                
		}
	}

	// Helper used within Evaluator and MonkeyBuiltins which is why it's
	// public and static.
	public static function NewError(string $message) 
	{
		$v = new MonkeyError();
		$v->Message = $message;
		return $v;
	}

	private static function EvalProgram(array $statements, MonkeyEnvironment $env) 
	{
		$result = null; //IMonkeyObject
		foreach ($statements as $stmt)
		{
			$result = Evaluator::Eval($stmt, $env);

			// Prevents further evaluation if the result of the evaluation
			// is a return statement. Note how we don't return MReturnValue
			// directly, but unwraps its value. The MReturnValue is an
			// internal detail to allow Eval() to signal to its caller that
			// it encountered and evaluated a return statement.
			if ($result instanceof \damix\engines\monkey\MonkeyReturnValue)
				return $result->Value;
			else if ($result instanceof \damix\engines\monkey\MonkeyError)
				return $result;
		}
		return $result;
	}

	private static function EvalBlockStatement(array $statements, MonkeyEnvironment $env) 
	{
		$result = null;//IMonkeyObject
		foreach ($statements as $stmt)
		{
			$result = Evaluator::Eval($stmt, $env);
			$rt = $result->Type;
			if ($rt == ObjectType::RETURNVALUE || $rt == ObjectType::ERROR)
			{
				// Compared to EvalProgram()), we don't unwrap the return
				// value. Instead when an ReturnValueObj is encountered as
				// the result of evaluating a statement, we return it to
				// EvalProgram() for unwrapping. This makes evaluation stop
				// in a possibly outer block statement and bubbles up the
				// result.
				return $result;
			}
		}
		return $result;
	}

	private static function EvalPrefixExpression(string $op, IMonkeyObject $right) 
	{
		switch ($op)
		{
			case "!": 
				return Evaluator::EvalBangOperatorExpression($right);
			case "-":
				return Evaluator::EvalMinusPrefixOperatorExpression($right);
			default: 
				return Evaluator::NewError("Unknown operator: {$op}{$right->Type}");
		}
	}

	private static function EvalBangOperatorExpression(IMonkeyObject $right) 
	{
		if ($right == Evaluator::$True)
			return Evaluator::$False;
		else if ($right == Evaluator::$False)
			return Evaluator::$True;
		else if ($right == Evaluator::$Null)
			return Evaluator::$True;

		return Evaluator::$False;
	}

	private static function EvalMinusPrefixOperatorExpression(IMonkeyObject $right)
	{
		if ($right->Type != ObjectType::INTEGER)
			return Evaluator::NewError("Unknown operator: -{$right->Type}");

		$value = $right->Value;
		$v = new MonkeyInteger();
		$v->Value = -$value;
		return $v;
	}

	private static function EvalInfixExpression(string $op, IMonkeyObject $left, IMonkeyObject $right)
	{
		if ($left->Type == ObjectType::INTEGER && $right->Type == ObjectType::INTEGER)
			return Evaluator::EvalIntegerInfixExpression($op, $left, $right);
		else if ($left->Type == ObjectType::STRING && $right->Type == ObjectType::STRING)
			return Evaluator::EvalStringInfixExpression($op, $left, $right);
		// Observe how for MonkeyBooleans we use reference comparison to
		// check for equality. This works only because the references are to
		// our singleton True and False instances. This wouldn't work for
		// MonkeyIntegers since those aren't singletons. 5 == 5 would be
		// false with reference equals. To compare MonkeyIntegers we must
		// unwrap the integers stored inside the MonkeyIntegers and compare
		// their values.
		else if ($op == "==")
			return $tihs->NativeBoolToBooleanObject($left == $right);
		else if ($op == "!=")
			return Evaluator::NativeBoolToBooleanObject($left != $right);
		else if ($left->Type != $right->Type)
			return Evaluator::NewError("Type mismatch: {$left->Type} {$op} {$right->Type}");

		return Evaluator::NewError("Unknown operator: {$left->Type} {$op} {$right->Type}");
	}

	private static function EvalIntegerInfixExpression(string $op, IMonkeyObject $left, IMonkeyObject $right)
	{
		$leftVal = $left->Value;
		$rightVal = $right->Value;
		
		switch ($op)
		{
			case "+":
				$v = new MonkeyInteger();
				$v->Value = $leftVal + $rightVal;
				return $v;
			case "-":
				$v = new MonkeyInteger();
				$v->Value = $leftVal - $rightVal;
				return $v;
			case "*":
				$v = new MonkeyInteger();
				$v->Value = $leftVal * $rightVal;
				return $v;
			case "/":
				$v = new MonkeyInteger();
				$v->Value = $leftVal / $rightVal;
				return $v;
			case "<":
				return Evaluator::NativeBoolToBooleanObject($leftVal < $rightVal);
			case ">":
				return Evaluator::NativeBoolToBooleanObject($leftVal > $rightVal);
			case "==":
				return Evaluator::NativeBoolToBooleanObject($leftVal == $rightVal);
			case "!=":
				return Evaluator::NativeBoolToBooleanObject($leftVal != $rightVal);
			default:
				return Evaluator::NewError("Unknown operator: {$left->Type} {$op} {$right->Type}");
		}
	}

	private static function EvalStringInfixExpression(string $op, IMonkeyObject $left, IMonkeyObject $right)
	{
		if ($op != "+")
			return NewError("Unknown operator: {$left->Type} {$op} {$right->Type}");

		$leftVal = $left->Value;
		$rightVal = $right->Value;          
		$v = new MonkeyString();
		$v->Value = $leftVal + $rightVal;
		return $v;
	}

	private static function EvalIfExpression(IfExpression $ie, MonkeyEnvironment $env)
	{
		$condition = Evaluator::Eval($ie->Condition, $env);
		if (Evaluator::IsError($condition))
			return $condition;
		elseif (Evaluator::IsTruthy($condition))
			return Evaluator::Eval($ie->Consequence, $env);
		elseif ($ie->Alternative != null)
			return Evaluator::Eval($ie->Alternative, $env);

		return $Null;
	}

	private static function IsTruthy(IMonkeyObject $obj) 
	{
		if ($obj == Evaluator::$Null)
			return false;
		elseif ($obj == Evaluator::$True)
			return true;
		elseif ($obj == Evaluator::$False)
			return false;

		return true;
	} 

	private static function NativeBoolToBooleanObject(bool $input)
	{
		return $input ? Evaluator::$True : Evaluator::$False;
	}

	private static function IsError(IMonkeyObject $obj) : bool 
	{
		return $obj != null ? $obj->Type == ObjectType::ERROR : false;
	}

	private static function EvalIdentifier(Identifier $node, MonkeyEnvironment $env)
	{
		$result = $env->Get($node->Value);
		if ($result[1])
			return $result[0];

		if( isset( MonkeyBuiltins::$Builtins[ $node->Value ] ) )
		{
			return MonkeyBuiltins::$Builtins[ $node->Value ];
		}
		
		return Evaluator::NewError("Identifier not found: {$node->Value}");
	}

	private static function EvalExpressions(?array $exps, MonkeyEnvironment $env)
	{
		$result = array();

		// Observe how, by definition, the arguments are evaluated left to
		// right. Since the side effect of evaluating one argument might be
		// relied on during the evaluation of the next, defining an explicit
		// evaluation order is important.
        if( $exps )
        {
            foreach ($exps as $e)
            {
                $evaluated = Evaluator::Eval($e, $env);
                if (Evaluator::IsError($evaluated))
                    return array( $evaluated );
                $result[] = $evaluated;
            }
        }

		return $result;
	}

	private static function ApplyFunction(IMonkeyObject $fn, array $args) 
	{
		if ($fn instanceof MonkeyFunction)
		{
			$extendedEnv = Evaluator::ExtendFunctionEnv($fn, $args);
			$evaluated = Evaluator::Eval($fn->Body, $extendedEnv);
			return Evaluator::UnwrapReturnValue($evaluated);
		}
		elseif ($fn instanceof MonkeyBuiltin)
		{
			return call_user_func_array($fn->Fn, array($args));
			// return $fn->Fn($args);
		}
		else
			return Evaluator::NewError("Not a function: {$fn->Type}");
	}

	private static function ExtendFunctionEnv(MonkeyFunction $fn, array $args) 
	{
		$env = MonkeyEnvironment::NewEnclosedEnvironment($fn->Env);
		foreach( $fn->Parameters as $i => $param )
		{
			$env->Set($param->Value, $args[$i]);
		}
		
		return $env;
	}

	private static function UnwrapReturnValue(IMonkeyObject $obj) 
	{
		// Unwrapping is necessary because otherwise a return statement
		// would bubble up through several functions and stop the evaluation
		// in all of them. We only want to stop the evaluation of the last
		// called function's body. Otherwise, EvalBlockStatement() would
		// stop evaluating statements in the "outer" functions.
		if ($obj instanceof MonkeyReturnValue)
			return $obj->Value;
		return $obj;
	}

	private static function EvalIndexExpression(IMonkeyObject $left, IMonkeyObject $index) 
	{
		if ($left->Type == ObjectType::ARRAY && $index->Type == ObjectType::INTEGER)
			return Evaluator::EvalArrayIndexExpression($left, $index);
		else if ($left->Type == ObjectType::HASH)
			return Evaluator::EvalHashIndexExpression($left, $index);
		return Evaluator::NewError("Index operator not supported {$left->Type}");
	}

	private static function EvalArrayIndexExpression(IMonkeyObject $array, IMonkeyObject $index) 
	{
		$arrayObject = $array;
		$idx = $index->Value;
		$max = count($arrayObject->Elements) - 1;

		if ($idx < 0 || $idx > $max)
		{
			// Some languages throw an exception when the index is out of
			// bounds. In Monkey by definition we return null as the result.
			return Evaluator::$Null;
		}            
		return $arrayObject->Elements[$idx];
	}

	public static function EvalHashIndexExpression(IMonkeyObject $hash, IMonkeyObject $index)
	{
		$hashObject = $hash;
		if ($index instanceof IHashable)
		{
			
			$ok = isset( $hashObject->Pairs[$index->HashKey()] );
			
			if (!$ok)
				return Evaluator::$Null;

			return $hashObject->Pairs[$index->HashKey()]->Value;
		}
		else
			return Evaluator::NewError("Unusable as hash key: {$index->Type}");
	}

	private static function EvalHashLiteral(HashLiteral $node, MonkeyEnvironment $env) 
	{
		$pairs = array(); //new Dictionary<HashKey, HashPair>();

		foreach($node->Pairs as $kv)
		{
			$key = Evaluator::Eval($kv->Key, $env);
			if (Evaluator::IsError($key))
				return $key;

			if ($key instanceof IHashable)
			{
				$value = Evaluator::Eval($kv->Value, $env);
				if (Evaluator::IsError($value))
					return $value;

				$hashKey = $key->HashKey();
				$hashPair = new HashPair();
				$hashPair->Key = $key;
				$hashPair->Value = $value;
				$pairs[ $hashKey ] = $hashPair;
			}
			else
				return Evaluator::NewError("Unusable as hash key: {$key->GetType()}");
		}

		$v = new MonkeyHash();
		$v->Pairs = $pairs;
		return $v;
	}
}
