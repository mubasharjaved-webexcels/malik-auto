<div class="row">
    <!-- Country -->
    <div class="col-md-6 mb-3">
        <label for="country_id" class="form-label">Country</label>
        <div class="position-relative">
            <i class="bi bi-chevron-down position-absolute text-muted" style="right: 15px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
            <select name="country_id" id="country_id" class="form-select text-dark" required>
                <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin')): ?>
                    <?php if(isset($carProfile)): ?>
                        <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($country->id); ?>"
                                <?php echo e(old('country_id', $carProfile->country_id) == $country->id ? 'selected' : ''); ?>>
                                <?php echo e($country->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <?php
                            $defaultCountry = $countries->firstWhere('id', 1);
                        ?>
                        <option value="<?php echo e($defaultCountry->id); ?>" selected><?php echo e($defaultCountry->name); ?></option>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (\Illuminate\Support\Facades\Blade::check('role', 'manager')): ?>
                    <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($country->id); ?>"
                            <?php echo e(old('country_id', $carProfile->country_id ?? $country->id) == $country->id ? 'selected' : ''); ?>>
                            <?php echo e($country->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </select>
        </div>
    </div>
    
    <!-- Car Status -->
    <div class="col-md-6 mb-3">
        <label for="car_status" class="form-label">Car Status</label>
        <div class="position-relative">
            <i class="bi bi-chevron-down position-absolute text-muted" style="right: 15px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
            <select name="car_status" id="car_status" class="form-select text-dark" required>
                <option value="">Select Status</option>
                    <?php
                        $statuses = [
                            'new_arrival' => 'New Arrival',
                            'ready_for_sale' => 'Ready For Sale',
                            'sold' => 'Sold',
                            'in_transit' => 'In Transit',
                            'under_maintenance' => 'Under Maintenance',
                            'pending_sold' => 'Pending Sold',
                            'rejected' => 'Rejected',
                            'booked' => 'Booked',
                        ];
                        $selectedStatus = old('car_status', $carProfile->car_status ?? 'new_arrival');
                    ?>
                    <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    
                        <?php if($value === 'ready_for_sale' && auth()->user()->hasRole('manager')): ?>
                            <option value="<?php echo e($value); ?>" <?php echo e($selectedStatus == $value ? 'selected' : ''); ?>>
                                Available For Sale
                            </option>
                        <?php else: ?>
                            <option value="<?php echo e($value); ?>" <?php echo e($selectedStatus == $value ? 'selected' : ''); ?>>
                                <?php echo e($label); ?>

                            </option>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
    </div>

    <?php $status = old('car_status', $carProfile->car_status ?? null); ?>

    <?php $localCurrency = $currencyType ?? 'JPY';  ?>

    
    <?php if (! \Illuminate\Support\Facades\Blade::check('role', 'manager')): ?>
    <div id="sale-price-wrapper" class="col-md-6 mb-3 <?php echo e($status === 'ready_for_sale' ? '' : 'd-none'); ?>">
        <label for="sale_price_usd" class="form-label">Sale Price</label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input
                type="number"
                min="0"
                step="0.01"
                oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                name="sale_price"
                id="sale_price_usd"
                value="<?php echo e(old('sale_price', $carProfile->sale_price ?? '')); ?>"
                class="form-control <?php $__errorArgs = ['sale_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
            >

            <span class="input-group-text"><?php echo e($localCurrency); ?></span>
            <input
                type="number"
                min="0"
                step="0.01"
                oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                id="sale_price_local"
                class="form-control"
            >
        </div>
        <?php $__errorArgs = ['sale_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div id="booking-price-wrapper" class="col-md-6 mb-3 <?php echo e($status === 'ready_for_sale' ? '' : 'd-none'); ?>">
        <label for="booking_price_usd" class="form-label">Booking Price</label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input
                type="number"
                min="0"
                step="0.01"
                oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                name="booking_price"
                id="booking_price_usd"
                value="<?php echo e(old('booking_price', $carProfile->booking_price ?? '')); ?>"
                class="form-control <?php $__errorArgs = ['booking_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
            >

            <span class="input-group-text"><?php echo e($localCurrency); ?></span>
            <input
                type="number"
                min="0"
                step="0.01"
                oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                id="booking_price_local"
                class="form-control"
            >
        </div>
        <?php $__errorArgs = ['booking_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <?php endif; ?>

    
    <div id="sold-price-wrapper" class="col-md-6 mb-3 <?php echo e($status === 'sold' ? '' : 'd-none'); ?>">
        <label for="sold_price_usd" class="form-label">Sold Price</label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input
                type="number"
                min="0"
                step="0.01"
                oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                name="sold_price"
                id="sold_price_usd"
                value="<?php echo e(old('sold_price', $carProfile->sold_price ?? '')); ?>"
                class="form-control <?php $__errorArgs = ['sold_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                <?php if (\Illuminate\Support\Facades\Blade::check('role', 'manager')): ?> required <?php endif; ?>
            >

            <span class="input-group-text"><?php echo e($localCurrency); ?></span>
            <input
                type="number"
                min="0"
                step="0.01"
                oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                id="sold_price_local"
                class="form-control"
            >
        </div>
        <?php $__errorArgs = ['sold_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <?php if (! \Illuminate\Support\Facades\Blade::check('role', 'admin')): ?>
    <div id="booking-price-wrapper" class="col-md-6 mb-3 <?php echo e($status === 'booked' ? '' : 'd-none'); ?>">
        <label for="booking_price_usd" class="form-label">Booking Price</label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input
                type="number"
                name="booking_price"
                id="booking_price_usd"
                value="<?php echo e(old('booking_price', $carProfile->booking_price ?? '')); ?>"
                class="form-control <?php $__errorArgs = ['booking_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" disabled
            >

            <span class="input-group-text"><?php echo e($localCurrency); ?></span>
            <input
                type="number"
                id="booking_price_local"
                class="form-control" disabled
            >
        </div>
        <?php $__errorArgs = ['booking_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <?php endif; ?>
    <?php if (! \Illuminate\Support\Facades\Blade::check('role', 'manager')): ?>
    <div id="receive-account-wrapper" class="col-md-6 mb-3 d-none">
        <label for="account_id" class="form-label">Receive in Account</label>
        <div class="position-relative">
            <i class="bi bi-chevron-down position-absolute text-muted" style="right: 15px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
            <select name="account_id" id="account_id" class="form-select text-dark">
                <option value="">Select Account</option>
                <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($account->id); ?>" <?php echo e(old('account_id', $carProfile->account_id ?? '') == $account->id ? 'selected' : ''); ?>>
                        [<?php echo e(ucfirst($account->type)); ?>] <?php echo e($account->title); ?> (<?php echo e($account->country->currency_type); ?>)
                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
    </div>
    <?php endif; ?>

    <!-- Confirmation Flags -->
    <input type="hidden" name="confirm_clear_sale" id="confirm_clear_sale" value="0">
    <input type="hidden" name="confirm_clear_both" id="confirm_clear_both" value="0">

    <div class="col-md-6 mb-3">
        <label for="located_yard" class="form-label">Located Yard</label>
        <div class="position-relative">
            <i class="bi bi-chevron-down position-absolute text-muted" style="right: 15px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
            <select name="located_yard" id="located_yard" class="form-select text-dark" required>
                <option value="">Select Yard</option>
            </select>
        </div>
        
        <div id="add-yard-wrapper" class="mt-2 d-none">
            <input type="text" id="new_yard" name="new_yard" class="form-control mt-2" placeholder="Enter New Yard">
        </div>
        
    </div>

    <!-- Car name -->
    <div class="col-md-6 mb-3">
        <label for="car_name" class="form-label">Car name</label>
        <input type="text" name="car_name" class="form-control" value="<?php echo e(old('car_name', $carProfile->car_name ?? '')); ?>">
    </div>

    <!-- Grade -->
    <div class="col-md-6 mb-3">
        <label for="grade" class="form-label">Grade</label>
        <input type="text" name="grade" class="form-control" value="<?php echo e(old('grade', $carProfile->grade ?? '')); ?>" required>
    </div>

    <!-- Seats -->
    <div class="col-md-6 mb-3">
        <label for="seats" class="form-label">Seats</label>
        <input type="number" name="seats" class="form-control" value="<?php echo e(old('seats', $carProfile->seats ?? '')); ?>" min="0" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
    </div>

    <!-- Chassis -->
    <div class="col-md-6 mb-3">
        <label for="chassis" class="form-label">Chassis</label>
        <input type="text" name="chassis" id="chassis" data-id="<?php echo e($carProfile->id ?? ''); ?>" class="form-control" value="<?php echo e(old('chassis', $carProfile->chassis ?? '')); ?>" required>
        <small id="chassis-error" class="text-danger"></small>
    </div>

    <!-- Shift -->
    <div class="col-md-6 mb-3">
        <label for="shift" class="form-label">Shift</label>
        <input type="text" name="shift" class="form-control" value="<?php echo e(old('shift', $carProfile->shift ?? '')); ?>" required>
    </div>

    <!-- Mileage -->
    <div class="col-md-6 mb-3">
        <label for="mileage" class="form-label">Mileage</label>
        <input type="number" name="mileage" class="form-control" value="<?php echo e(old('mileage', $carProfile->mileage ?? '')); ?>" min="0" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
    </div>

    <!-- Engine CC -->
    <div class="col-md-6 mb-3">
        <label for="engine_cc" class="form-label">Engine CC</label>
        <input type="number" name="engine_cc" class="form-control" value="<?php echo e(old('engine_cc', $carProfile->engine_cc ?? '')); ?>" min="0" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
    </div>

    <!-- Dimension -->
    <div class="col-md-6 mb-3">
        <label for="dimension" class="form-label">Model Year</label>
        <input type="text" name="dimension" class="form-control" value="<?php echo e(old('dimension', $carProfile->dimension ?? '')); ?>" >
    </div>

    <!-- M3 -->
    
    
    <?php if (! \Illuminate\Support\Facades\Blade::check('role', 'manager')): ?>
    <!-- Price -->
    <div class="col-md-6 mb-3">
        <label for="price_usd" class="form-label">Price</label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" min="0" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')" step="0.01" name="price" id="price_usd"
                class="form-control" value="<?php echo e(old('price', $carProfile->price ?? '')); ?>" required>

            <span class="input-group-text">(JPY)</span>
            <input type="number" min="0" oninput="this.value = this.value.replace(/[^0-9]/g, '')" step="0.01" id="price_jpy"
                class="form-control" value="" required>
        </div>
    </div>
    <?php endif; ?>

    <!-- Fuel -->
    <div class="col-md-6 mb-3">
        <label for="fuel" class="form-label">Fuel Type</label>
        <input type="text" oninput="this.value = this.value.replace(/[^a-zA-Z]/g, '')" name="fuel" class="form-control" value="<?php echo e(old('fuel', $carProfile->fuel ?? '')); ?>" required>
    </div>

    <!-- Max Loading -->
    <div class="col-md-6 mb-3">
        <label for="max_loading" class="form-label">Max Loading</label>
        <input type="text" name="max_loading" class="form-control" value="<?php echo e(old('max_loading', $carProfile->max_loading ?? '')); ?>" required>
    </div>

    <!-- Transit Expense -->
    

    <!-- Car Image -->
    <div class="row mb-3">
        <div class="col-md-3 mb-3">
            <label for="car_image" class="form-label">Car Image</label>
            <input type="file" name="car_image" class="form-control" accept=".jpg,.jpeg,.png" <?php if(!isset($carProfile)): ?> required <?php endif; ?>>
            <small class="text-muted" style="font-size: 12px;">Accepted formats: .jpg, .jpeg, .png (Max: 2MB)</small>
            <?php if(!empty($carProfile->car_image)): ?>
                <img src="<?php echo e(asset('storage/'.$carProfile->car_image)); ?>" alt="Car Image" class="mt-2" style="max-height: 100px;">
            <?php endif; ?>
        </div>
        <div class="col-md-3 mb-3">
            <label for="car_gallery" class="form-label">Car Gallery</label>
            <input 
                type="file" 
                name="car_gallery[]" 
                class="form-control" 
                accept=".jpg,.jpeg,.png" 
                multiple 
                <?php if(!isset($carProfile)): ?> required <?php endif; ?>
            >
            <small class="text-muted" style="font-size: 12px;">
                Accepted formats: .jpg, .jpeg, .png (Max: 2MB each)
            </small>
        
            <?php if(isset($carProfile) && $carProfile->galleryImages): ?>
                <div class="mt-2 d-flex flex-wrap gap-2">
                    <?php $__currentLoopData = $carProfile->galleryImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <img src="<?php echo e(asset('storage/'.$image->path)); ?>" alt="Car Image" style="max-height: 80px;">
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>


        <!-- Car Video -->
        <div class="col-md-6 mb-3">
            <label for="car_video" class="form-label">Car Video</label>
            <input type="file" name="car_video" class="form-control" <?php if(!isset($carProfile)): ?> <?php endif; ?>>
            <?php if(!empty($carProfile->car_video)): ?>
                <video width="200" height="150" controls class="mt-2">
                    <source src="<?php echo e(asset('storage/'.$carProfile->car_video)); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="mt-3 d-flex">
    <button type="submit" class="btn btn-primary">
        <?php echo e(isset($carProfile) ? 'Update' : 'Add'); ?> Car
    </button>

    <a href="<?php echo e(route('car-profiles.index')); ?>" class="btn btn-secondary ms-2">
        Back to List
    </a>
</div>
<?php /**PATH /home/malikautonet/public_html/resources/views/dashboards/car_profiles/partials/form.blade.php ENDPATH**/ ?>