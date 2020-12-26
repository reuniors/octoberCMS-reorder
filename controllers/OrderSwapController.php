<?php namespace Reuniors\Reorder\Controllers;

use DB;
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
        $this->addCss('/plugins/reuniors/reorder/assets/orderSwap.css');
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
        $value = (int)post('value');
        $oldValue = (int)post('oldValue');
        $columnName = post('columnName');
        $recordClass = base64_decode(post('recordIdentifier'));

        if ($this->checkAndFixOrder($recordClass, $columnName)) {
            return response(
                [
                    'message' => 'Invalid order fixed, please refresh page and try again'
                ],
                400
            );
        }

        $modelDataCollection = $recordClass::whereIn($columnName, [$value, $oldValue])
            ->get();
        if (!empty($modelDataCollection) && !isset($modelDataCollection[1])) {
            return response(['message' => 'Invalid data'], 404);
        }
        foreach ($modelDataCollection as $oneModelData) {
            $oneModelData->$columnName = $oneModelData->$columnName === $value
                ? $oldValue
                : $value;
            $oneModelData->save();
        }
    }

    public function checkAndFixOrder($modelClassName, $columnName)
    {
        $invalidSortDataList = $modelClassName::query()
            ->select($columnName, DB::raw('count(*) as count_sort'))
            ->groupBy($columnName)
            ->havingRaw('count_sort > 1')
            ->orderBy($columnName)
            ->get();
        $countInvalidSortData = $invalidSortDataList->count($columnName);
        if ($countInvalidSortData > 0) {
            foreach ($invalidSortDataList as $oneInvalidSortData) {
                $minSortValue = $oneInvalidSortData->$columnName;
                $modelDataCollection = $modelClassName::query()
                    ->where($columnName, '>=', $minSortValue)
                    ->where($columnName, '<', $minSortValue + $oneInvalidSortData->count_sort)
                    ->orderBy($columnName)
                    ->get();
                $orderIndex = $minSortValue;
                foreach ($modelDataCollection as $oneModelData) {
                    if (
                        $oneModelData->$columnName != $orderIndex
                    ) {
                        $oneModelData->$columnName = $orderIndex;
                        $oneModelData->save();
                    }
                    $orderIndex++;
                }
            }
            return true;
        }
        return false;
    }
}
