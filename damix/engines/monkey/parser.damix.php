<?php
/*
* Copyright : https://github.com/ronnieholm/Monkey-CSharp
*/
declare(strict_types=1);
// Tip: Setting a breakpoint in one of the parsing methods and inspecting the
// call stack when its hit, will effectively show the Abstract Syntax Tree at
// that point during parsing.

namespace damix\engines\monkey;

// using PrefixParseFn = Func<IExpression>;
// using InfixParseFn = Func<IExpression, IExpression>;

// In order to view output written to stdout, either run the tests from the
// command line with "dotnet test Monkey.Tests", use the xUnit GUI runner,
// or in VSCode run tests through the .NET Test Explorer plugin. Running
// tests from within the VSCode editor's "run test" or "debug test" links,
// stdout isn't redirected to the VSCode's Output tab.
class ParserTracer
{
	const TraceIdentPlaceholder = '&nbsp;&nbsp;&nbsp;&nbsp;';
	static $traceLevel = 0;
	private $_withTracing = false;//bool

	private function IncIdent()
	{
		ParserTracer::$traceLevel++;
	}
	private function DecIdent()
	{
		ParserTracer::$traceLevel--;
	}
	private function IdentLevel() : string
	{
		$s = '';
		for($i = 0; $i < ParserTracer::$traceLevel; $i++)
		{
			$s .= ParserTracer::TraceIdentPlaceholder;
		}
		return $s;
	}
	private function TracePrint(string $message) 
	{
		if ($this->_withTracing)
			print "{$this->IdentLevel()}{$message}<br/>";
	}

	public function ParserTracer(bool $withTracing)
	{
		$this->_withTracing = $withTracing;
	}
	public function Trace(string $message)
	{
		$this->TracePrint("BEGIN {$message}");
		$this->IncIdent();
	}
	public function Untrace(string $message)
	{
		$this->DecIdent();
		$this->TracePrint("END {$message}");
	}
}

// Actual numeric numbers doesn't matter, but the order and the relation
// to each other does. We want to be able to answer questions such as
// whether operator * has higher precedence than operator ==. While
// using an enum over a class with integer constants alliviates the need
// to explicitly assign a value to each member, it making debugging the
// Pratt parser slightly more difficult. During precedence value
// comparisons, the debugger will show the strings over their its
// implicit number.
class PrecedenceLevel
{
	const NONE = 0;
	const LOWEST = 1;
	const EQUALS = 2;         // ==
	const LESSGREATER = 3;    // > or <
	const SUM = 4;            // +
	const PRODUCT = 5;        // *
	const PREFIX = 6;         // -x or !x
	const CALL = 7;          // myFunction(x)
	const INDEX = 8;          // array[index]
}

class Parser
{
	private $_lexer; //Lexer

	// For visualizing and debugging the Pratt expression parser for Monkey
	// expressions.
	private $_tracer; //ParserTracer

	// Acts like _position and PeekChar within the lexer, but instead of
	// pointing to a character in the input they point to the current and
	// next tokens. We need to look at _curToken, the current token under
	// examination, to decide what to do next, and we need _peekToken to
	// guide the decision in case _curToken doesn't provide us with enough
	// information, e.g., with the input "5;", then _curToken is Int and we
	// require _peekToken to decide if where're at the end of the line or if
	// we're at the start of an arithmetic expression. In effect, this
	// implements a parser with one token lookahead.
	private $_curToken;//Token
	private $_peekToken; //Token

	public $Errors = array(); //List<string>

	// Functions based on token type called as part of Pratt parsing.
	private $_prefixParseFns; //Dictionary<TokenType, PrefixParseFn>
	private $_infixParseFns; //Dictionary<TokenType, InfixParseFn>

	

