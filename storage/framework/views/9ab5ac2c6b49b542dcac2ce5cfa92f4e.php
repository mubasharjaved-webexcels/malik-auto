
<?php echo $__env->make('dashboards.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="container-fluid page-body-wrapper">
  <?php echo $__env->make('dashboards.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <style>
    .profit-list td {
      padding: 1rem;
    }
    .table-header{
      background-color: #6495ed;color:#fff;
    }
    .table-header th{
      background-color: #6495ed;color:#fff;
    }
  </style>
  <div class="main-panel">
      <div class="content-wrapper px-2 py-0">
        <div class="row">
          <div class="col-sm-12">
            <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center table-header py-3">
              <h4 class="card-title text-white mb-0">Profit by Cars</h4>
            </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped mb-3">
                      <thead>
                          <tr class="table-header">
                              <th>#</th>
                              <th>Car Stock No</th>
                              <th>Auction Price</th>
                              <th>Expense</th>
                              <th>Sold price</th>
                              <th>Sold By</th>
                              <th>Profit & Loss</th>
                          </tr>
                      </thead>
                      <tbody>
                          
                        <?php $__empty_1 = true; $__currentLoopData = $profitByCars; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $profitByCar): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                          <tr class="profit-list">
                            <td><?php echo e($key+1); ?></td>
                            <td><?php echo e($profitByCar->rec_no); ?></td>
                            <td>$<?php echo e(number_format($profitByCar->price,2)); ?></td>
                            <td>$<?php echo e(number_format(($profitByCar->expenses_sum_usd_amount ?? 0), 2)); ?></td>
                            <td>$<?php echo e(number_format($profitByCar->sold_price,2)); ?></td>
                            <td><?php echo e($profitByCar->assignedManager->name ?? 'Admin'); ?></td>
                            <?php
                              $totalAmount = $profitByCar->price + ($profitByCar->expenses_sum_usd_amount ?? 0);
                              $profitOrLoss = $profitByCar->sold_price - $totalAmount;
                              $textClass = $totalAmount < $profitByCar->sold_price ? 'text-success' : 'text-danger';
                            ?>

                            <td class="<?php echo e($textClass); ?>">
                              <?php echo e(($profitOrLoss < 0 ? '-$' : '$') . number_format(abs($profitOrLoss), 2)); ?>

                            </td>
                          </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                          <tr><td colspan="7" class="text-center">No vouchers found</td></tr>
                        <?php endif; ?>
                      </tbody>
                  </table>
                    <?php if($profitByCars->hasPages()): ?>
                        <div class="mt-4 d-flex justify-content-center">
                            <?php echo e($profitByCars->links('pagination::bootstrap-5')); ?>

                        </div>
                    <?php endif; ?>
                </div>
              </div>
            </div>
          </div> <!-- col-sm-12 -->
        </div> <!-- row -->
      </div> <!-- content-wrapper -->

      <?php echo $__env->make('dashboards.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div> <!-- main-panel -->
  </div> <!-- page-body-wrapper -->
</div> <!-- container-scroller -->
<?php /**PATH /home/malikautonet/public_html/resources/views/dashboards/accounts/profit-by-cars.blade.php ENDPATH**/ ?>