<?php namespace Reuniors\Reorder;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }

    public function registerListColumnTypes()
    {
        return [
            'reorder_button' => [$this, 'showReorderButton'],
        ];
    }

    public function showReorderButton($value, $column, $record)
    {
        $recordClass = base64_encode(get_class($record));
        $recordClassName = $this->clean($recordClass);
        $prepareData = json_encode([
            'recordIdentifier' => $recordClass,
            'columnName' => $column->columnName,
        ]);
        return "
            <button
                class='btn btn-cyan reorder-order-switch $recordClassName initial-state'
                data-prepare-data='$prepareData'
                data-class-name='$recordClassName'
                type='button'
                data-cancel-title='Cancel switch'
                data-switch-title='Swap'
                data-value='$value'
                style='color: #000; min-width: 80px'
            >
                $value
            </button>
        ";
    }

    public function clean($string) {
        $string = str_replace(' ', '-', $string);
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    }
}
