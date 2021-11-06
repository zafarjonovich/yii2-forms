<?php


namespace zafarjonovich\Yii2Forms\forms;


use yii\base\Model;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class BaseForm extends Model
{
    protected $modelProperties = [];

    public static function activeRecordModel()
    {
        return null;
    }

    public function getModel()
    {
        if(get_parent_class(static::activeRecordModel()) != ActiveRecord::class){
            throw new \Exception('activeRecordModel must instance of ActiveRecord');
        }

        $modelClass = static::activeRecordModel();

        $where = [];

        foreach ((array)$modelClass::primaryKey() as $primaryKey) {
            $where[$primaryKey] = $this->{$primaryKey};
        }

        /**
         * @var ActiveRecord $model
         */

        $model = $modelClass::findOne($where);

        if($model === null) {
            $model = new $modelClass;
        }

        return $model;
    }

    public function __get($name)
    {
        return $this->modelProperties[$name] ?? null;
    }

    public function __set($name,$value)
    {
        if(!in_array($name,$this->getModel()->attributes())) {
            throw new \Exception('Setting uncnown property: '.(static::activeRecordModel()).'::'.$name);
        }

        $this->modelProperties[$name] = $value;
    }

    public function attributes()
    {
        return $this->getModel()->attributes();
    }

    public function configureModel($model)
    {
        $attributeNames = array_intersect($model->attributes(),$this->attributes());
        \Yii::configure($model,$this->getAttributes($attributeNames));

        return $model;
    }

    public function configure($model)
    {
        $attributeNames = array_intersect($this->attributes(),$model->attributes());
        \Yii::configure($this,$this->getAttributes($attributeNames));
    }
}