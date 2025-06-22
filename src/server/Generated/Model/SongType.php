<?php

namespace Generated\Model;

class SongType extends \ArrayObject
{
    /**
     * @var array
     */
    protected $initialized = [];
    public function isInitialized($property): bool
    {
        return array_key_exists($property, $this->initialized);
    }
    /**
     * 
     *
     * @var string
     */
    protected $songTypeId;
    /**
     * 
     *
     * @var string
     */
    protected $songTypeName;
    /**
     * 
     *
     * @var int
     */
    protected $orderNo;
    /**
     * 
     *
     * @return string
     */
    public function getSongTypeId(): string
    {
        return $this->songTypeId;
    }
    /**
     * 
     *
     * @param string $songTypeId
     *
     * @return self
     */
    public function setSongTypeId(string $songTypeId): self
    {
        $this->initialized['songTypeId'] = true;
        $this->songTypeId = $songTypeId;
        return $this;
    }
    /**
     * 
     *
     * @return string
     */
    public function getSongTypeName(): string
    {
        return $this->songTypeName;
    }
    /**
     * 
     *
     * @param string $songTypeName
     *
     * @return self
     */
    public function setSongTypeName(string $songTypeName): self
    {
        $this->initialized['songTypeName'] = true;
        $this->songTypeName = $songTypeName;
        return $this;
    }
    /**
     * 
     *
     * @return int
     */
    public function getOrderNo(): int
    {
        return $this->orderNo;
    }
    /**
     * 
     *
     * @param int $orderNo
     *
     * @return self
     */
    public function setOrderNo(int $orderNo): self
    {
        $this->initialized['orderNo'] = true;
        $this->orderNo = $orderNo;
        return $this;
    }
}