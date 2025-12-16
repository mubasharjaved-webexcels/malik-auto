
    <?php $__env->startPush('scripts'); ?>
    <script>
        const isManager = <?php echo json_encode(auth()->user()->hasRole('manager'), 15, 512) ?>;
        document.addEventListener('DOMContentLoaded', function () {
            const countrySelect = document.getElementById('country_id');
            const yardSelect = document.getElementById('located_yard');
            const newYardWrapper = document.getElementById('add-yard-wrapper');
            const newYardInput = document.getElementById('new_yard');

            // Store initial selected values for edit mode
            const selectedCountryId = countrySelect.value;
            const selectedYard = <?php echo json_encode(old('located_yard', $carProfile->located_yard ?? ''), 512) ?>;

            // use Laravel-generated route to avoid hardcoding
            const routeTemplate = `<?php echo e(route('yards.by.country', ['countryId' => '__ID__'])); ?>`;

            // Function to populate yards
            function loadYards(countryId, selected = null) {
            yardSelect.innerHTML = '<option value="">Loading...</option>';
            newYardWrapper.classList.add('d-none');
            newYardInput.required = false;

            // Replace hardcoded URL with Laravel route
            const url = routeTemplate.replace('__ID__', countryId);

            // fetch(`/yards-by-country/${countryId}`)
             fetch(url)
                .then(res => res.json())
                .then(data => {
                    yardSelect.innerHTML = '';
                    let yardFound = false;

                    if (data.yards.length > 0) {
                        data.yards.forEach(yard => {
                            const option = new Option(yard, yard, false, yard === selected);
                            yardSelect.appendChild(option);
                            if (yard === selected) yardFound = true;
                        });
                        
                        if (!isManager) {
                            const addOption = new Option('Add New Yard', '__add_new__', false, false);
                            yardSelect.appendChild(addOption);
                        }
                        if (!yardFound && selected) {
                            yardSelect.value = '__add_new__';
                            newYardWrapper.classList.remove('d-none');
                            newYardInput.required = true;
                            newYardInput.value = selected;
                        }

                    } else {
                        // No yards found at all
                        if (!isManager) {
                            const addOption = new Option('Add New Yard', '__add_new__', true, true);
                            yardSelect.appendChild(addOption);

                            newYardWrapper.classList.remove('d-none');
                            newYardInput.required = true;
                            newYardInput.value = '';
                        } else {
                            yardSelect.innerHTML = '<option value="">No Yard Available</option>';
                        }
                    }
                });
            }


            // Initial load (important for edit)
            if (selectedCountryId) {
                loadYards(selectedCountryId, selectedYard);
            }

            // On country change
            countrySelect.addEventListener('change', function () {
                loadYards(this.value, null);
            });

            // On yard dropdown change
            yardSelect.addEventListener('change', function () {
                if (this.value === '__add_new__') {
                    newYardWrapper.classList.remove('d-none');
                    newYardInput.required = true;
                } else {
                    newYardWrapper.classList.add('d-none');
                    newYardInput.required = false;
                }
            });

            // On form submit
            document.querySelector('form').addEventListener('submit', function (e) {
                if (yardSelect.value === '__add_new__') {
                    const newValue = newYardInput.value.trim();
                    if (!newValue) {
                        e.preventDefault();
                        alert("Please enter new yard name.");
                        return;
                    }
                    const newOption = new Option(newValue, newValue, true, true);
                    yardSelect.appendChild(newOption);
                    yardSelect.value = newValue;
                }
            });
      });
    </script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const carStatus = document.querySelector('select[name="car_status"]');
            const salePriceWrapper = document.getElementById('sale-price-wrapper');
            const soldPriceWrapper = document.getElementById('sold-price-wrapper'); // might be null
            const soldPriceField = document.getElementById('sold_price_usd');

            function togglePriceFields() {
                const status = carStatus.value;
                if (salePriceWrapper) {
                    if (status === 'ready_for_sale') {
                        salePriceWrapper.classList.remove('d-none');
                    } else {
                        salePriceWrapper.classList.add('d-none');
                    }
                }

                if (soldPriceWrapper) {
                    if (status === 'sold') {
                        soldPriceWrapper.classList.remove('d-none');
                        if (soldPriceField) {
                            soldPriceField.setAttribute('required', true);
                        }
                    } else {
                        soldPriceWrapper.classList.add('d-none');
                        if (soldPriceField) {
                            soldPriceField.removeAttribute('required');
                        }
                    }
                }
                
                // if (status === 'ready_for_sale') {
                //     salePriceWrapper.classList.remove('d-none');
                //     if (soldPriceWrapper) soldPriceWrapper.classList.add('d-none');
                // } else if (status === 'sold') {
                //     if (soldPriceWrapper) soldPriceWrapper.classList.remove('d-none');
                //     salePriceWrapper.classList.add('d-none');
                // } else {
                //     salePriceWrapper.classList.add('d-none');
                //     if (soldPriceWrapper) soldPriceWrapper.classList.add('d-none');
                // }
            }

            carStatus.addEventListener('change', togglePriceFields);
            togglePriceFields(); // Run on page load
        });
    </script>

    
    
  <?php $__env->stopPush(); ?><?php /**PATH /home/malikautonet/public_html/resources/views/dashboards/car_profiles/partials/yard-script.blade.php ENDPATH**/ ?>