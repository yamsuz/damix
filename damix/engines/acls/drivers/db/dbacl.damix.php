<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\acls\drivers;


class DbAcl
	extends \damix\engines\acls\AclBase
{
	
	protected \damix\engines\orm\OrmBaseFactory $sormaclsright;
	protected \damix\engines\orm\OrmBaseFactory $ormsubjects;
	protected \damix\engines\orm\OrmBaseFactory $ormrights;
	protected \damix\engines\orm\OrmBaseFactory $ormgroups;
	protected \damix\engines\orm\OrmBaseFactory $ormusersgroups;
	
	public function __construct()
	{
		parent::__construct();
		
		$profile = \damix\engines\acls\Acl::$profile->get('acl', 'profile');
		$this->ormsubjects = \damix\engines\orm\Orm::get(\damix\engines\acls\Acl::$profile->get('acl', 'ormsubjects'), $profile);
		$this->ormrights = \damix\engines\orm\Orm::get(\damix\engines\acls\Acl::$profile->get('acl', 'ormrights'), $profile);
		$this->ormgroups = \damix\engines\orm\Orm::get(\damix\engines\acls\Acl::$profile->get('acl', 'ormgroups'), $profile);
		$this->ormusersgroups = \damix\engines\orm\Orm::get(\damix\engines\acls\Acl::$profile->get('acl', 'ormusersgroups'), $profile);
		$this->sormaclsright = \damix\engines\orm\Orm::get( \damix\engines\acls\Acl::$profile->get('acl', 'sormaclsright'), $profile);
	}
	
	public function setup() : void
	{
		$this->ormsubjects->createTable(true);
		$this->ormrights->createTable(true);
		$this->ormgroups->createTable(true);
		$this->ormusersgroups->createTable(true);
	}
	
	protected function loadright() : void
	{
		$this->loadgroup();
		
		if( empty( self::$_groups ) )
		{
			return;
		}
		
		$c = $this->sormaclsright->getConditionsClear('loadrightsubject');
		$c->addString( array( 'table' => 'aclrights', 'field' => 'groupcode' ), \damix\engines\orm\conditions\OrmOperator::ORM_OP_IN, self::$_groups);
		$subjects = $this->sormaclsright->loadrightsubject();
		
		self::$_acl = array();
		
		foreach( $subjects as $subject )
		{
			self::$_acl[ $subject->aclsubjects_subject ] = !$subject->aclrights_cancel;
		}
	}
	
	protected function loadgroup() : void
	{
		if( self::$_groups !== null )
		{
			return;
		}
		
		$c = $this->ormusersgroups->getConditionsClear('select');
		$c->addString( array( 'table' => 'aclusersgroups', 'field' => 'login' ), \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, \damix\engines\tools\xTools::login() );
		$groups = $this->ormusersgroups->select();
		self::$_groups = array();
		foreach( $groups as $group )
		{
			self::$_groups[] = $group->aclusersgroups_groupcode;
		}
	}
	
	public function addsubject(string $subject, string $label = '') : void
	{
		$c = $this->ormsubjects->getConditionsClear('select');
		$c->addString( 'subject', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $subject );
		$liste = $this->ormsubjects->select();
		
		if( $liste->rowcount() == 0 )
		{
			$record = $this->ormsubjects->createRecord();
			$record->subject = $subject;
			$record->label = $label;
			$this->ormsubjects->insert( $record );
		}
	}
	
	public function addright(string $subject, string $group, bool $cancel = false) : void
	{
		$c = $this->ormrights->getConditionsClear('select');
		$c->addString( 'subject', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $subject );
		$c->addString( 'groupcode', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $group );
		$liste = $this->ormrights->select();
		
		if( $liste->rowcount() == 0 )
		{
			$record = $this->ormrights->createRecord();
			$record->subject = $subject;
			$record->groupcode = $group;
			$record->cancel = $cancel;
			$this->ormrights->insert( $record );
		}
	}
	
	public function addgroup(string $code, string $label) : void
	{
		$c = $this->ormgroups->getConditionsClear('select');
		$c->addString( 'code', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $code );
		$liste = $this->ormgroups->select();
		
		if( $liste->rowcount() == 0 )
		{
			$record = $this->ormgroups->createRecord();
			$record->code = $code;
			$record->label = $label;
			$this->ormgroups->insert( $record );
		}
	}
	
	public function addusergroup(string $login, string $group) : void
	{
		$c = $this->ormusersgroups->getConditionsClear('select');
		$c->addString( 'login', \damix\engines\orm\conditions\OrmOperator::ORM_OP_EQ, $login );
		$liste = $this->ormusersgroups->select();
		
		if( $liste->rowcount() == 0 )
		{
			$record = $this->ormusersgroups->createRecord();
			$record->login = $login;
			$record->groupcode = $group;
			$this->ormusersgroups->insert( $record );
		}
	}
}