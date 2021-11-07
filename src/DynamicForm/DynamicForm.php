<?php

namespace zafarjonovich\Yii2Forms\views;

use yii\base\Widget;

class DynamicForm extends Widget
{
    public $minItemCount = 1;

    public $items = [];

    public $template = null;

    public $form;

    public $model;

    public function init()
    {

        parent::init();
    }

    private function getClientValidators()
    {
        $view = $this->view;

        $validators = [];

        foreach ($formModel->getActiveValidators() as $activeValidator) {
            foreach ($activeValidator->attributes as $attribute) {
                $js = $activeValidator->clientValidateAttribute($formModel,$attribute,$view);
                if($js !== null) {
                    $validators[$attribute][] = $activeValidator->clientValidateAttribute($formModel,$attribute,$view);
                }
            }
        }

        foreach ($validators as &$validator) {
            $validator = "function(value, messages) {\n" . implode("\n",$validator) . "\n}";
        }

        return $validators;
    }

    public static function begin($config = [])
    {
        ob_start();
        return parent::begin($config);
    }

    public static function end()
    {
        $form = ob_get_clean();
        echo strtoupper($template);
        return parent::end();
    }
}