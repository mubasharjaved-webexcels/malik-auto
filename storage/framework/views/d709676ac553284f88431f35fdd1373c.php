
<?php echo $__env->make('dashboards.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="container-fluid page-body-wrapper">
  <?php echo $__env->make('dashboards.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<style>
  .account-list td {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
}
</style>
<div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-sm-12">
          <div class="card shadow-sm">
            <div class="card-body">
              
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
              
              <div class="d-flex justify-content-between align-items-center">
                <h4 class="card-title"><?php echo e(ucfirst($type)); ?> Accounts</h4>
                <?php if (! \Illuminate\Support\Facades\Blade::check('role', 'manager|salesperson')): ?>
                  <a href="<?php echo e(route('accounts.create', $type)); ?>" class="btn btn-primary mb-3">Add <?php echo e(ucfirst($type)); ?> Account</a>
                <?php endif; ?>
              </div>
              <?php if($accounts->isEmpty()): ?>
                  <p>No <?php echo e($type); ?> accounts found.</p>
              <?php else: ?>
              <table class="table table-bordered table-striped">
                  <thead>
                      <tr>
                          <th>Title</th>
                          <th>Country</th>
                          <th>Currency</th>
                          
                          <th>Balance</th>
                          <th>Actions</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                          <tr class="account-list">
                              <td><?php echo e($acc->title); ?></td>
                              <td><?php echo e($acc->country->name); ?></td>
                              <td><?php echo e($acc->country->currency_type); ?></td>
                              
                              <td><?php echo e(number_format($acc->opening_balance, 2)); ?></td>
                              <td>
                                  <a href="<?php echo e(route('accounts.statement', ['account' => $acc->id, 'type' => $acc->type])); ?>" class="btn btn-sm btn-success">Statement</a>
                                <?php if (! \Illuminate\Support\Facades\Blade::check('role', 'manager|salesperson')): ?>
                                  <a href="<?php echo e(route('accounts.edit', $acc->id)); ?>" class="btn btn-sm btn-warning">Edit</a>
                                  
                                  <form method="POST" action="<?php echo e(route('accounts.toggleStatus', $acc->id)); ?>" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <button type="submit" class="btn btn-sm btn-<?php echo e($acc->status == 'active' ? 'danger' : 'success'); ?>">
                                      <?php echo e($acc->status == 'active' ? 'Deactivate' : 'Activate'); ?>

                                    </button>
                                  </form>
                                <?php endif; ?>
                              </td>
                          </tr>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </tbody>
              </table>
              <?php endif; ?>
            </div>
          </div>
        </div> <!-- col-sm-12 -->
      </div> <!-- row -->
    </div> <!-- content-wrapper -->

    <?php echo $__env->make('dashboards.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  </div> <!-- main-panel -->
</div> <!-- page-body-wrapper -->
</div> <!-- container-scroller -->
<?php /**PATH /home/malikautonet/public_html/resources/views/dashboards/accounts/index.blade.php ENDPATH**/ ?>