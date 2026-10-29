// Shared behaviour for the shop role's bakery-themed pages.
// Wires up every ".bakery-qty-stepper" (+/- buttons around a number input)
// found on the page - used on both the create-order label list and the
// cart table. The +/- buttons always move the quantity by a whole number,
// even though the input itself (step="0.01") still accepts decimal values
// when typed directly.
//
// Exposed on window so the create-order page can re-run it on the product
// grid after an AJAX filter refresh swaps that markup back in.
window.BakeryTheme = window.BakeryTheme || {};

window.BakeryTheme.initQtySteppers = function (root) {
    (root || document).querySelectorAll('.bakery-qty-stepper').forEach(function (stepper) {
        if (stepper.dataset.steppersBound) return;
        stepper.dataset.steppersBound = '1';

        var input = stepper.querySelector('input[type="number"]');
        if (!input) return;

        var minus = stepper.querySelector('.bakery-qty-minus');
        var plus = stepper.querySelector('.bakery-qty-plus');

        // Only present on the create-order label list (each item has a
        // "selected_items[]" toggle); a no-op elsewhere, e.g. the cart table.
        function syncSelectCheckbox() {
            var checkbox = stepper.parentElement && stepper.parentElement.querySelector('.bakery-select-checkbox');
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
    window.BakeryTheme.initQtySteppers(document);
});
