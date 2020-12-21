$.fn.swapParentRows = function($elem) {
    var $before = $(this)
    var $tempTr = $('<tr></tr>')
    $elem.children().appendTo($tempTr)
    $elem.empty()
    $before.children().appendTo($elem)
    $before.empty()
    $tempTr.children().appendTo($before)
};

$(document).ready(function () {
    var addedSwap = {}
    var clonedElements = {}
    var $reorderSwaps = $('.reorder-order-switch.initial-state')
    var cancelSwapBtnName = '#cancelSwapBtn'
    var addToolbarBtn = function (data, $btn) {
        $(cancelSwapBtnName).remove()
        var $closestLayout = $btn.closest('div.layout')
        var $toolbarCancelBtn = $('<button id="cancelSwapBtn" class="btn btn-warning"></button>')
            .html('Cancel swap: ' + data.value);
        $('.toolbar-item.toolbar-primary > div', $closestLayout).append($toolbarCancelBtn);
        $(cancelSwapBtnName).click(function () {
            resetButton($btn, data)
            $(cancelSwapBtnName).remove()
        })
    }
    var resetButton = function ($btn, data) {
        $btn.html($btn.data('value'))
        $btn.removeClass('btn-warning')
        $btn.addClass('btn-cyan')
        if (addedSwap[data.recordIdentifier]) {
            delete addedSwap[data.recordIdentifier]
        }
        $(cancelSwapBtnName).remove()
    }
    var reorderClickEvent = function (event) {
        event.stopPropagation()
        event.preventDefault()
        var $this = $(this)
        var $table = $this.closest('table.table.data')
        var $currentTd = $this.closest('td')
        var $activeTh = $('th.active', $table)
        var data = $this.data('prepare-data')
        data.value = $this.data('value')
        if (!data.recordIdentifier || ($activeTh && $activeTh.index() !== $currentTd.index())) {
            return false
        }
        $this.removeClass('initial-state')
        var dataClassName = $this.data('class-name')
        if ($this.hasClass('btn-warning')) {
            resetButton($this, data)
            return false
        }
        if (addedSwap[data.recordIdentifier] >= 0) {
            data.oldValue = addedSwap[data.recordIdentifier]
            var $currentReorderSwaps = $('.reorder-order-switch.' + dataClassName)
            var $activeReorderSwaper = $('.reorder-order-switch.btn-warning.' + dataClassName)
            $currentReorderSwaps.prop('disabled', true);
            $this.request('onOrderSwap', {
                data: data,
                success: function () {
                    var $activeRowBtn = $activeReorderSwaper.length > 0
                        ? $activeReorderSwaper
                        : $('button', clonedElements[data.recordIdentifier])
                    var $currentTr = $this.closest('tr')
                    $activeRowBtn.data('value', data.value)
                    $this.data('value', data.oldValue)
                    resetButton($activeRowBtn, data)
                    $currentTr.swapParentRows($activeRowBtn.closest('tr'))
                    delete addedSwap[data.recordIdentifier]
                    $currentReorderSwaps.removeAttr('disabled')
                    resetButton($this, data)
                },
                fail: function () {
                    $currentReorderSwaps.removeAttr('disabled')
                    resetButton($activeReorderSwaper, data)
                }
            })
        } else {
            addedSwap[data.recordIdentifier] = data.value
            $this.html($this.data('cancel-title'))
            $this.removeClass('btn-cyan')
            $this.addClass('btn-warning')
            clonedElements[data.recordIdentifier] = $this.closest('tr').clone()
            addToolbarBtn(data, $this)
        }
        console.log(data)
    }

    function updateSwapsEvent ($reorderSwaps) {
        $reorderSwaps.hover(function () {
            var $this = $(this)
            var $table = $this.closest('table.table.data')
            var $currentTd = $this.closest('td')
            var $activeTh = $('th.active', $table)
            if (!$this.hasClass('btn-warning') && $activeTh && $activeTh.index() === $currentTd.index() ) {
                $this.html($this.data('switch-title'))
            }
        }, function () {
            var $this = $(this)
            var $table = $this.closest('table.table.data')
            var $currentTd = $this.closest('td')
            var $activeTh = $('th.active', $table)
            if (!$this.hasClass('btn-warning') && $activeTh && $activeTh.index() === $currentTd.index()) {
                $this.html($this.data('value'))
            }
        })
        $.each($reorderSwaps, function () {
            $(this).unbind('click')
        })
        $reorderSwaps.click(reorderClickEvent)
    }
    updateSwapsEvent($reorderSwaps)

    $(document).on('render', function () {
        $reorderSwaps = $('.reorder-order-switch.initial-state')
        updateSwapsEvent($reorderSwaps)
    })
})