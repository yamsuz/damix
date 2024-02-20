<?php
/*
* Copyright : https://github.com/ronnieholm/Monkey-CSharp
*/
declare(strict_types=1);
namespace damix\engines\monkey;

class TokenType
{
	const ILLEGAL = 1;    // Unknown token/character
	const EOF = 2;        // End of File stops parsing

	// Identifiers and literals
	const IDENT = 3;      // add, foobar, x, y
	const INT = 4;        // 123456
	const STRING = 5;     // "foobar"

	// Operators
	const ASSIGN = 6;     // =
	const PLUS = 7;       // +
	const MINUS = 8;      // -
	const BANG = 9;       // !
	const ASTERISK = 10;   // *
	const SLASH = 11;      // /
	const LT = 12;         // <
	const GT = 13;         // >
	const EQ = 14;         // ==
	const NOTEQ = 15;      // !=

	// Delimiters
	const COMMA = 16;      // ,
	const SEMICOLON = 17;  // ;
	const LPAREN = 18;     // (
	const RPAREN = 19;     // )
	const LBRACE = 20;     // {
	const RBRACE = 21;     // }
	const LBRACKET = 22;   // [
	const RBRACKET = 23;   // ]
	const COLON = 24;      // :

	// Keywords
	const FUNCTION = 25;
	const LET = 26;
	const TRUE = 27;
	const FALSE = 28;
	const IF = 29;
	const ELSE = 30;
	const RETURN = 31;
}

class Token
{
	public $Type; //TokenType
	public $Literal; //string 

	public function __construct(int $type, string $literal)
	{
		$this->Type = $type;
		$this->Literal = $literal;
	}

	public function ToString(): string
	{
		return "({$this->Type},{$this->Literal})";
	}
}

class Lexer
{
	private $_input; //string 

	// Position in input from where we last read a character.
	private $_position;//int

	// Position in input from where we're going to read a character from
	// next.
	private $_readPosition = 0; //int

	// Character under examination.
	private $_ch;//char

	private $keywords = array( //Dictionary<string, TokenType> 
		"fn" 	 => TokenType::FUNCTION,
		"let" 	 => TokenType::LET,
		"true" 	 => TokenType::TRUE,
		"false"  => TokenType::FALSE,
		"if" 	 => TokenType::IF,
		"else" 	 => TokenType::ELSE,
		"return" => TokenType::RETURN
	);

	public function __construct(string $input)
	{
		$this->_input = $input;
		$this->ReadChar();
	}

	public function NextToken() : Token
	{
		$tok = null; //Token
		$this->SkipWhitespace();

		switch ($this->_ch)
		{
			case '=':
				if ($this->PeekChar() == '=')
				{
					$c = $this->_ch;
					$this->ReadChar();
					$tok = new Token(TokenType::EQ, $c . $this->_ch);
				}
				else
					$tok = new Token(TokenType::ASSIGN, $this->_ch);
				break;
			case '+':
				$tok = new Token(TokenType::PLUS, $this->_ch);
				break;
			case '-':
				$tok = new Token(TokenType::MINUS, $this->_ch);
				break;
			case '!':
				if ($this->PeekChar() == '=')
				{
					$c = $this->_ch;
					$this->ReadChar();
					$tok = new Token(TokenType::NOTEQ, $c . $this->_ch);
				}
				else
					$tok = new Token(TokenType::BANG, $this->_ch);
				break;
			case '/':
				$tok = new Token(TokenType::SLASH, $this->_ch);
				break;
			case '*':
				$tok = new Token(TokenType::ASTERISK, $this->_ch);
				break;
			case '<':
				$tok = new Token(TokenType::LT, $this->_ch);
				break;
			case '>':
				$tok = new Token(TokenType::GT, $this->_ch);
				break;
			case ';':
				$tok = new Token(TokenType::SEMICOLON, $this->_ch);
				break;
			case ',':
				$tok = new Token(TokenType::COMMA, $this->_ch);
				break;
			case '(':
				$tok = new Token(TokenType::LPAREN, $this->_ch);
				break;
			case ')':
				$tok = new Token(TokenType::RPAREN, $this->_ch);
				break;
			case '{':
				$tok = new Token(TokenType::LBRACE, $this->_ch);
				break;
			case '}':
				$tok = new Token(TokenType::RBRACE, $this->_ch);
				break;
			case '"':
				$tok = new Token(TokenType::STRING, $this->ReadString());
				break;
			case '[':
				$tok = new Token(TokenType::LBRACKET, $this->_ch);
				break;
			case ']':
				$tok = new Token(TokenType::RBRACKET, $this->_ch);
				break;            
			case ':':
				$tok = new Token(TokenType::COLON, $this->_ch);
				break;        
			case '\0':
				$tok = new Token(TokenType::EOF, "");
				break;
			default:
				if ($this->IsLetter($this->_ch))
				{
					$ident = $this->ReadIdentifier();
					$type = $this->LookupIdent($ident);
					$tok = new Token($type, $ident);

					// Early return is necessary because when calling
					// ReadIdentifier() we call ReadChar() repeatedly,
					// advancing _readPosition and _position past the last
					// character of the current identifier. So we don't need
					// to call NextToken after the switch again.
					return $tok;
				}
				elseif ($this->IsDigit($this->_ch))
				{
					$type = TokenType::INT;
					$literal = $this->ReadNumber();
					return new Token($type, $literal);
				}
				else
				{
					$tok = new Token(TokenType::ILLEGAL, $this->_ch);
					$this->ReadChar();
					return $tok;
				}
		}

		$this->ReadChar();
		return $tok;
	}

	public function LookupIdent(string $ident) : int
	{
		return isset($this->keywords[ $ident ]) ? $this->keywords[ $ident ] : TokenType::IDENT;
	}

	private function PeekChar() : string
	{
		return $this->_readPosition >= strlen($this->_input)
			? '\0'
			: $this->_input[$this->_readPosition];
	}

	private function ReadChar()
	{		
		$this->_ch = $this->_readPosition >= strlen($this->_input)
			? '\0'
			: $this->_input[$this->_readPosition];
		$this->_position = $this->_readPosition;
		$this->_readPosition++;
	}

	private function ReadIdentifier() : string
	{
		$p = $this->_position;
		while ($this->IsLetter($this->_ch))
			$this->ReadChar();
		return substr( $this->_input, $p, $this->_position - $p);
	}

	private function ReadNumber() : string
	{
		$p = $this->_position;
		while ($this->IsDigit($this->_ch))
			$this->ReadChar();
		return substr($this->_input, $p, $this->_position - $p);
	}

	private function IsLetter(string $ch) : bool
	{
		return 'a' <= $ch && $ch <= 'z' || 'A' <= $ch && $ch <= 'Z' || $ch == '_';
	}

	private function SkipWhitespace()
	{
		while ($this->_ch == ' ' || $this->_ch == "\t" || $this->_ch == "\n" || $this->_ch == "\r")
			$this->ReadChar();
	}

	private function IsDigit(string $ch) : bool
	{
		return '0' <= $ch && $ch <= '9';
	}

	private function ReadString() : string
	{
		$position = $this->_position + 1;

		// BUG: Passing a string which isn't terminated by " cases an
		// infinite loop because even though we've reached the end of input,
		// the " characters hasn't been reached.
		do
		{
			$this->ReadChar();
		}
		while ($this->_ch != '"');
		return substr($this->_input, $position, $this->_position - $position);
	}
}
