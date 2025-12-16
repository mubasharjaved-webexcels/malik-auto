
<?php echo $__env->make('dashboards.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="container-fluid page-body-wrapper">
  <?php echo $__env->make('dashboards.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<style>
  .jv-list td {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
}
  .jv-head th{
     background-color: #6495ed;color:#fff;
  }
  .table-header{
    background-color: #6495ed;
  }
</style>
<div class="main-panel">
    <div class="content-wrapper px-2 py-0">
      <div class="row">
        <div class="col-sm-12">
          <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center table-header">
              <h4 class="card-title text-white mb-0">Journal vouchers</h4>
                    <a href="<?php echo e(route('accounts.create-voucher')); ?>" class="btn btn-primary py-2">Add JV</a>
            </div>
            <div class="card-body">
              <div class="table-responsive">
              
                <?php if(session('success')): ?>
                  <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
                    <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
                  <script>
                      setTimeout(function () {
                          let alert = document.getElementById('success-alert');
                          if (alert) {
                              alert.classList.remove('show');
                              alert.classList.add('fade');
                              alert.style.display = 'none';
                          }
                      }, 4000);
                  </script>
                <?php endif; ?>
                
                <table class="table table-bordered table-striped mb-3">
                    <thead>
                        <tr class="jv-head">
                            <th>Date</th>
                            <th>Country</th>
                            <th>Description</th>
                            <th>Opening Balance</th>
                            <th>Debit</th>
                            <th>Credit</th>
                            <th>Closing Balance</th>
                            <th>Currency</th>
                            <th>Transfer By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                      <?php $__empty_1 = true; $__currentLoopData = $journalVoucher; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="jv-list">
                          <td><?php echo e($jv->created_at->format('d/m/Y')); ?></td>
                          <td><?php echo e($jv->account->title ?? 'N/A'); ?></td>
                          <td><?php echo e($jv->transaction_type); ?></td>
                          <td><?php echo e(number_format($jv->opening_balance,2)); ?></td>
                          <td class="<?php echo e($jv->flow_type == 'debit' ? 'text-danger' : ''); ?>">
                              <?php if($jv->flow_type == 'debit'): ?>
                                  <?php echo e(number_format($jv->amount, 2)); ?>

                              <?php else: ?>
                                  <?php echo e(''); ?>

                              <?php endif; ?>
                          </td>
                          <td class="<?php echo e($jv->flow_type == 'credit' ? 'text-success' : ''); ?>">
                              <?php if($jv->flow_type == 'credit'): ?>
                                  <?php echo e(number_format($jv->amount, 2)); ?>

                              <?php else: ?>
                                  <?php echo e(''); ?>

                              <?php endif; ?>
                          </td>
                          <td><?php echo e(number_format($jv->closing_balance,2)); ?></td>
                          <td><?php echo e($jv->currency_type); ?></td>
                          <td><?php echo e($jv->user->name ?? 'System'); ?></td>
                          <td>
                            <a href="<?php echo e(route('accounts.jv-statement', ['jv' => $jv->jv_no])); ?>" class="btn btn-sm btn-success">Statement</a>
                          </td>
                        </tr>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="10" class="text-center">No vouchers found</td></tr>
                      <?php endif; ?>
                    </tbody>
                </table>
                  <?php if($journalVoucher->hasPages()): ?>
                      <div class="mt-4 d-flex justify-content-center">
                          <?php echo e($journalVoucher->links('pagination::bootstrap-5')); ?>

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
<?php /**PATH /home/malikautonet/public_html/resources/views/dashboards/accounts/journal-voucher.blade.php ENDPATH**/ ?>