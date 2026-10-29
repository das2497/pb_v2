// Shared behaviour for the order-admin role's create-order page. Wires up
// every ".oa-order-qty-stepper" (+/- buttons around a number input),
// mirroring assets/js/rep-order-theme.js's stepper behaviour for the rep
// role's create-order/cart pages. The +/- buttons always move the quantity
// by a whole number, even though the input itself (step="0.01") still
// accepts decimal values when typed directly.
//
// Exposed on window so the create-order page can re-run it on the product
// grid after an AJAX filter refresh swaps that markup back in.
window.OAOrderTheme = window.OAOrderTheme || {};

window.OAOrderTheme.initQtySteppers = function (root) {
    (root || document).querySelectorAll('.oa-order-qty-stepper').forEach(function (stepper) {
        if (stepper.dataset.steppersBound) return;
        stepper.dataset.steppersBound = '1';

        var input = stepper.querySelector('input[type="number"]');
        if (!input) return;

        var minus = stepper.querySelector('.oa-order-qty-minus');
        var plus = stepper.querySelector('.oa-order-qty-plus');

        function syncSelectCheckbox() {
            var checkbox = stepper.parentElement && stepper.parentElement.querySelector('.oa-order-select-checkbox');
            if (!checkbox) return;
            var qty = parseFloat(input.value) || 0;
            checkbox.checked = qty > 0;
            checkbox.dispatchEvent(new Event('change'));
        }

        var min = parseFloat(input.getAttribute('min')) || 0;

        if (minus) {
            minus.addEventListener('click', function () {
                var qty = parseFloat(input.value) || 0;
                input.value = Math.max(min, Math.round(qty) - 1);
                syncSelectCheckbox();
            });
        }

        if (plus) {
            plus.addEventListener('click', function () {
                var qty = parseFloat(input.value) || 0;
                input.value = Math.round(qty) + 1;
                syncSelectCheckbox();
            });
        }

        input.addEventListener('input', syncSelectCheckbox);
    });
};

document.addEventListener('DOMContentLoaded', function () {
    window.OAOrderTheme.initQtySteppers(document);
});
