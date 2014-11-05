<?php

/*
 * @author Maciej "Gilek" Kłak
 * @copyright Copyright &copy; 2014 Maciej "Gilek" Kłak
 * @version 2.0.0-alpha
 * @package yii-gtreetable
 */

abstract class BaseModel extends CActiveRecord
{
    const POSITION_BEFORE = 'before';
    const POSITION_AFTER = 'after';
    const POSITION_FIRST_CHILD = 'firstChild';
    const POSITION_LAST_CHILD = 'lastChild';
    const TYPE_DEFAULT = 'default';
    
    public $parent;
    public $position;
    public $related;
    public $nameAttribute = 'name';
    public $typeAttribute = 'type';
    public $hasManyRoots;
    public $rootAttribute;
    public $leftAttribute;
    public $rightAttribute;
    public $levelAttribute;

    public function __toString() {
        return $this->{$this->nameAttribute};
    }

    public function rules() {
        return array(
            array('parent', 'required', 'on' => 'create'),
            array('related', 'required', 'on' => 'create, move'),
            array('position', 'required', 'on' => 'create, move'),
            array('position', 'in', 'range' => $this->getPositions(), 'on' => 'create, move'),
            array($this->nameAttribute, 'required', 'on' => 'create, update'),
            array($this->nameAttribute, 'length', 'max' => 128, 'on' => 'create, update'),
            array($this->nameAttribute, 'filter', 'filter' => array('CHtml', 'encode'), 'skipOnError' => true, 'on' => 'create, update'),
        );
    }    
    
    public function relations() {
        return array(
            'relatedRel' => array(self::BELONGS_TO, get_class($this) , 'related'),
        );
    }    
    
    public function beforeSave($insert) {
        parent::beforeSave($insert);

        if ($this->isNewRecord) {
            $this->{$this->typeAttribute} = self::TYPE_DEFAULT;
        }
        return true;
    }
    
    public function behaviors() {
        return array(
            'nestedSetBehavior'=>array(
                'class'=>'ext.gtreetable.behaviors.nestedset.NestedSetBehavior',
            ),            
        );
    }    
    
    public function getPositions() {
        return array(
            self::POSITION_BEFORE,
            self::POSITION_AFTER,
            self::POSITION_FIRST_CHILD,
            self::POSITION_LAST_CHILD
        );
    }   
    
    /**
     * 
     * @param string $glue
     * @return string
     */
    public function getPath($glue = ' » ') {
        $path = array();
        foreach ($this->ancestors()->all() as $model) {
            $path[] = (string) $model;
        }
        $path[] = (string) $this;
        krsort($path);
        return implode($glue, $path);
    }    
}
