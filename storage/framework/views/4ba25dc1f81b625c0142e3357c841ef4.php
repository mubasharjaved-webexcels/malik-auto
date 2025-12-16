<?php echo $__env->make('dashboards.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="container-fluid page-body-wrapper">
  <?php echo $__env->make('dashboards.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<style>
  .pending-list td {
      padding: 7px 15px 7px 15px;
  }
</style>
  <div class="main-panel">
    <div class="content-wrapper px-2">
      
      
      <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <script>
            setTimeout(() => {
                const alert = document.getElementById('success-alert');
                if (alert) {
                    alert.classList.remove('show');
                    alert.classList.add('fade');
                    alert.style.display = 'none';
                }
            }, 4000);
        </script>
      <?php elseif(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
            <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <script>
            setTimeout(() => {
                const alert = document.getElementById('error-alert');
                if (alert) {
                    alert.classList.remove('show');
                    alert.classList.add('fade');
                    alert.style.display = 'none';
                }
            }, 4000);
        </script>
      <?php endif; ?>

      
      <div class="row">
        <div class="col-sm-12">
          <div class="card shadow-sm">
            <div class="text-bg-light card-header d-flex justify-content-between align-items-center py-3">
              <h4 class="card-title mb-0">Pending Booking & Sold Approvals</h4>
            </div>
            <div class="card-body">
              <div class="table-responsive px-2">
                <table class="table table-bordered table-hover pending-list">
                  <thead>
                    <tr>
                      <th>Rec No</th>
                      <th>Country</th>
                      <th>Manager Name</th>
                      <th>Purchase Price</th>
                      <th>Sale Price</th>
                      <th>Expenses</th>
                      <th>Suggested Price</th>
                      <th>Booking Price</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $getPendingRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $car): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                      <tr>
                        <td><?php echo e($car->rec_no); ?></td>
                        <td><?php echo e($car->country->name); ?></td>
                        <td><?php echo e($car->assignedManager ? $car->assignedManager->name : 'N/A'); ?></td>
                        <td>$<?php echo e(number_format($car->price, 2) ?: 'N/A'); ?></td>
                        <td>$<?php echo e(number_format($car->sale_price, 2) ?: 'N/A'); ?></td>
                        <td>$<?php echo e(number_format($car->expenses_sum_usd_amount, 2) ?: 'N/A'); ?></td>
                        <td>$<?php echo e(number_format($car->suggested_sold_price, 2) ?: 'N/A'); ?></td>
                        <td>$<?php echo e(number_format($car->booking_price, 2) ?: 'N/A'); ?></td>
                        <td>
                          <?php if($car->car_status === 'pending_sold'): ?>
                            <button type="submit" class="btn btn-success btn-sm approve-btn" data-car-id="<?php echo e($car->id); ?>" 
                              data-type="sold" 
                              data-is-booked="<?php echo e($car->is_booked); ?>"
                              data-suggested="<?php echo e($car->suggested_sold_price); ?>"
                              data-booking="<?php echo e($car->booking_price); ?>">
                              Approve Sold Price
                            </button>
                            <form action="<?php echo e(route('car_profiles.processPendingRequestAction', $car->id)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?> 
                                <?php echo method_field('PUT'); ?>
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="type" value="sold">
                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                          <?php elseif($car->car_status === 'booked'): ?>
                            <button type="submit" class="btn btn-success btn-sm approve-btn"
                                data-car-id="<?php echo e($car->id); ?>"
                                data-type="booked">
                                Approve Booking Price
                            </button>
                            <form action="<?php echo e(route('car_profiles.processPendingRequestAction', $car->id)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?> 
                                <?php echo method_field('PUT'); ?>
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="type" value="booked">
                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                      <tr>
                        <td colspan="9" class="text-center">
                            <div class="alert alert-warning text-center shadow-sm rounded mb-0 d-inline-block mx-auto">
                                <i class="mdi mdi-alert-circle-outline"></i>
                                No pending booking and sold approvals.
                            </div>
                        </td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div> <!-- card -->
        </div> <!-- col -->
      </div> <!-- row -->
    </div> <!-- content-wrapper -->
    <!-- Approval Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form id="approveForm" method="POST">
          <?php echo csrf_field(); ?>
          <?php echo method_field('PUT'); ?>
          <input type="hidden" name="action" value="approve">
          <input type="hidden" name="car_id" id="modal_car_id">
          <input type="hidden" name="type" id="modal_type">

          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="approveModalLabel">Select Account to Confirm Sale & Booking Amounts</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div id="approval-info" class="alert alert-info d-none"></div>
            
            <div class="modal-body">
              <div class="mb-3">
                <label for="account_id" class="form-label">Select Bank/Cash Account</label>
                <select class="form-select text-dark" name="account_id" id="account_id" required>
                  <option value="">Select Account</option>
                  <?php $__currentLoopData = $bankAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($account->id); ?>">
                      [<?php echo e(ucfirst($account->type)); ?>] <?php echo e($account->title); ?> (<?php echo e($account->country->currency_type); ?>)
                    </option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
            </div>

            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">Confirm Approval</button>
            </div>
          </div>
        </form>
      </div>
    </div>
    <!-- Approval Modal -->
    <?php echo $__env->make('dashboards.car_profiles.partials.yard-script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('dashboards.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const approveButtons = document.querySelectorAll('.approve-btn');
        const modal = new bootstrap.Modal(document.getElementById('approveModal'));
        const carIdInput = document.getElementById('modal_car_id');
        const approveForm = document.getElementById('approveForm');
        const typeInput = document.getElementById('modal_type');
        const approvalInfo = document.getElementById('approval-info');

        approveButtons.forEach(btn => {
          btn.addEventListener('click', function () {
            const carId = this.dataset.carId;
            const type  = this.dataset.type ;
            const isBooked = parseInt(this.dataset.isBooked);
            const suggested = parseFloat(this.dataset.suggested || 0);
            const booking   = parseFloat(this.dataset.booking || 0);
            carIdInput.value = carId;
            typeInput.value = type;
            approveForm.action = `/car-profiles/${carId}/handle-pending-requests`;

              if (isBooked === 1) {
                const remaining = (suggested - booking).toFixed(2);
                approvalInfo.classList.remove('d-none');
                approvalInfo.innerHTML = `
                  <strong>Note:</strong> The booking price ($${booking.toLocaleString()}) 
                  will be subtracted from the suggested sold price ($${suggested.toLocaleString()}).
                  <br/>Final amount to approve: <strong>$${remaining.toLocaleString()}</strong>
                `;
              } else {
                approvalInfo.classList.add('d-none');
                approvalInfo.innerHTML = '';
              }

            modal.show();
          });
        });
      });
    </script>
  </div> <!-- main-panel -->
</div> <!-- page-body-wrapper -->
<?php /**PATH /home/malikautonet/public_html/resources/views/dashboards/car_profiles/pending_sold.blade.php ENDPATH**/ ?>