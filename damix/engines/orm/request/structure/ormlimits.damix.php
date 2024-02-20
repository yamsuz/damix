<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\orm\request\structure;


class OrmLimits
{
    public ?int $Offset = null;
    public ?int $RowCount = null;
    
    public function setLimit( int $offset, int $rowcount )
    {
        $this->Offset = $offset;
        $this->RowCount = $rowcount;
    }
    
    public function getHashData() : array
    {
        return array( $this->Offset, $this->RowCount );
    }
}