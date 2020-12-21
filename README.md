# OctoberCMS reorder plugin
## Simple OctoberCMS Reorder (Swap) plugin

Swap two items in list, just by clicking on generated button in `order` column. 
Before you can swap items, you must order by that column (Asc or Desc)
Working with pagination, search and other filters.
Working for simple relations (relation list view - partial)
### Not (yet) working if Relation is N-N or Nested (BelongsToMany, NestedTree) **for now**

## How to enable Swap button in order column:
In backend Controller of your plugin ({yourPlugin}/controller/{nameOfController}.php), 
in `$implement` array add `Reuniors\Reorder\Controllers\OrderSwapController`, example:
```
public $implement = [
    'Backend\Behaviors\ListController',
    'Backend\Behaviors\FormController',
    'Backend\Behaviors\ReorderController',
    'Backend\Behaviors\RelationController',
    
    'Reuniors\Reorder\Controllers\OrderSwapController'
];
```
In your Models list yaml ({yourPlugin}/model/{nameOfModel}/{list}.yaml) of your main or relation list
just set type of your order `type: reorder_button` example:
```
sort_order:
    label: 'Sort'
    type: reorder_button
```
That is it, you should now see buttons with order value instead of just text,
and you must order by that column to enable Swap button.
When you click on Swap (when hovers on order column value) button, button becomes Cancel Swap, 
and Cancel Swap button is added in toolbar (Cancel Swap: {order_value}) 