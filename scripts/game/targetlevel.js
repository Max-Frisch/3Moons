(function ($) {
    function readJSON(value) {
        try {
            return JSON.parse(value || '{}') || {};
        } catch (e) {
            return {};
        }
    }

    function formatAmount(value) {
        if (typeof NumberGetHumanReadable === 'function') {
            return NumberGetHumanReadable(value);
        }
        return String(Math.round(value)).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function recalc(box) {
        var input = $(box).find('input[name="target"]').get(0);
        if (!input) {
            return;
        }

        var start = parseInt($(box).attr('data-start-level'), 10) || 1;
        var max = parseInt($(box).attr('data-max-level'), 10) || 0;
        var target = parseInt(input.value, 10);
        if (isNaN(target) || target < start) {
            target = start;
        }
        if (max > 0 && target > max) {
            target = max;
        }

        var factor = parseFloat($(box).attr('data-cost-factor')) || 1;
        var baseCost = readJSON($(box).attr('data-cost-base'));
        var resources = typeof targetResources === 'object' && targetResources !== null ? targetResources : {};
        var levels = target - start + 1;

        $(box).find('.construct_cost').each(function () {
            var resourceId = $(this).attr('data-resource');
            if (!Object.prototype.hasOwnProperty.call(baseCost, resourceId)) {
                return;
            }

            var amount = parseFloat(baseCost[resourceId]) || 0;
            var total;
            if (factor === 1) {
                total = amount * levels;
            } else {
                total = 0;
                for (var level = start; level <= target; level++) {
                    total += amount * Math.pow(factor, level);
                }
            }
            total = Math.round(total);

            var value = $(this).find('.construct_cost_value');
            value.text(formatAmount(total));

            var available = parseFloat(resources[resourceId]);
            value.css('color', !isNaN(available) && total > available ? 'red' : '');
        });
    }

    $(function () {
        $('.block_construct[data-cost-base]').each(function () {
            var box = this;
            $(box).find('input[name="target"]').on('input change blur', function () {
                recalc(box);
            });
            recalc(box);
        });
    });
})(jQuery);
