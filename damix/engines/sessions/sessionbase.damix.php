<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\sessions;


abstract class SessionBase 
	implements \SessionHandlerInterface
{
	public abstract function close(): bool;
	public abstract function destroy(string $id): bool;
	public abstract function gc(int $max_lifetime): int|false;
	public abstract function open(string $path, string $name): bool;
	public abstract function read(string $id): string|false;
	public abstract function write(string $id, string $data): bool;
	
}