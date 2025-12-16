
    <?php $__env->startPush('scripts'); ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');
            const statusSelect = document.getElementById('car_status');
            const saleWrapper = document.getElementById('sale-price-wrapper');
            const bookingWrapper = document.getElementById('booking-price-wrapper');
            const soldWrapper = document.getElementById('sold-price-wrapper');
            const soldPriceInput = document.getElementById('sold_price_usd');
            const confirmClearSale = document.getElementById('confirm_clear_sale');
            const confirmClearBoth = document.getElementById('confirm_clear_both');
            const originalStatus = "<?php echo e(old('car_status', $carProfile->car_status ?? 'new_arrival')); ?>";
            const receiveAccountWrapper = document.getElementById('receive-account-wrapper');

            function toggleFieldsByStatus(status) {
                saleWrapper?.classList.toggle('d-none', status !== 'ready_for_sale');
                bookingWrapper?.classList.toggle('d-none', status !== 'ready_for_sale');
                <?php if (\Illuminate\Support\Facades\Blade::check('role', 'manager')): ?>
                    bookingWrapper?.classList.toggle('d-none', status !== 'booked');
                <?php endif; ?>
                soldWrapper?.classList.toggle('d-none', status !== 'sold');
                receiveAccountWrapper?.classList.toggle('d-none', status !== 'sold');
                const accountField = document.getElementById('account_id');

                if (accountField) {
                    if (status === 'sold') {
                        accountField.setAttribute('required', 'required');
                    } else {
                        accountField.removeAttribute('required');
                    }
                }
            }

            function revertStatus() {
                statusSelect.value = lastValidStatus;
                toggleFieldsByStatus(lastValidStatus);
            }

            toggleFieldsByStatus(statusSelect.value);
            let lastValidStatus = originalStatus; 

            statusSelect.addEventListener('change', function () {
                const newStatus = this.value;

                if (newStatus === 'pending_sold') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Not Allowed',
                        text: '"Pending Sold" cannot be selected directly.',
                    }).then(() => {
                        revertStatus();
                    });
                    return;
                }

                <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin')): ?>
                    if (newStatus === 'booked') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Not Allowed',
                            text: 'Please select "Ready for Sale" to add booking price.',
                        }).then(() => {
                            revertStatus();
                        });
                        return;
                    }
                <?php endif; ?>

                // if (lastValidStatus === 'ready_for_sale' && !['under_maintenance', 'sold'].includes(newStatus)) {
                if (lastValidStatus === 'ready_for_sale') {
                    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'manager')): ?>
                        if (['booked', 'sold', 'under_maintenance'].includes(newStatus)) {
                            lastValidStatus = newStatus;
                            toggleFieldsByStatus(newStatus);
                            return;
                        }
                    <?php else: ?>
                        if (['sold', 'under_maintenance'].includes(newStatus)) {
                            lastValidStatus = newStatus;
                            toggleFieldsByStatus(newStatus);
                            return;
                        }
                    <?php endif; ?>
                    
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'This will clear the Sale and Booking Price.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, clear it',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            confirmClearSale.value = '1';
                            lastValidStatus = newStatus; 
                            toggleFieldsByStatus(newStatus);
                        } else {
                            revertStatus();
                        }
                    });
                    return;
                }

                if (lastValidStatus === 'sold' && newStatus !== 'sold') {
                    const soldPriceValue = soldPriceInput?.value.trim();
                    if (soldPriceValue) {
                        Swal.fire({
                            title: 'Are you sure?',
                            text: 'This will clear Sold Prices.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, clear it',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                confirmClearBoth.value = '1';
                                lastValidStatus = newStatus; 
                                toggleFieldsByStatus(newStatus);
                            } else {
                                revertStatus();
                            }
                        });
                        return;
                    }
                }
                
                <?php if (\Illuminate\Support\Facades\Blade::check('role', 'manager')): ?>
                    const notAllowedStatuses = ['new_arrival', 'rejected'];
                    if (notAllowedStatuses.includes(newStatus)) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Not Allowed',
                            text: `Managers cannot change status to "${newStatus.replace('_', ' ')}".`,
                        }).then(() => {
                            revertStatus();
                        });

                        return;
                    }
                <?php endif; ?>
                
                lastValidStatus = newStatus;
                toggleFieldsByStatus(newStatus);

            });
        });
        
        //////////////// for chassis duplication /////////////////////
          $(document).ready(function () {
            let chassisField = $('#chassis');
            let errorField = $('#chassis-error');
            chassisField.on('input', function () {
                errorField.text('');
            });
            
            $('#chassis').on('blur', function () {
                let chassis = $(this).val().trim();
                let id = $(this).data('id'); 

                if (chassis === '') return;

                $.ajax({
                    url: '<?php echo e(route("check.chassis")); ?>',
                    type: 'POST',
                    data: {
                        chassis: chassis,
                        id: id,
                        _token: '<?php echo e(csrf_token()); ?>'
                    },
                    success: function (response) {
                        if (response.exists) {
                            errorField.text('This chassis number already exists.');
                        }
                    },
                    error: function () {
                        errorField.text('Error checking chassis number.');
                    }
                });
            });
        });
        
    </script>

  <?php $__env->stopPush(); ?><?php /**PATH /home/malikautonet/public_html/resources/views/dashboards/car_profiles/partials/status-script.blade.php ENDPATH**/ ?>