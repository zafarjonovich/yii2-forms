<?php


namespace zafarjonovich\Yii2Forms\forms;

use zafarajonovich\Yii2Forms\forms\BaseForm;
use yii\base\Exception;
use yii\base\Model;

class BaseNestedForm extends BaseForm
{
    protected $nestedForms = [];

    private $skipableProperties = [];

    /**
     * @param $name
     * @return bool
     */
    protected function isSkipableProperty($name)
    {
        return in_array($name,$this->skipableProperties);
    }

    /**
     * @param $name
     */
    protected function addSkipableProperty($name)
    {
        $this->skipableProperties[] = $name;
    }

    /**
     * @param $property
     * @param $data
     * @return bool
     */
    private function canSkip($property,$data)
    {
        return $this->isSkipableProperty($property) and !isset($data[$property]);
    }

    /**
     * @return bool
     */
    public function hasNestedForms()
    {
        return !empty($this->nestedForms);
    }

    /**
     * @param $property_name
     * @param $formClass
     */
    public function addNestedForm($property_name, $formClass)
    {
        $this->nestedForms[$property_name] = $formClass;
    }

    /**
     * @param array $values
     * @param bool $safeOnly
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function setAttributes($values, $safeOnly = true)
    {
        if($this->hasNestedForms()){
            $this->initializeNestedForms($values);
        }
        parent::setAttributes($values, $safeOnly);
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        $valid = $this->validateNestedVarables();

        if(!$valid)
            return false;

        return parent::validate($attributeNames, $clearErrors);
    }

    /**
     * @param $data
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    private function initializeNestedForms($data)
    {
        foreach ($this->nestedForms as $property => $class) {

            if (!$this->hasProperty($property)) {
                throw new Exception("Property {$property} does not exists in class ");
            }

            if($this->canSkip($property,$data))
                continue;

            if(!is_array($data[$property]))
                throw new Exception("Property {$property} should be in array format");

            /**
             * @var $model Model
             */
            foreach ($data[$property] as $item) {
                $model = new $class;
                $model->setAttributes($item);
                $this->{$property}[] = $model;
            }
        }
    }

    /**
     * @param null $attributeNames
     * @param bool $clearErrors
     * @return bool
     */
    public function validateNestedVarables()
    {
        $isOk = true;

        foreach ($this->nestedForms as $property => $class) {
            if(!$this->isSkipableProperty($property)){
                /**
                 * @var $model Model
                 */
                $models = $this->{$property};

                foreach ($models as $model) {
                    if (
                        ($model->hasMethod('validateNested') && !$model->validateNested()) ||
                        (!$model->hasMethod('validateNested') && !$model->validate())
                    ) {
                        $isOk = false;
                        $this->addError($property, "Nested Property: {$property} validation error");
                    }
                }
            }
        }
        return $isOk;
    }
}