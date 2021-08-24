<?php


namespace zafarjonovich\Yii2Forms\forms;


use app\models\Person;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class BaseForm extends Model
{
    /**
     * @var ActiveRecord $activeRecordModel
     */
    public $activeRecordModel;

    protected $attributes = [];

    public function init()
    {
        if(get_parent_class(Person::class) != ActiveRecord::class){
            throw new \Exception('activeRecordModel must instance of ActiveRecord');
        }
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        /**
         * @var ActiveRecord $activeRecordModel
         */
        $activeRecordModel = new $this->activeRecordModel;

        $attributeNames = array_intersect($activeRecordModel->attributes(),$this->attributes());

        //dump($attributeNames);die;

        \Yii::configure($activeRecordModel,$this->getAttributes($attributeNames));

        $activeRecordModel->save($runValidation,$attributeNames);

        return $activeRecordModel;
    }
}