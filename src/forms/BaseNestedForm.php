<?php


namespace zafarjonovich\Yii2Forms\forms;

use zafarjonovich\Yii2Forms\forms\BaseForm;
use yii\base\Exception;
use yii\base\Model;

class BaseNestedForm extends BaseForm
{
    private $multipleNestedForms = [];

    private $nestedForms = [];

    private $skipableProperties = [];

    public function init()
    {
        foreach ($this->nestedForms as $property => $options) {
            if(!$this->isMultipleForm($property)){
                $this->{$property} = \Yii::createObject($options['formClass'],$options['initialProperties']);
            }
        }

        parent::init();
    }

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

    private function isMultipleForm($property_name)
    {
        return in_array($property_name,$this->multipleNestedForms);
    }

    public function hasNestedForm($property_name)
    {
        return isset($this->nestedForms[$property_name]);
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
    public function addNestedForm($property_name, $formClass,$initialProperties = [])
    {
        $this->nestedForms[$property_name] = compact('formClass','initialProperties');
    }

    /**
     * @param $property_name
     * @param $formClass
     */
    public function addMultipleNestedForm($property_name, $formClass, $initialProperties = [])
    {
        $this->addNestedForm($property_name, $formClass, $initialProperties);
        $this->multipleNestedForms[] = $property_name;
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
        foreach ($this->nestedForms as $property => $options) {

            if (!$this->hasProperty($property)) {
                throw new Exception("Property {$property} does not exists in class ");
            }

            if($this->canSkip($property,$data))
                continue;

            if(!isset($data[$property]))
                throw new Exception("Property {$property} should set");

            $properties = $data[$property];

            if($this->isMultipleForm($property)){

                if(!is_array($properties))
                    throw new Exception("Property {$property} should be in array format");

                /**
                 * @var $model Model
                 */
                foreach ($properties as $item){
                    $initialOptions = array_merge($options['initialProperties'],$item);

                    $model = new $options['formClass'];
                    $model->setAttributes($initialOptions);

                    $this->{$property}[] = $model;
                }
            }else{

                $initialOptions = array_merge($options['initialProperties'],$properties);

                $model = new $options['formClass'];
                $model->setAttributes($initialOptions);

                $this->{$property} = $model;
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

                if($this->isMultipleForm($property)){
                    $ok = Model::validateMultiple($this->{$property}) && $isOk;
                }else{
                    $ok = $this->{$property}->validate() && $isOk;
                }

                if(!$ok)
                    $this->addError($property,"Nested form error");

                $isOk = $ok && $isOk;
            }
        }

        return $isOk;
    }
}