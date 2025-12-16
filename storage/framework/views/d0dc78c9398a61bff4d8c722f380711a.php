
<?php echo $__env->make('dashboards.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="container-fluid page-body-wrapper">
  <?php echo $__env->make('dashboards.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

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
              
              <h4 class="card-title">Currency Rates</h4>
              <h5 class="mb-4 text-dark text-center"><strong>1 USD = Base Currency</strong></h5>

              <div class="table-responsive">
                <table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Country</th>
                      <th>Currency Rate</th>
                      <th>Currency Type</th>
                      <th>Last Updated</th>
                      <?php if (! \Illuminate\Support\Facades\Blade::check('role', 'manager')): ?>
                      <th>Action</th>
                      <?php endif; ?>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                      <tr>
                        <td><?php echo e($index + 1); ?></td>
                        <td><?php echo e($country->name); ?></td>
                        <td><?php echo e(floatval($country->currency_rate) ?? 'N/A'); ?></td>
                        <td><?php echo e($country->currency_type ?? 'N/A'); ?></td>
                        <td class="text-muted small mb-0"><?php echo e($country->updated_at->diffForHumans()); ?></td>
                      <?php if (! \Illuminate\Support\Facades\Blade::check('role', 'manager')): ?>
                        <td>
                          <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editCurrencyModal<?php echo e($country->id); ?>">
                            Edit
                          </a>
                          <a href="#" 
                            class="card-link btn btn-secondary text-primary show-exchange-rate" 
                            data-currency="<?php echo e($country->currency_type); ?>" 
                            data-country="<?php echo e($country->name); ?>"
                            data-bs-toggle="modal" 
                            data-bs-target="#currencyInfoModal">
                            Info
                          </a>
                        </td>
                      <?php endif; ?>
                      </tr>
                      <!-- Edit Currency Modal -->
                      <div class="modal fade" id="editCurrencyModal<?php echo e($country->id); ?>" tabindex="-1" aria-labelledby="editCurrencyModalLabel<?php echo e($country->id); ?>" aria-hidden="true">
                        <div class="modal-dialog">
                          <div class="modal-content">
                            <form action="<?php echo e(route('currencies.update', $country->id)); ?>" method="POST">
                              <?php echo csrf_field(); ?>
                              <?php echo method_field('PUT'); ?>

                              <div class="modal-header">
                                <h5 class="modal-title" id="editCurrencyModalLabel<?php echo e($country->id); ?>">Edit Currency</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>

                              <div class="modal-body">
                                

                                <div class="mb-3">
                                  <label for="currency_rate_<?php echo e($country->id); ?>" class="form-label">Currency Rate for 1 USD to  <?php echo e($country->currency_type); ?></label>
                                  <input type="number" step="0.01" name="currency_rate" id="currency_rate_<?php echo e($country->id); ?>" class="form-control" value="<?php echo e($country->currency_rate); ?>" required>
                                </div>
                              </div>

                              <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Update</button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                      <tr>
                        <td colspan="3" class="text-center">No data available</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div> <!-- col-sm-12 -->
      </div> <!-- row -->
    </div> <!-- content-wrapper -->

    <!-- Currency Info Modal -->
    <div class="modal fade" id="currencyInfoModal" tabindex="-1" aria-labelledby="currencyInfoLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="currencyInfoLabel">Currency Rate</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p id="currency-rate-text">Loading...</p>
          </div>
        </div>
      </div>
    </div>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.show-exchange-rate').forEach(function(button) {
        button.addEventListener('click', function () {
            const currency = this.getAttribute('data-currency');
            const country = this.getAttribute('data-country');
            const rateText = document.getElementById('currency-rate-text');
            rateText.textContent = "Fetching rate...";

            fetch(`https://api.exchangerate-api.com/v4/latest/USD?access_key=YOUR_API_KEY`)
                .then(response => response.json())
                .then(data => {
                    if (data.rates && data.rates[currency]) {
                        const rate = data.rates[currency];
                        // Convert timestamp to Pakistan Time (UTC+5)
                        const updatedTime = new Date(data.time_last_updated * 1000);
                        const pstTime = new Date(updatedTime.getTime() + (5 * 60 * 60 * 1000)); // Add 5 hours for PST
                        
                        // Format date/time (e.g., "29 Jul 2025, 15:30 PST")
                        const formattedTime = pstTime.toLocaleString('en-PK', {
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                            timeZone: 'UTC'
                        }) + " PKT";

                        rateText.innerHTML = `
                            1 USD = <strong>${rate.toFixed(4)} ${currency}</strong><br>
                            <small>(${country})</small><br>
                            <small style="color: #666;">Updated: ${formattedTime}</small>
                        `;
                    } else {
                        rateText.textContent = "Currency not supported.";
                    }
                })
                .catch((error) => {
                    rateText.textContent = "Error fetching rates. Please try again.";
                    console.error("Fetch Error:", error);
                });
        });
    });
});
</script>


    <?php echo $__env->make('dashboards.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  </div> <!-- main-panel -->
</div> <!-- page-body-wrapper -->
</div> <!-- container-scroller -->
<?php /**PATH /home/malikautonet/public_html/resources/views/dashboards/currency_rates/index.blade.php ENDPATH**/ ?>