<?php echo $__env->make('dashboards.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="container-fluid page-body-wrapper">
  <?php echo $__env->make('dashboards.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <style>
    .table-header{
      background-color: #6495ed;
    }
  </style>
  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-sm-8 offset-sm-2">
          <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center table-header">
              <h4 class="card-title text-white mb-0">Transfer Amount</h4>
                <a href="<?php echo e(route('accounts.journal-voucher')); ?>" class="btn btn-primary py-2">Back</a>
            </div>
            <div class="card-body">

              <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
                    <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <script>
                    setTimeout(function () {
                        let alert = document.getElementById('error-alert');
                        if (alert) {
                            alert.classList.remove('show');
                            alert.classList.add('fade');
                            alert.style.display = 'none';
                        }
                    }, 4000);
                </script>
              <?php endif; ?>

              <?php if($errors->any()): ?>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Account Error',
                        text: "<?php echo e($errors->first()); ?>",
                    });
                </script>
              <?php endif; ?>
              
              <form method="POST" action="<?php echo e(route('accounts.store-voucher')); ?>">
                <?php echo csrf_field(); ?>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label>Transfer From</label>
                    <select name="from_account" id="from_account" class="form-control text-dark" required>
                      <option value="">-- Select Account --</option>
                      <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($acc->id); ?>" data-balance="<?php echo e($acc->opening_balance); ?>"><?php echo e($acc->title); ?> [<?php echo e($acc->country->currency_type); ?>] (<?php echo e($acc->country->name); ?>)</option>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label>Available Balance</label>
                    <input type="text" id="available_balance" class="form-control" disabled>
                  </div>
                </div>

                <div class="mb-3">
                  <label>Receive In</label>
                  <select name="to_account" class="form-control text-dark" required>
                    <option value="">-- Select Account --</option>
                    <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <option value="<?php echo e($acc->id); ?>"><?php echo e($acc->title); ?> [<?php echo e($acc->country->currency_type); ?>] (<?php echo e($acc->country->name); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label>Amount <small>(In transfer from account currency)</small></label>
                  <input type="number" step="0.01" oninput="this.value = this.value.replace(/[^0-9.]/g, '')" name="amount" class="form-control" required>
                </div>

                <div class="mb-3">
                  <label>Description</label>
                  <textarea name="jv_description" class="form-control" rows="3" required></textarea>
                </div>

                <button type="submit" class="btn btn-success">Add</button>
                <a href="<?php echo e(route('accounts.journal-voucher')); ?>" class="btn btn-danger">Cancel</a>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php $__env->startPush('scripts'); ?>
      <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fromAccount = document.getElementById('from_account');
            const balanceField = document.getElementById('available_balance');

            fromAccount.addEventListener('change', function () {
                const selectedOption = fromAccount.options[fromAccount.selectedIndex];
                const balance = selectedOption.dataset.balance || '';
                balanceField.value = balance;
            });
        });
      </script>
    <?php $__env->stopPush(); ?>
    <?php echo $__env->make('dashboards.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  </div>
</div><?php /**PATH /home/malikautonet/public_html/resources/views/dashboards/accounts/create-voucher.blade.php ENDPATH**/ ?>