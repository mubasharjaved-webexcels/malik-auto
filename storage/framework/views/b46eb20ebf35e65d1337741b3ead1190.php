<?php if($carExpenses->isEmpty()): ?>
  <tr>
      <td colspan="8">
          <div class="alert alert-warning text-center shadow-sm rounded m-0">
              <i class="mdi mdi-alert-circle-outline"></i>
              No car expenses found.
          </div>
      </td>
  </tr>
<?php else: ?>
  <?php $__currentLoopData = $carExpenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <tr>
    <td style="padding-top: 0px;padding-bottom: 0px;">
      
        <?php if($expense->carProfile && $expense->carProfile->car_image): ?>
          <img src="<?php echo e(asset('storage/' . $expense->carProfile->car_image)); ?>" style="width: 40px; height: 30px; margin-left:30px;" class="me-2">
        <?php else: ?>
          <img src="<?php echo e(asset('images/no-image.png')); ?>" style="width: 40px; height: 30px;" class="me-2">
        <?php endif; ?>
        <span><?php echo e($expense->carProfile->rec_no ?? 'N/A'); ?></span>
      
    </td>
    <td><?php echo e($expense->expenses_for); ?></td>
    <td><?php echo e($expense->account->title ?? 'N/A'); ?></td>
    <td><?php echo e(number_format($expense->amount, 2)); ?></td>
    <td><?php echo e($expense->currency); ?></td>
    <td>
      $<?php echo e(number_format($expense->usd_amount, 2)); ?>

      <small class="text-muted d-block">
        (<?php echo e(number_format($expense->amount, 2)); ?> <?php echo e($expense->currency); ?> ÷ <?php echo e(number_format($expense->currencyInfo->currency_rate ?? 1, 2)); ?>)
      </small>
    </td>
    <td><?php echo e($expense->creator->name ?? 'N/A'); ?></td>
    <td><?php echo e($expense->created_at->format('Y-m-d H:i')); ?></td>
    <td>...</td>
  </tr>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?><?php /**PATH C:\wamp64\www\malik_auto\resources\views/dashboards/car_expenses/partials/expense_listing.blade.php ENDPATH**/ ?>