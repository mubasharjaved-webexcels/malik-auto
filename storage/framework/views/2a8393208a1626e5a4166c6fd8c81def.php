<?php echo $__env->make('dashboards.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="container-fluid page-body-wrapper">
  <?php echo $__env->make('dashboards.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <div class="main-panel">
    <div class="content-wrapper">
      <div class="container py-4">
        <h2 class="text-2xl font-semibold mb-4">Add Car Profile</h2>

        <form action="<?php echo e(route('car-profiles.store')); ?>" method="POST" enctype="multipart/form-data">
          <?php echo csrf_field(); ?>
        <?php echo $__env->make('dashboards.car_profiles.partials.form', ['carProfile' => null, 'countries' => $countries], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </form>
      </div>
    </div>
    
    <?php
      $jpyRate = optional(collect($countries)->firstWhere('currency_type', 'JPY'))->currency_rate;
    ?>
    
    <?php echo $__env->make('dashboards.car_profiles.partials.yard-script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('dashboards.car_profiles.partials.status-script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> 
    <?php echo $__env->make('dashboards.car_profiles.partials.price-conversion-script', ['jpyRate' => number_format($jpyRate, 6, '.', '')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> 
    <?php echo $__env->make('dashboards.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  </div> <!-- main-panel -->
</div> <!-- page-body-wrapper -->
<?php /**PATH /home/malikautonet/public_html/resources/views/dashboards/car_profiles/create.blade.php ENDPATH**/ ?>