	// Table of precedence associated with token. Observe how not every
	// precedence value is present (Lowest and Prefix missing) and some are
	// levels are appear more than once (LessGreater, Sum, Product). Lowest
	// serves as a starting precedence for the Pratt parser while Prefix
	// isn't associated with a token but an expression as a whole. On the
	// other hand, some operators, such as multiplication and division,
	// share the same precedence level.
	private $precedences = array( //Dictionary<TokenType, PrecedenceLevel>
		TokenType::EQ 			=> PrecedenceLevel::EQUALS,
		TokenType::NOTEQ 		=> PrecedenceLevel::EQUALS,
		TokenType::LT 			=> PrecedenceLevel::LESSGREATER,
		TokenType::GT 			=> PrecedenceLevel::LESSGREATER,
		TokenType::PLUS 		=> PrecedenceLevel::SUM,
		TokenType::MINUS 		=> PrecedenceLevel::SUM,
		TokenType::SLASH 		=> PrecedenceLevel::PRODUCT,
		TokenType::ASTERISK 	=> PrecedenceLevel::PRODUCT,
		TokenType::LPAREN 		=> PrecedenceLevel::CALL,
		TokenType::LBRACKET 	=> PrecedenceLevel::INDEX,            
	);

	private function RegisterPrefix(int $t, string $fn)
	{
		$this->_prefixParseFns[$t] =  $fn;
	}
	private function RegisterInfix(int $t, string $fn)
	{
		$this->_infixParseFns[$t] = $fn;
	}

	public function __construct(Lexer $lexer, bool $withTracing)
	{
		$this->_lexer = $lexer;
		$this->_tracer = new ParserTracer($withTracing);
		$this->Errors = array();

		$this->_prefixParseFns = array();
		$this->RegisterPrefix(TokenType::IDENT, 'ParseIdentifier');
		$this->RegisterPrefix(TokenType::INT, 'ParseIntegerLiteral');
		$this->RegisterPrefix(TokenType::BANG, 'ParsePrefixExpression');
		$this->RegisterPrefix(TokenType::MINUS, 'ParsePrefixExpression');
		$this->RegisterPrefix(TokenType::TRUE, 'ParseBoolean');
		$this->RegisterPrefix(TokenType::FALSE, 'ParseBoolean');
		$this->RegisterPrefix(TokenType::LPAREN, 'ParseGroupedExpression');
		$this->RegisterPrefix(TokenType::IF, 'ParseIfExpression');
		$this->RegisterPrefix(TokenType::FUNCTION, 'ParseFunctionLiteral');
		$this->RegisterPrefix(TokenType::STRING, 'ParseStringLiteral');
		$this->RegisterPrefix(TokenType::LBRACKET, 'ParseArrayLiteral');
		$this->RegisterPrefix(TokenType::LBRACE, 'ParseHashLiteral');

		$this->_infixParseFns = array();
		$this->RegisterInfix(TokenType::PLUS, 'ParseInfixExpression');
		$this->RegisterInfix(TokenType::MINUS, 'ParseInfixExpression');
		$this->RegisterInfix(TokenType::SLASH, 'ParseInfixExpression');
		$this->RegisterInfix(TokenType::ASTERISK, 'ParseInfixExpression');
		$this->RegisterInfix(TokenType::EQ, 'ParseInfixExpression');
		$this->RegisterInfix(TokenType::NOTEQ, 'ParseInfixExpression');
		$this->RegisterInfix(TokenType::LT, 'ParseInfixExpression');
		$this->RegisterInfix(TokenType::GT, 'ParseInfixExpression');
		$this->RegisterInfix(TokenType::LPAREN, 'ParseCallExpression');
		$this->RegisterInfix(TokenType::LBRACKET, 'ParseIndexExpression');

		// Read two tokens so _curToken and _peekToken tokens are both set.
		$this->NextToken();
		$this->NextToken();
	}

	public function ParseProgram() : Program
	{
		$p = new Program();
		$p->Statements = array();

		while (!$this->CurTokenIs(TokenType::EOF))
		{
			$s = $this->ParseStatement();
			if ($s != null)
				$p->Statements[] = $s;
			$this->NextToken();
		}
		return $p;
	}

	private function NextToken()
	{
		$this->_curToken = $this->_peekToken;
		$this->_peekToken = $this->_lexer->NextToken();
	}

	private function ParseStatement() : IStatement
	{
		switch ($this->_curToken->Type)
		{
			case TokenType::LET:
				return $this->ParseLetStatement();
			case TokenType::RETURN:
				return $this->ParseReturnStatement();
			default:
				// The only two real statement types in Monkey are let and
				// return. If none of those matched, try to parse input as
				// pseudo ExpressionStatement.
				return $this->ParseExpressionStatement();
		}
	}

