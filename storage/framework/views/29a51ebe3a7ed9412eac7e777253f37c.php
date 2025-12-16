<?php echo $__env->make('dashboards.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="container-fluid page-body-wrapper">
  <?php echo $__env->make('dashboards.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <div class="main-panel">
    <div class="content-wrapper">
      <div class="container">
        <h2 class="text-2xl font-semibold mb-4">Car Profile Details</h2>
          <div class="row">
              <!-- Rec No -->
              <div class="col-md-6 mb-3">
                  <label>Rec No:</label>
                  <p><?php echo e($carProfile->rec_no); ?></p>
              </div>

              <!-- Located Yard -->
              <div class="col-md-6 mb-3">
                  <label>Located Yard:</label>
                  <p><?php echo e($carProfile->located_yard); ?></p>
              </div>
          </div>

          <div class="row">
              <!-- Grade -->
              <div class="col-md-6 mb-3">
                  <label>Grade:</label>
                  <p><?php echo e($carProfile->grade); ?></p>
              </div>

              <!-- Seats -->
              <div class="col-md-6 mb-3">
                  <label>Seats:</label>
                  <p><?php echo e($carProfile->seats); ?></p>
              </div>
          </div>

          <div class="row">
              <!-- Chassis -->
              <div class="col-md-6 mb-3">
                  <label>Chassis:</label>
                  <p><?php echo e($carProfile->chassis); ?></p>
              </div>

              <!-- Shift -->
              <div class="col-md-6 mb-3">
                  <label>Shift:</label>
                  <p><?php echo e($carProfile->shift); ?></p>
              </div>
          </div>

          <div class="row">
              <!-- Mileage -->
              <div class="col-md-6 mb-3">
                  <label>Mileage:</label>
                  <p><?php echo e($carProfile->mileage); ?></p>
              </div>

              <!-- Engine CC -->
              <div class="col-md-6 mb-3">
                  <label>Engine CC:</label>
                  <p><?php echo e($carProfile->engine_cc); ?></p>
              </div>
          </div>

          <div class="row">
              <!-- Dimension -->
              <div class="col-md-6 mb-3">
                  <label>Model Year:</label>
                  <p><?php echo e($carProfile->dimension); ?></p>
              </div>

              <!-- M3 -->
              <div class="col-md-6 mb-3">
                  <label>M3:</label>
                  <p><?php echo e($carProfile->m3); ?></p>
              </div>
          </div>

            <?php if (! \Illuminate\Support\Facades\Blade::check('role', 'manager|salesperson')): ?>
            <div class="row">
              <!-- Price -->
              <div class="col-md-6 mb-3">
                  <label>Price:</label>
                  <p><?php echo e($carProfile->price); ?></p>
              </div>

              <div class="col-md-6 mb-3">
                  <label>Sale Price:</label>
                  <p><?php echo e($carProfile->sale_price); ?></p>
              </div>
            </div>
            <?php endif; ?>

          <div class="row">
            <?php if (! \Illuminate\Support\Facades\Blade::check('role', 'manager|salesperson')): ?>
              <div class="col-md-6 mb-3">
                  <label>Sold Price:</label>
                  <p><?php echo e($carProfile->sold_price); ?></p>
              </div>
            <?php endif; ?>

              <!-- Fuel -->
              <div class="col-md-6 mb-3">
                  <label>Fuel Type:</label>
                  <p><?php echo e($carProfile->fuel); ?></p>
              </div>
          </div>

          <div class="row">
              <!-- Max Loading -->
              <div class="col-md-6 mb-3">
                  <label>Max Loading:</label>
                  <p><?php echo e($carProfile->max_loading); ?></p>
              </div>

              <!-- Country -->
              <div class="col-md-6 mb-3">
                  <label>Country:</label>
                  <p><?php echo e($carProfile->country->name ?? 'N/A'); ?></p>
              </div>
          </div>

          <div class="row">
              <!-- Car Status -->
              <div class="col-md-6 mb-3">
                  <label>Car Status:</label>
                  <p><?php echo e(ucfirst(str_replace('_', ' ', $carProfile->car_status))); ?></p>
              </div>
              <!-- Transit Expense -->
              <div class="col-md-6 mb-3">
                  <label>Transit Expense:</label>
                  <p><?php echo e(ucfirst(str_replace('_', ' ', $carProfile->transit_expense))); ?></p>
              </div>
          </div>

          <div class="row">
              <!-- Currency Type -->
              <div class="col-md-6 mb-3">
                  <label>Currency Type:</label>
                  <p><?php echo e(ucfirst(str_replace('_', ' ', $carProfile->currency_type))); ?></p>
              </div>
          </div>

          <div class="row">
              <!-- Car Image -->
              <div class="col-md-6 mb-3">
                  <label>Car Image:</label>
                  <?php if(!empty($carProfile->car_image)): ?>
                      <img src="<?php echo e(asset('storage/'.$carProfile->car_image)); ?>" alt="Car Image" style="max-height: 200px;">
                  <?php else: ?>
                      <p>No image available</p>
                  <?php endif; ?>
              </div>

              <!-- Car Video -->
              <div class="col-md-6 mb-3">
                  <label>Car Video:</label>
                  <?php if(!empty($carProfile->car_video)): ?>
                      <video width="200" height="150" controls>
                          <source src="<?php echo e(asset('storage/'.$carProfile->car_video)); ?>" type="video/mp4">
                          Your browser does not support the video tag.
                      </video>
                  <?php else: ?>
                      <p>No video available</p>
                  <?php endif; ?>
              </div>
          </div>

          <div class="col-md-12 mt-3">
              <a href="<?php echo e(route('car-profiles.edit', $carProfile)); ?>" class="btn btn-primary">Edit</a>
              <a href="<?php echo e(route('car-profiles.index')); ?>" class="btn btn-secondary">Back to List</a>
          </div>
      </div>
    </div>

    <?php echo $__env->make('dashboards.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  </div> <!-- main-panel -->
</div> <!-- page-body-wrapper -->
<?php /**PATH /home/malikautonet/public_html/resources/views/dashboards/car_profiles/show.blade.php ENDPATH**/ ?>