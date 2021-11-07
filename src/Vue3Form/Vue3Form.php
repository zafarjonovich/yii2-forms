<?php

namespace zafarjonovich\Yii2Forms\Vue3Form;

use yii\base\Widget;
use yii\helpers\ArrayHelper;

class Vue3Form extends Widget
{
    public $model;

    public $fieldClass = ActiveField::class;

    public $fieldConfig = [];

    public $validationStateOn = false;

    public function init()
    {

        parent::init();
    }

    public static function begin($config = [])
    {
        ob_start();
        return parent::begin($config);
    }


    public function run()
    {
        $template = ob_get_clean();
        echo $template;
    }

    /**
     * Generates a form field.
     * A form field is associated with a model and an attribute. It contains a label, an input and an error message
     * and use them to interact with end users to collect their inputs for the attribute.
     * @param Model $model the data model.
     * @param string $attribute the attribute name or expression. See [[Html::getAttributeName()]] for the format
     * about attribute expression.
     * @param array $options the additional configurations for the field object. These are properties of [[ActiveField]]
     * or a subclass, depending on the value of [[fieldClass]].
     * @return ActiveField the created ActiveField object.
     * @see fieldConfig
     */
    public function field($attribute, $options = [])
    {
        $model = $this->model;

        $config = $this->fieldConfig;
        if ($config instanceof \Closure) {
            $config = call_user_func($config, $model, $attribute);
        }
        if (!isset($config['class'])) {
            $config['class'] = $this->fieldClass;
        }

        return \Yii::createObject(ArrayHelper::merge($config, $options, [
            'model' => $model,
            'attribute' => $attribute,
            'form' => $this,
        ]));
    }
}