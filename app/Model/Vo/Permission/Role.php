<?php declare(strict_types = 1);

namespace App\Model\Vo\Permission;

use App\Model\Entity\Permission\RoleEntity;
use Happysir\Lib\Annotation\Mapping\POJO;
use Happysir\Lib\BasePOJO;

/**
 * Class Role
 * @POJO()
 */
class Role extends BasePOJO
{
    /**
     * 角色昵称
     *
     * @var string
     */
    private $roleName = '';
    
    /**
     * 角色描述
     *
     * @var string
     */
    private $describe = '';
    
    /**
     * getName
     *
     * @return string
     */
    public function getRoleName() : string
    {
        return $this->roleName;
    }
    
    /**
     * @param string $roleName
     */
    public function setRoleName(string $roleName) : void
    {
        $this->roleName = $roleName;
    }
    
    /**
     * getDescribe
     *
     * @return string
     */
    public function getDescribe() : string
    {
        return $this->describe;
    }
    
    /**
     * @param string $describe
     */
    public function setDescribe(string $describe) : void
    {
        $this->describe = $describe;
    }
    
    /**
     * @return \App\Model\Entity\Permission\RoleEntity
     * @throws \Swoft\Db\Exception\DbException
     */
    public function convertTo() : RoleEntity
    {
        return RoleEntity::new($this->toArray());
    }
    
    /**
     * @param \App\Model\Entity\Permission\RoleEntity $resourceEntity
     * @return $this
     * @throws \Swoft\Db\Exception\DbException
     */
    public static function convertFrom(RoleEntity $resourceEntity) : self
    {
        return self::new($resourceEntity->toArray());
    }
}