	private function ParseLetStatement() : LetStatement
	{
		$stmt = new LetStatement();
		$stmt->Token = $this->_curToken;
		if (!$this->ExpectPeek(TokenType::IDENT))
			return null;

		$stmt->Name = new Identifier();
		$stmt->Name->Token = $this->_curToken;
		$stmt->Name->Value = $this->_curToken->Literal;
		if (!$this->ExpectPeek(TokenType::ASSIGN))
			return null;

		$this->NextToken();
		$stmt->Value = $this->ParseExpression(PrecedenceLevel::LOWEST);
		if ($this->PeekTokenIs(TokenType::SEMICOLON))
		{
			$this->NextToken();
		}

		return $stmt;
	}

	private function ParseReturnStatement() : ReturnStatement
	{
		$stmt = new ReturnStatement();
		$stmt->Token = $this->_curToken;

		$this->NextToken();
		$stmt->ReturnValue = $this->ParseExpression(PrecedenceLevel::LOWEST);

		if ($this->PeekTokenIs(TokenType::SEMICOLON))
			$this->NextToken();
		return $stmt;
	}

	private function ParseExpressionStatement() : ExpressionStatement
	{
		$this->_tracer->Trace("ParseExpressionStatement");
		$stmt = new ExpressionStatement();
		$stmt->Token = $this->_curToken;

		// Pass in lowest precedence since we haven't parsed anything yet.
		$stmt->Expression = $this->ParseExpression(PrecedenceLevel::LOWEST);

		// Expression statements end with optional semicolon.
		if ($this->PeekTokenIs(TokenType::SEMICOLON))
		{
			$this->NextToken();
		}
		$this->_tracer->Untrace("ParseExpressionStatement");
		return $stmt;
	}

	private function ParseExpression(int $precedence)
	{
		$this->_tracer->Trace("ParseExpression");
		$ok = isset( $this->_prefixParseFns[ $this->_curToken->Type ] );
		$prefix = null;
		if( $ok )
		{
			$prefix = $this->_prefixParseFns[ $this->_curToken->Type ];
		}
		else
		{
			$this->NoPrefixParseFnError( $this->_curToken->Type);
			return null;
		}
		$leftExpr = $this->$prefix();

		// The precedence is what the original Pratt paper refers to as
		// right-binding power and PeekPrecedence() is what it refers to as
		// left-binding power. For as long as left-binding power >
		// right-binding power, we're going to add another level to the
		// Abstract Syntax Three, signifying operations which need to be
		// carried out first when the expression is evaluated.
		while (! $this->PeekTokenIs(TokenType::SEMICOLON) && $precedence < $this->PeekPrecedence())
		{
			$ok = isset( $this->_infixParseFns[ $this->_peekToken->Type]);
			$infix = null;
			if( $ok )
			{
				$infix = $this->_infixParseFns[ $this->_peekToken->Type];
			}
			if (!$ok)
				return $leftExpr;

			$this->NextToken();
			$leftExpr = $this->$infix($leftExpr);
		}
		$this->_tracer->Untrace("ParseExpression");

		return $leftExpr;
	}

	private function CurTokenIs(int $t) : bool
	{
		return $this->_curToken->Type == $t;
	}

	private function PeekTokenIs(int $t) : bool
	{
		return $this->_peekToken->Type == $t;
	}

	private function ExpectPeek(int $t) : bool
	{
		if ($this->PeekTokenIs($t))
		{
			$this->NextToken();
			return true;
		}

		$this->PeekError($t);
		return false;
	}

	private function PeekError(int $t)
	{
		$this->Errors[] = "Expected next token to be {$t}, got {$this->_peekToken->Type} instead.";
	}

	private function ParseIdentifier() : ?IExpression
	{
		$i = new Identifier();
		$i->Token = $this->_curToken;
		$i->Value = $this->_curToken->Literal;
		return $i;
	}

	private function ParseIntegerLiteral() : ?IExpression
	{
		$this->_tracer->Trace("ParseIntegerLiteral");
		$lit = new IntegerLiteral();
		$lit->Token = $this->_curToken;

		$value = intval($this->_curToken->Literal);
		if ($value != $this->_curToken->Literal)
		{
			$this->Errors[] = "Could not parse '{$this->_curToken->Literal}' as integer";
			return null;
		}
		$lit->Value = $value;
		$this->_tracer->Untrace("ParseIntegerLiteral");
		return $lit;
	}

