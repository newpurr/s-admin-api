<?php declare(strict_types = 1);

namespace App\Model\Dao\Permission\Impl;

use App\Common\Enum\Permission\TypeEnum;
use App\Exception\AlreadyExistsException;
use App\Exception\InvalidParamException;
use App\Model\Dao\AbstractDao;
use App\Model\Dao\Permission\PermissionDaoInterface;
use App\Model\Entity\Rbac\ApiFieldResourceEntity;
use App\Model\Entity\Rbac\ApiResourceEntity;
use App\Model\Entity\Rbac\FrontResourceEntity;
use App\Model\Vo\Permission\BackEndApiField;
use Happysir\Lib\Enum\BoolEnum;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Db\Eloquent\Builder;
use Swoft\Db\Eloquent\Collection;
use Swoft\Db\Eloquent\Model;

/**
 * @Bean()
 */
class PermissionDao extends AbstractDao implements PermissionDaoInterface
{
    public function model() : string
    {
        return ApiResourceEntity::class;
    }
    
    /**
     * 删除全部权限
     *
     * @param int $type
     * @return int
     * @throws \App\Exception\InvalidParamException
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Swoft\Db\Exception\DbException
     */
    public function deletedAllResource(
        int $type = TypeEnum::API_INTERFACE
    ) : int {
        $query = $this->getEntityQuery($type);
        
        return $query->where('is_deleted', BoolEnum::FALSE)
                     ->update(['is_deleted' => BoolEnum::TRUE]);
    }
    
    /**
     * @param array $attr
     * @return \App\Model\Entity\Rbac\ApiResourceEntity
     * @throws \App\Exception\InvalidParamException
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Swoft\Db\Exception\DbException
     */
    public function findOrCreateBackEndRoute(
        array $attr
    ) : ApiResourceEntity {
        if (!$attr) {
            throw new InvalidParamException();
        }
        /** @var ApiResourceEntity $entity */
        $entity = ApiResourceEntity::firstOrNew(
            [
                'request_method' => $attr['method'],
                'uri'        => $attr['path']
            ]
        );
        $entity->fill(
            [
                'api_name'   => $attr['name'],
                'is_deleted' => BoolEnum::FALSE,
                'extra'      => json_encode((object)($attr['extra'] ?? [])),
            ]
        );
        $entity->save();
        
        return $entity;
    }
    
    /**
     * 启用指定后端路由
     *
     * @param array $resourceIds
     * @param int   $type
     * @return int
     * @throws \App\Exception\InvalidParamException
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Swoft\Db\Exception\DbException
     */
    public function enableResource(
        array $resourceIds,
        int $type = TypeEnum::API_INTERFACE
    ) : int {
        if (!$resourceIds) {
            return 0;
        }
        
        $query = $this->getEntityQuery($type);
        
        return $query->where('is_deleted', BoolEnum::TRUE)
                     ->whereIn('id', $resourceIds)
                     ->update(['is_deleted' => BoolEnum::FALSE]);
    }
    
    /**
     * 删除指定资源
     *
     * @param array $resourceIds
     * @param int   $type
     * @return int
     * @throws \App\Exception\InvalidParamException
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Swoft\Db\Exception\DbException
     */
    public function deleteResourceBy(
        array $resourceIds,
        int $type = TypeEnum::API_INTERFACE
    ) : int {
        if (!$resourceIds) {
            return 0;
        }
        
        $query = $this->getEntityQuery($type);
        
        return $query->where('is_deleted', BoolEnum::FALSE)
                     ->whereIn('id', $resourceIds)
                     ->update(['is_deleted' => BoolEnum::TRUE]);
    }
    
    /**
     * 删除不在指定资源中的资源
     *
     * @param array $resourceIds
     * @param int   $type
     * @return int
     * @throws \App\Exception\InvalidParamException
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Swoft\Db\Exception\DbException
     */
    public function deleteResourceByNotIn(
        array $resourceIds,
        int $type = TypeEnum::API_INTERFACE
    ) : int {
        if (!$resourceIds) {
            return 0;
        }
        
        $query = $this->getEntityQuery($type);
        
        return $query->where('is_deleted', BoolEnum::FALSE)
                     ->whereNotIn('id', $resourceIds)
                     ->update(['is_deleted' => BoolEnum::TRUE]);
    }
    
