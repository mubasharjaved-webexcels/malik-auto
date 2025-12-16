
  <?php $__currentLoopData = $processedExpenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
      <td><?php echo e($key+1); ?></td>
      <td><?php echo e($expense->country->name ?? 'N/A'); ?></td>
      
      <td><?php echo e($expense->expense_name); ?></td>
      <td><?php echo e($expense->account->title ?? 'N/A'); ?></td>
      <td><?php echo e(number_format($expense->amount, 2)); ?></td>
      <td>
        $<?php echo e(number_format($expense->usd_amount, 2)); ?>

        <small class="text-muted d-block">
          (<?php echo e(number_format($expense->amount, 2)); ?> <?php echo e($expense->currency); ?> ÷ <?php echo e(number_format($expense->currencyInfo->currency_rate ?? 1, 2)); ?>)
        </small>
      </td>
      <td><?php echo e($expense->creator->name ?? 'Unknown'); ?></td>
      <td><?php echo e($expense->created_at->format('d-m-Y')); ?></td>
      <td>
        <button class="btn btn-sm btn-warning edit-btn" data-id="<?php echo e($expense->id); ?>">Edit</button>
        <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo e($expense->id); ?>">Delete</button>
      </td>
    </tr>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php /**PATH C:\wamp64\www\malik_auto\resources\views/dashboards/office_expenses/partials/office_expense_rows.blade.php ENDPATH**/ ?>