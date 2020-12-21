<?php namespace Reuniors\Reorder\Controllers;

use Backend\Classes\ControllerBehavior;
use \Illuminate\Support\Facades\Validator;
use October\Rain\Exception\AjaxException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OrderSwapController extends ControllerBehavior
{
    public $actions = ['orderSwap'];
    
    public function __construct($controller)
    {
        parent::__construct($controller);
        $this->addJs('/plugins/reuniors/reorder/assets/orderSwap.js');
    }

    public function onOrderSwap()
    {
        $validator = Validator::make(post(), [
            'recordIdentifier' => 'required',
            'columnName' => 'required',
            'value' => 'required',
            'oldValue' => 'required',
        ]);
        if ($validator->fails()) {
            return response($validator->getMessageBag(), 403);
        }
        $value = post('value');
        $oldValue = post('oldValue');
        $columnName = post('columnName');
        $recordClass = base64_decode(post('recordIdentifier'));
        $modelDataCollection = $recordClass::whereIn($columnName, [$value, $oldValue])
            ->get();
        if (!empty($modelDataCollection) && !isset($modelDataCollection[1])) {
            return response(['message' => 'Invalid data'], 404);
        }
        foreach ($modelDataCollection as $oneModelData) {
            $oneModelData->$columnName = (string)$oneModelData->$columnName === (string)$value
                ? $oldValue
                : $value;
            $oneModelData->save();
        }
    }
}