	private function ParseBoolean() : ?IExpression
	{
		$b = new Boolean_();
		$b->Token = $this->_curToken;
		$b->Value = $this->CurTokenIs(TokenType::TRUE);
		return $b;
	}

	private function ParsePrefixExpression() : ?IExpression
	{
		$this->_tracer->Trace("ParsePrefixExpression");
		$expr = new PrefixExpression();
		$expr->Token = $this->_curToken;
		$expr->Operator = $this->_curToken->Literal;
		$this->NextToken();
		$expr->Right = $this->ParseExpression(PrecedenceLevel::PREFIX);
		$this->_tracer->Untrace("ParsePrefixExpression");
		return $expr;
	}

	private function NoPrefixParseFnError(int $type)
	{
		$this->Errors[] = "No prefix parse function for {$type} found";
	}

	private function ParseInfixExpression(IExpression $left) : ?IExpression
	{
		$this->_tracer->Trace("ParseInfixExpression");
		$expr = new InfixExpression();
		$expr->Token = $this->_curToken;
		$expr->Operator = $this->_curToken->Literal;
		$expr->Left = $left;
		$p = $this->CurPrecedence();
		$this->NextToken();
		$expr->Right = $this->ParseExpression($p);
		$this->_tracer->Untrace("ParseInfixExpression");
		return $expr;
	}

	private function ParseCallExpression(IExpression $function) : ?IExpression
	{
		$expr = new CallExpression();
		$expr->Token = $this->_curToken;
		$expr->Function = $function;
		$expr->Arguments = $this->ParseExpressionList(TokenType::RPAREN);
		return $expr;
	}

	private function ParseIndexExpression(IExpression $left) : ?IExpression
	{
		$expr = new IndexExpression();
		$expr->Token = $this->_curToken;
		$expr->Left = $left;

		$this->NextToken();
		$expr->Index = $this->ParseExpression(PrecedenceLevel::LOWEST);

		// BUG: Attempting to parse "{}[""foo""" with a missing ] causes
		// null to be returned. The null is passed to Eval() for evaluation
		// but since no node type is defined for null, we end up in Eval()'s
		// default case which throws an Exception. In the process of
		// throwing this exception it itself throws a NullReferenceException
		// because node in "throw new Exception($"Invalid node type:
		// {node.GetType()}");" is null.
		if (!$this->ExpectPeek(TokenType::RBRACKET))
			return null;
		return $expr;
	}

	private function ParseGroupedExpression() : ?IExpression
	{
		$this->NextToken();
		$expr = $this->ParseExpression(PrecedenceLevel::LOWEST);
		return !$this->ExpectPeek(TokenType::RPAREN) ? null : $expr;
	}

	private function ParseIfExpression() : ?IExpression
	{
		$expression = new IfExpression();
		$expression->Token = $this->_curToken;

		if (!$this->ExpectPeek(TokenType::LPAREN))
			return null;

		$this->NextToken();
		$expression->Condition = $this->ParseExpression(PrecedenceLevel::LOWEST);

		if (!$this->ExpectPeek(TokenType::RPAREN))
			return null;

		if (!$this->ExpectPeek(TokenType::LBRACE))
			return null;

		$expression->Consequence = $this->ParseBlockStatement();

		if ($this->PeekTokenIs(TokenType::ELSE))
		{
			$this->NextToken();
			if (!$this->ExpectPeek(TokenType::LBRACE))
				return null;

			$expression->Alternative = $this->ParseBlockStatement();
		}

		return $expression;
	}

	private function ParseBlockStatement() : ?BlockStatement
	{
		$block = new BlockStatement();
		$block->Token = $this->_curToken;
		$block->Statements = array();

		$this->NextToken();

		// BUG: If '}' is missing from the program, this code goes into an
		// infinite loop.
		while (!$this->CurTokenIs(TokenType::RBRACE))
		{
			$stmt = $this->ParseStatement();
			if ($stmt != null)
				$block->Statements[] = $stmt;
			$this->NextToken();
		}
		return $block;
	}

