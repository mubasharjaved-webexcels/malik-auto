
    <?php $__env->startPush('scripts'); ?>
    <script>
    /////////////////////////price field for USD and JPY /////////////////////////
    document.addEventListener('DOMContentLoaded', function () {
        const jpyRate = parseFloat("<?php echo e($jpyRate); ?>");
        // console.log(jpyRate);
        
        if (!jpyRate || isNaN(jpyRate)) {
            console.warn('JPY rate not found or invalid.');
            return;
        }

        const usdInput = document.getElementById('price_usd');
        const jpyInput = document.getElementById('price_jpy');

        if (!usdInput || !jpyInput) return;

        const toFloat = (val) => parseFloat(val) || 0;
        const round = (val) => (Math.round(val * 100) / 100).toFixed(2);

        usdInput.addEventListener('input', () => {
            const usd = toFloat(usdInput.value);
            jpyInput.value = round(usd * jpyRate);
        });

        jpyInput.addEventListener('input', () => {
            const jpy = toFloat(jpyInput.value);
            usdInput.value = round(jpy / jpyRate);
        });

        if (usdInput.value) {
            jpyInput.value = round(toFloat(usdInput.value) * jpyRate);
        }
    });

    ///////////////////////////conversion for sale and sold price////////////////////////
document.addEventListener('DOMContentLoaded', function () {
    const localRate = parseFloat("<?php echo e($conversionRate); ?>");

    if (!localRate || isNaN(localRate)) {
        console.warn('Invalid currency rate:', localRate);
        return;
    }

    const toFloat = val => parseFloat(val) || 0;
    const round = val => (Math.round(val * 100) / 100).toFixed(2);

    const setupTwoWaySync = (usdId, localId) => {
        const usdInput = document.getElementById(usdId);
        const localInput = document.getElementById(localId);

        if (!usdInput || !localInput) {
            console.warn(`Missing input(s): ${usdId}, ${localId}`);
            return;
        }

        // USD → Local sync
        usdInput.addEventListener('input', () => {
            const usdVal = toFloat(usdInput.value);
            localInput.value = round(usdVal * localRate);
        });

        // Local → USD sync
        localInput.addEventListener('input', () => {
            const localVal = toFloat(localInput.value);
            usdInput.value = round(localVal / localRate);
        });

        // Initial sync on page load
        if (usdInput.value) {
            localInput.value = round(toFloat(usdInput.value) * localRate);
        } else if (localInput.value) {
            usdInput.value = round(toFloat(localInput.value) / localRate);
        }
    };

    // Sale price
    setupTwoWaySync('sale_price_usd', 'sale_price_local');

    // Booking price
    setupTwoWaySync('booking_price_usd', 'booking_price_local');

    // Sold price
    setupTwoWaySync('sold_price_usd', 'sold_price_local');
});


    </script>
  <?php $__env->stopPush(); ?><?php /**PATH /home/malikautonet/public_html/resources/views/dashboards/car_profiles/partials/price-conversion-script.blade.php ENDPATH**/ ?>