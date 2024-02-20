<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);

namespace damix\auth;

\damix\engines\orm\Orm::inc( 'auth~tormusers' );
\damix\core\classes\Classe::inc( 'auth~iUsers' );

class Userdummy
	extends \damix\auth\cOrmProperties_auth_tormusers
	implements \damix\auth\iUsers
{
	public string $login;
	
	public function setup() : void
	{
		$orm = \damix\engines\orm\Orm::get( 'auth~tormusers' );
		$orm->createTable(true);
		$orm->alterIndexTable(true);
	}
	
	public function loadUser(string $login) : bool
	{
		$orm = \damix\engines\orm\Orm::get( 'auth~tormusers' );
		$c = $orm->getConditionsClear('select');
		$c->addString( 'login', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $login );
		$liste = $orm->select();
		if( $record = $liste->fetch() )
		{
			return $this->loadrecord( $record, 'users_');
		}
		return false;
	}
	
	public function getPassword() : string
	{
		return $this->password ?? '';
	}
}