	private function ParseFunctionLiteral() : ?IExpression
	{
		$lit = new FunctionLiteral();
		$lit->Token = $this->_curToken;

		if (!$this->ExpectPeek(TokenType::LPAREN))
			return null;

		$lit->Parameters = $this->ParseFunctionParameters();

		if (!$this->ExpectPeek(TokenType::LBRACE))
			return null;

		$lit->Body = $this->ParseBlockStatement();
		return $lit;
	}

	private function ParseFunctionParameters() : ?array //List<Identifier>
	{
		$identifiers = array();
		if ($this->PeekTokenIs(TokenType::RPAREN))
		{
			$this->NextToken();
			return $identifiers;
		}

		$this->NextToken();
		$ident = new Identifier();
		$ident->Token = $this->_curToken;
		$ident->Value = $this->_curToken->Literal;
		$identifiers[] = $ident;

		while ($this->PeekTokenIs(TokenType::COMMA))
		{
			$this->NextToken();
			$this->NextToken();
			$ident = new Identifier();
			$ident->Token = $this->_curToken;
			$ident->Value = $this->_curToken->Literal;
			$identifiers[] = $ident;
		}

		if (!$this->ExpectPeek(TokenType::RPAREN))
			return null;

		return $identifiers;
	}

	private function ParseStringLiteral() : ?IExpression
	{
		$s = new StringLiteral();
		$s->Token = $this->_curToken;
		$s->Value = $this->_curToken->Literal;
		return $s;
	}

	private function ParseArrayLiteral() : ?IExpression
	{
		$array = new ArrayLiteral();
		$array->Token = $this->_curToken;
		$array->Elements = $this->ParseExpressionList(TokenType::RBRACKET);
		return $array;
	}

	// Similar to ParseFunctionParameters() expect that it's more generic
	// and returns a list of expression rather than a list of identifiers.
	private function ParseExpressionList(int $end) : ?array //List<IExpression>
	{
		$list = array();

		if ($this->PeekTokenIs($end))
		{
			$this->NextToken();
			return $list;
		}

		$this->NextToken();
		$list[] = $this->ParseExpression(PrecedenceLevel::LOWEST);

		while ($this->PeekTokenIs(TokenType::COMMA))
		{
			$this->NextToken();
			$this->NextToken();
			$list[] = $this->ParseExpression(PrecedenceLevel::LOWEST);
		}

		if (!$this->ExpectPeek($end))
			return null;

		return $list;
	}

	private function ParseHashLiteral() : ?IExpression
	{
		$hash = new HashLiteral();
		$hash->Token = $this->_curToken;
		$hash->Pairs = array(); //new Dictionary<IExpression, IExpression>();
		
		while (!$this->PeekTokenIs(TokenType::RBRACE))
		{
			$this->NextToken();
			$key = $this->ParseExpression(PrecedenceLevel::LOWEST);

			if (!$this->ExpectPeek(TokenType::COLON))
			{
				return null;
			}

			$this->NextToken();
			$value = $this->ParseExpression(PrecedenceLevel::Lowest);
			$hash->Pairs[ $key ] = $value;

			if (!$this->PeekTokenIs(TokenType::RBRACE) && !$this->ExpectPeek(TokenType::COMMA))
				return null;
		}

		if (!$this->ExpectPeek(TokenType::RBRACE))
			return null;

		return $hash;
	}

	private function PeekPrecedence() : int
	{
		$ok = isset( $this->precedences[ $this->_peekToken->Type] );
		$pv = null;
		if( $ok )
		{
			$pv = $this->precedences[ $this->_peekToken->Type];
		}
		

		// Returning Lowest when precedence cannot be found for a token is
		// what enables us to parse for instance grouped expression. The
		// RParen doesn't have an associated precedence, so when Lowest is
		// returned it causes the parser to finish evaluating a
		// subexpression as a whole.
		return $ok ? $pv : PrecedenceLevel::LOWEST;
	}

	private function CurPrecedence() : int
	{
		$ok = isset( $this->precedences[ $this->_curToken->Type ] );
		$pv = null;
		if( $ok )
		{
			$pv = $this->precedences[ $this->_curToken->Type ];
		}
		
		return $ok ? $pv : PrecedenceLevel::LOWEST;
	}
}