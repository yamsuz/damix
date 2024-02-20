<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\sessions;


class SessionDb
	extends \damix\engines\sessions\SessionBase
{
	private string $ormselector;
	
	
	public function __construct()
	{
		$this->ormselector = \damix\engines\settings\Setting::getValue('default', 'session', 'tormselector');
		
	}
	
	public function close(): bool
	{
		return true;
	}
	public function destroy(string $id): bool
	{
		$orm = $this->getOrm();
		$orm->delete( $id );
		return true;
	}
	public function gc(int $max_lifetime): int|false
	{
		$date=\damix\engines\tools\xDate::now();
		$date->subPeriod(array( 'second' => $max_lifetime) );
		
		$orm = $this->getOrm();
		$c = $orm->getConditionsClear( 'select' );
		$c->addDateTime( 'update', \damix\engines\orm\conditions\OrmOperator::ORM_OP_LT, $date );
		$liste = $orm->select();
		foreach( $liste as $info)
		{
			$this->destroy($info->sessions_idsessions);
		}
		return $liste->rowCount();
	}
	public function open(string $path, string $name): bool
	{
		return true;
	}
	public function read(string $id): string
	{
		$orm = $this->getOrm();
		$session=$orm->get( $id );
		// \damix\engines\logs\log::dump( $session );
		return $session->data ?? '';
	}
	public function write(string $id, string $data): bool
	{
		$orm = $this->getOrm();
		$liste = $this->getRecord($id);
		$record = $liste->fetch();
			// \damix\engines\logs\log::log( $id );
			// \damix\engines\logs\log::log( $data );
		if(!$record)
		{
			$session = $orm->createRecord();
			$session->user=\damix\engines\tools\xTools::login();
			$session->idsessions=$id;
			$session->data=$data;
			$orm->insert($session);
		}
		else{
			$session = new \damix\damix\cOrmRecord_damix_tormsessions();
			$session->loadrecord($record);
			$session->user=\damix\engines\tools\xTools::login();
			$session->data=$data;
			$orm->update($session);
		}
		return true;
	}
	
	protected function getRecord(string $id):?\damix\engines\databases\DbResultSet
	{
		$orm = $this->getOrm();
		$c = $orm->getConditionsClear( 'select' );
		$c->addString( 'idsessions', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $id );
		$liste = $orm->select();
		return $liste;
	}
	
	protected function getOrm() : \damix\engines\orm\OrmBaseFactory
	{
		return \damix\engines\orm\Orm::get( $this->ormselector );
	}
}