    /**
     * 通过类型获取资源id
     *
     * @param int $type
     * @return array
     * @throws \App\Exception\InvalidParamException
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getIdsByType(int $type = TypeEnum::API_INTERFACE) : array
    {
        $query = $this->getEntityQuery($type);
        
        return $query
            ->where('is_deleted', BoolEnum::FALSE)
            ->get(['id'])
            ->pluck('id')
            ->toArray();
    }
    
    /**
     * 提供指定的权限id，获取其中启用的权限（过滤失效权限id）
     *
     * @param int   $type
     * @param array $columns
     * @return \Swoft\Db\Eloquent\Collection
     * @throws \App\Exception\InvalidParamException
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getRecordsByType(
        int $type = TypeEnum::API_INTERFACE,
        array $columns = ['*']
    ) : Collection {
        $query = $this->getEntityQuery($type);
        
        return $query
            ->where('is_deleted', BoolEnum::FALSE)
            ->get($columns);
    }
    
    /**
     * @param array $ids
     * @param array $columns
     * @return \Swoft\Db\Eloquent\Collection
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getBackEndRouteByPerIds(
        array $ids,
        array $columns = ['*']
    ) : Collection {
        if (!$ids) {
            return new Collection();
        }
        
        return ApiResourceEntity::query()
                                ->whereIn('id', $ids)
                                ->get($columns);
    }
    
    /**
     * @param int                                      $apiPerId
     * @param \App\Model\Vo\Permission\BackEndApiField $dto
     * @return \App\Model\Entity\Rbac\ApiFieldResourceEntity
     * @throws \App\Exception\AlreadyExistsException
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Swoft\Db\Exception\DbException
     */
    public function createBackEndApiField(
        int $apiPerId,
        BackEndApiField $dto
    ) : ApiFieldResourceEntity {
        /** @var ApiFieldResourceEntity $entity */
        $entity = ApiFieldResourceEntity::where('field_key', $dto->getFieldKey())
                                        ->where('api_per_id', $apiPerId)
                                        ->first();
        if ($entity) {
            if (!$entity->getIsDeleted()) {
                throw new AlreadyExistsException(
                    sprintf('field_key:%s已存在', $dto->getFieldKey())
                );
            }
            
            if ($entity->getIsDeleted()) {
                $entity->setIsDeleted(BoolEnum::FALSE);
                
                $entity->save();
                
                return $entity;
            }
        }
        
        $entity = ApiFieldResourceEntity::new($dto->toArray());
        $entity->save();
        
        return $entity;
    }
    
    /**
     * 通过api权限id获取api权限字段
     *
     * @param array $apiPerIds
     * @param array $columns
     * @return Collection
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getApiFieldByApiPerIds(
        array $apiPerIds,
        array $columns = ['*']
    ) : Collection {
        return ApiFieldResourceEntity::query()
                                     ->whereIn('api_per_id', $apiPerIds)
                                     ->where('is_deleted', BoolEnum::FALSE)
                                     ->get($columns);
    }
    
    /**
     * @param int $type
     * @return \Swoft\Db\Eloquent\Builder
     * @throws \App\Exception\InvalidParamException
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Swoft\Db\Exception\DbException
     */
    protected function getEntityQuery(int $type) : Builder
    {
        /** @var Model $entityName */
        $entityName = $this->getEntityName($type);
        
        return $entityName::query();
    }
    
    /**
     * @param int $type
     * @return string
     * @throws \App\Exception\InvalidParamException
     */
    protected function getEntityName(int $type) : string
    {
        switch ($type) {
            case TypeEnum::API_INTERFACE:
                $entityName = ApiResourceEntity::class;
                break;
            case TypeEnum::API_INTERFACE_FIELD:
                $entityName = ApiFieldResourceEntity::class;
                break;
            case TypeEnum::FRONT_END:
                $entityName = FrontResourceEntity::class;
                break;
            default:
                throw new InvalidParamException();
                break;
        }
        
        return $entityName;
    }
}
