<?php echo $__env->make('dashboards.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="container-fluid page-body-wrapper">
  <?php echo $__env->make('dashboards.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <style>
    .dropdown-toggle::after {
      display: none !important;
    }
  </style>
<?php
 $currencies = \App\Models\Country::select('currency_type')
                ->whereNotNull('currency_type')
                ->distinct()
                ->pluck('currency_type');
?>
  <div class="main-panel">
    
    
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
    
    <div id="dynamic-success" class="alert alert-success alert-dismissible fade show d-none" role="alert">
        <span id="dynamic-success-message"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    
    <div id="dynamic-danger" class="alert alert-danger alert-dismissible fade show d-none" role="alert">
        <span id="dynamic-danger-message"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

      <div class="row px-3 py-4">
        <div class="col-sm-12">
          <div class="card">
            
            <div class="text-bg-light pt-2 px-3 pb-0 card-header d-flex justify-content-between align-items-center">
              <h4 class="card-title mb-0">Car Expenses</h4>
              <div class="d-flex align-items-center">
                <form action="<?php echo e(route('car-expense.search')); ?>" method="GET" class="d-flex align-items-left" style="margin-bottom: 5px;">
                  <input type="text" 
                    id="car-expense-search"
                    name="search" 
                    value="<?php echo e(request('search')); ?>" 
                    class="form-control form-control-sm" 
                    style="width:300px; height: 31px; margin-right:17px;" 
                    placeholder="Search by Stock no or Expense name...">
                </form>
                <button type="button" style="font-size:12px;border-radius: 5px; margin-bottom:5px;" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCarExpenseModal">
                  <i class="mdi mdi-plus" style="font-size: 12px;"></i> Add Car Expenses
                </button>
              </div>
            </div>
            <div class="card-body">
              <form id="filterForm">
                
                <h6 class="text-muted mb-3">Filter by Country, Manager & Record No</h6>
                <div class="row mb-4">
                  <?php if (! \Illuminate\Support\Facades\Blade::check('role', 'manager|salesperson')): ?>
                  
                  <div class="col-md-3">
                    <select id="country-filter" class="form-select text-dark">
                      <option value="">Countries</option>
                      <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($country->id); ?>"><?php echo e($country->name); ?></option>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                  </div>

                  
                  <div class="col-md-3">
                    <select id="manager-filter" class="form-select text-dark" disabled>
                      <option value="">Select Managers</option>
                    </select>
                  </div>
                  <?php endif; ?>
                  
                  <div class="col-md-3">
                    <select id="recno-filter" class="form-select text-dark" disabled>
                      <option value="">Rec Nos</option>
                    </select>
                  </div>

                  
                  <div class="col-md-3">
                    <button id="reset-filters" class="btn btn-secondary w-100">
                      <i class="mdi mdi-refresh"></i> Reset Filters
                    </button>
                  </div>
                </div>
              </form>

             <div class="table-responsive">
                <table id="carExpensesTable" class="table table-striped table-bordered w-auto" style="width: auto; min-width: 100%; overflow-x: visible;">
                  <thead>
                    <tr>
                      <th>Stock Number</th>
                      <th>Expenses Name</th>
                      <th>Paid From</th>
                      <th>Amount</th>
                      <th>Currency</th>
                      <th>Total (USD)</th>
                      <th>Created By</th>
                      <th>Date</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody id="car-list">
                    <?php echo $__env->make('dashboards.car_expenses.partials.expense_listing', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                  </tbody>
                  <tfoot id="car-expense-tfoot">
                    <tr>
                      <th colspan="5" class="text-end"><strong>Grand Total:</strong></th>
                      <th id="car-expense-grand-total">
                        <strong class="text-success">$<?php echo e(number_format($carExpenses->sum('usd_amount'), 2)); ?></strong>
                        <small class="text-muted d-block">Total USD Amount</small>
                      </th>
                      <th colspan="3"></th>
                    </tr>
                  </tfoot>
                </table>
                <?php if($carExpenses->hasPages()): ?>
                    <div class="mt-4 d-flex justify-content-center">
                        <?php echo e($carExpenses->links('pagination::bootstrap-5')); ?>

                    </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div> <!-- col-sm-12 -->
      </div> <!-- row -->

      
      <div class="modal fade" id="addCarExpenseModal" tabindex="-1" aria-labelledby="addCarExpenseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="addCarExpenseModalLabel">Add Car Expense</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCarExpenseForm" method="POST" action="<?php echo e(route('car-expenses.store')); ?>">
              <?php echo csrf_field(); ?>
              <div class="modal-body">
                <div class="row">
                  <div class="col-12">
                    <div class="form-group mb-3">
                      <label for="car_profile_id" class="form-label">Select Car <span class="text-danger">*</span></label>
                      <select class="form-select text-dark" id="car_profile_id" name="car_profile_id" required>
                        <option value="">Choose a car</option>
                        <?php $__currentLoopData = $carProfiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $car): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                          <option value="<?php echo e($car->id); ?>" 
                                  data-rec-no="<?php echo e($car->rec_no); ?>"
                                  data-chassis="<?php echo e($car->chassis); ?>"
                                  data-image="<?php echo e($car->car_image ? asset('storage/' . $car->car_image) : asset('images/no-image.png')); ?>">
                            <?php echo e($car->rec_no); ?>

                          </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                      </select>
                      <div class="invalid-feedback"></div>
                      
                      
                      <div id="selectedCarPreview" class="mt-2 d-none">
                        <div class="card border">
                          <div class="card-body p-2">
                            <div class="d-flex align-items-center">
                              <img id="previewImage" src="" alt="Car Image" class="rounded me-2" style="width: 50px; height: 40px; object-fit: cover;">
                              <div>
                                <small class="text-muted">Selected:</small>
                                <div id="previewText" class="fw-bold small"></div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-12">
                    <div class="form-group mb-3">
                      <label for="expenses_for" class="form-label">Expenses Name <span class="text-danger">*</span></label>
                      <input type="text" class="form-control text-dark" id="expenses_for" name="expenses_for" required placeholder="e.g., Fuel, Maintenance, Repair">
                      <div class="invalid-feedback"></div>
                    </div>
                  </div>

                  <div class="col-md-12">
                    <div class="form-group mb-3">
                      <label for="accountSelect" class="form-label">Pay From Account <span class="text-danger">*</span></label>
                      <select class="form-select text-dark" id="accountSelect" name="account_id" required>
                        <option value="">Select Account</option>
                      </select>
                      <div class="invalid-feedback"></div>
                    </div>
                  </div>

                  
                  
                  

                  <div class="row">
                    <!-- Left: User Input in Selected Currency -->
                    <div class="col-md-6">
                      <div class="form-group mb-3">
                        <label for="localAmount" class="form-label text-dark">
                          Amount in your currency <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                          <span class="input-group-text" id="currency_type_in_amount">(JPY)</span>
                          <input type="number" step="0.01" class="form-control" id="localAmount" name="amount" placeholder="Enter local amount">
                        </div>
                      </div>
                    </div>


                    <!-- Right: USD Amount -->
                    <div class="col-md-6">
                      <div class="form-group mb-3">
                        <label for="usdAmount" class="form-label text-dark">
                          Amount in USD
                        </label>
                        <div class="input-group">
                          <span class="input-group-text">$</span>
                          <input type="number" step="0.01" class="form-control" id="usdAmount" name="usdAmount" placeholder="Enter USD amount">
                        </div>
                      </div>
                    </div>
                  </div>

                  

                  <div class="col-md-6">
                    <div class="form-group mb-3">
                      <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                      <select class="form-select text-dark" id="currency" name="currency" required readonly>
                        <option value="">Select Currency</option>
                        <?php $__currentLoopData = $currencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $currency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                          <option value="<?php echo e($currency); ?>"><?php echo e($currency); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                      </select>
                      <div class="invalid-feedback"></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveCarExpenseBtn">
                  <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                  Save
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      
      <div class="modal fade" id="editCarExpenseModal" tabindex="-1" aria-labelledby="editCarExpenseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editCarExpenseModalLabel">Edit Car Expense</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCarExpenseForm" method="POST">
              <?php echo csrf_field(); ?>
              <?php echo method_field('PUT'); ?>
              <div class="modal-body">
                <div class="row">
                  <div class="col-12">
                    <div class="form-group mb-3">
                      <label for="edit_car_profile_id" class="form-label">Select Car <span class="text-danger">*</span></label>
                      <select class="form-select" id="edit_car_profile_id" name="car_profile_id" required>
                        <option value="">Choose a car...</option>
                        <?php $__currentLoopData = $carProfiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $car): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                          <option value="<?php echo e($car->id); ?>"
                                  data-rec-no="<?php echo e($car->rec_no); ?>"
                                  data-chassis="<?php echo e($car->chassis); ?>"
                                  data-image="<?php echo e($car->car_image ? asset('storage/' . $car->car_image) : asset('images/no-image.png')); ?>">
                            <?php echo e($car->rec_no); ?> - <?php echo e($car->chassis ?? 'N/A'); ?>

                          </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                      </select>
                      <div class="invalid-feedback"></div>
                      
                      
                      <div id="editSelectedCarPreview" class="mt-2 d-none">
                        <div class="card border">
                          <div class="card-body p-2">
                            <div class="d-flex align-items-center">
                              <img id="editPreviewImage" src="" alt="Car Image" class="rounded me-2" style="width: 50px; height: 40px; object-fit: cover;">
                              <div>
                                <small class="text-muted">Selected:</small>
                                <div id="editPreviewText" class="fw-bold small"></div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-12">
                    <div class="form-group mb-3">
                      <label for="edit_expenses_for" class="form-label">Expenses Name <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="edit_expenses_for" name="expenses_for" required placeholder="e.g., Fuel, Maintenance, Repair">
                      <div class="invalid-feedback"></div>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="form-group mb-3">
                      <label for="edit_amount" class="form-label">Amount <span class="text-danger">*</span></label>
                      <input type="number" step="0.01" class="form-control" id="edit_amount" name="amount" required placeholder="0.00">
                      <div class="invalid-feedback"></div>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="form-group mb-3">
                      <label for="edit_currency" class="form-label">Currency <span class="text-danger">*</span></label>
                      <select class="form-select" id="edit_currency" name="currency" required>
                        <option value="">Select Currency</option>
                        <?php $__currentLoopData = $currencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $currency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                          <option value="<?php echo e($currency); ?>"><?php echo e($currency); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                      </select>
                      <div class="invalid-feedback"></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="updateCarExpenseBtn">
                  <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                  Update
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  
</div>


<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>


<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<?php $__env->stopPush(); ?>

<script>
$(document).ready(function() {
    // Initialize DataTable with client-side processing
    // var table = $('#carExpensesTable').DataTable({
    //     order: [[0, 'desc']],
    //     responsive: true,
    //     pageLength: 10,
    //     language: {
    //         processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>'
    //     }
    // });

    // Add Car Expense Form Submit
    $('#addCarExpenseForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var btn = $('#saveCarExpenseBtn');
        var spinner = btn.find('.spinner-border');
        
        // Reset previous validation states
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').text('');
        
        // Show loading state
        btn.prop('disabled', true);
        spinner.removeClass('d-none');
        $('#currency').prop('disabled', false);
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if(response.success) {
                    // Hide modal
                    $('#addCarExpenseModal').modal('hide');
                    
                    // Reset form
                    form[0].reset();
                    
                    // Show success message
                    showDynamicAlert('success', response.message || 'Car expense added successfully!');
                    
                    // Reload page to show new data
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    // Validation errors
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        var input = form.find('[name="' + key + '"]');
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(value[0]);
                    });
                } else if(xhr.status === 400 && xhr.responseJSON.error) {
                    // showDynamicAlert('danger', xhr.responseJSON.error);
                    var amountField = form.find('[name="amount"]');
                    amountField.addClass('is-invalid');
                    amountField.siblings('.invalid-feedback').text(xhr.responseJSON.error);
                }  else {
                    showDynamicAlert('danger', 'An error occurred while saving the car expense.');
                }
            },
            complete: function() {
                // Hide loading state
                btn.prop('disabled', false);
                spinner.addClass('d-none');
                $('#currency').prop('disabled', true); 
            }
        });
    });
    /////////////////// Bank and cash accounts ///////////////////
    const getAccountsUrl = "<?php echo e(url('accounts/get-accounts')); ?>";

    $('#addCarExpenseModal').on('show.bs.modal', function () {
        $.get(getAccountsUrl, function (data) {
            const accountSelect = $('#accountSelect');
            const currencyField = $('#currency');

            accountSelect.html('<option value="">Select Account</option>');
            currencyField.html('<option value="">Select Currency</option>');
            currencyField.prop('disabled', true); // initially disabled

            data.forEach(acc => {
                accountSelect.append(`
                    <option value="${acc.id}" data-currency="${acc.currency_type}">
                        ${acc.title} (${acc.currency_type}${acc.yard ? ' - ' + acc.yard : ''})
                    </option>
                `);
            });

            /////// start amount field split by Abdul Rauf ////////
            const localAmount = $('#localAmount');
            const usdAmount = $('#usdAmount');
            const currency_type_in_amount = $('#currency_type_in_amount');

            localAmount.prop('disabled', true);
            usdAmount.prop('disabled', true);

            localAmount.off('input').on('input', function () {
              const selectedOption = accountSelect.find('option:selected');
              const selectedCurrency = selectedOption.data('currency');
              const currencyRates = <?php echo json_encode($currencyRates, 15, 512) ?>;
              const conversionRate = parseFloat(currencyRates[selectedCurrency]);
              const localVal = parseFloat(localAmount.val());
              
              if (!isNaN(localVal) && (conversionRate)) {
                usdAmount.val((localVal / conversionRate).toFixed(2));
              } 
            });

            usdAmount.off('input').on('input', function () {
              const selectedOption = accountSelect.find('option:selected');
              const selectedCurrency = selectedOption.data('currency');
              const currencyRates = <?php echo json_encode($currencyRates, 15, 512) ?>;
              const conversionRate = parseFloat(currencyRates[selectedCurrency]);
              const localVal = parseFloat(usdAmount.val());
              
              if (!isNaN(localVal) && (conversionRate)) {
                localAmount.val((localVal * conversionRate).toFixed(2));
              } 
            });

            /////// end amount field split by Abdul Rauf ////////

            // Reset currency field on account change
            accountSelect.off('change').on('change', function () {
                const selectedOption = $(this).find('option:selected');
                const selectedCurrency = selectedOption.data('currency');

                if (selectedCurrency) {

                  currencyField.html(`<option value="${selectedCurrency}" selected>${selectedCurrency}</option>`);
                  currencyField.prop('disabled', true);

                  /////// start around view change by Abdul Rauf ////////
                  currency_type_in_amount.text(`(${selectedCurrency})`);
                  localAmount.prop('disabled', false);
                  usdAmount.prop('disabled', false);

                  const currencyRates = <?php echo json_encode($currencyRates, 15, 512) ?>;
                  const conversionRate = parseFloat(currencyRates[selectedCurrency]);
                  const localVal = parseFloat(localAmount.val());
                  
                  if (!isNaN(localVal) && (conversionRate)) {
                    usdAmount.val((localVal / conversionRate).toFixed(2));
                  } 
                  /////// end  view change by Abdul Rauf ////////
                  
                } else {
                    currencyField.html('<option value="">Select Currency</option>');
                    currency_type_in_amount.text("");
                    /////// start amount field split by Abdul Rauf ////////
                    currencyField.prop('disabled', true);
                    localAmount.prop('disabled', true);
                    usdAmount.prop('disabled', true);
                    /////// end amount field split Abdul Rauf ////////

                }
            });
        });
    });
    /////////////////// Bank and cash accounts ///////////////////

    // Edit Car Expense
    $(document).on('click', '.edit-car-expense', function() {
        var id = $(this).data('id');
        
        $.ajax({
            url: "<?php echo e(route('car-expenses.show', ':id')); ?>".replace(':id', id),
            method: 'GET',
            success: function(response) {
                $('#edit_car_profile_id').val(response.car_profile_id);
                $('#edit_expenses_for').val(response.expenses_for);
                $('#edit_amount').val(response.amount);
                $('#edit_currency').val(response.currency);
                
                // Trigger change event to show car preview
                $('#edit_car_profile_id').trigger('change');
                
                $('#editCarExpenseForm').attr('action', "<?php echo e(route('car-expenses.update', ':id')); ?>".replace(':id', id));
                $('#editCarExpenseModal').modal('show');
            },
            error: function() {
                showDynamicAlert('danger', 'Error loading car expense data.');
            }
        });
    });

    // Update Car Expense Form Submit
    $('#editCarExpenseForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var btn = $('#updateCarExpenseBtn');
        var spinner = btn.find('.spinner-border');
        
        // Reset previous validation states
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').text('');
        
        // Show loading state
        btn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if(response.success) {
                    // Hide modal
                    $('#editCarExpenseModal').modal('hide');
                    
                    // Show success message
                    showDynamicAlert('success', response.message || 'Car expense updated successfully!');
                    
                    // Reload page to show updated data
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    // Validation errors
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        var input = form.find('[name="' + key + '"]');
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(value[0]);
                    });
                } else {
                    showDynamicAlert('danger', 'An error occurred while updating the car expense.');
                }
            },
            complete: function() {
                // Hide loading state
                btn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Delete Car Expense
    $(document).on('click', '.delete-car-expense', function() {
        var id = $(this).data('id');
        
        if(confirm('Are you sure you want to delete this car expense?')) {
            $.ajax({
                url: "<?php echo e(route('car-expenses.destroy', ':id')); ?>".replace(':id', id),
                method: 'DELETE',
                data: {
                    _token: '<?php echo e(csrf_token()); ?>'
                },
                success: function(response) {
                    if(response.success) {
                        showDynamicAlert('success', response.message || 'Car expense deleted successfully!');
                        
                        // Reload page to show updated data
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    }
                },
                error: function() {
                    showDynamicAlert('danger', 'Error deleting car expense.');
                }
            });
        }
    });

    // Show Dynamic Alert Function
    function showDynamicAlert(type, message) {
        var alertDiv = $('#dynamic-' + type);
        var messageSpan = $('#dynamic-' + type + '-message');
        
        messageSpan.text(message);
        alertDiv.removeClass('d-none');
        
        // Auto hide after 5 seconds
        setTimeout(function() {
            alertDiv.addClass('d-none');
        }, 5000);
    }

    // Car Selection Preview for Add Modal
    $('#car_profile_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var carImage = selectedOption.data('image');
        var recNo = selectedOption.data('rec-no');
        var chassis = selectedOption.data('chassis');
        
        if ($(this).val()) {
            $('#previewImage').attr('src', carImage);
            // $('#previewText').text(recNo + ' - ' + (chassis || 'N/A'));
            $('#previewText').text(recNo);
            $('#selectedCarPreview').removeClass('d-none');
        } else {
            $('#selectedCarPreview').addClass('d-none');
        }
    });

    // Car Selection Preview for Edit Modal
    $('#edit_car_profile_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var carImage = selectedOption.data('image');
        var recNo = selectedOption.data('rec-no');
        var chassis = selectedOption.data('chassis');
        
        if ($(this).val()) {
            $('#editPreviewImage').attr('src', carImage);
            $('#editPreviewText').text(recNo + ' - ' + (chassis || 'N/A'));
            $('#editSelectedCarPreview').removeClass('d-none');
        } else {
            $('#editSelectedCarPreview').addClass('d-none');
        }
    });

    // Reset modal forms when hidden
    $('#addCarExpenseModal').on('hidden.bs.modal', function() {
        $('#addCarExpenseForm')[0].reset();
        $('#addCarExpenseForm').find('.is-invalid').removeClass('is-invalid');
        $('#addCarExpenseForm').find('.invalid-feedback').text('');
        $('#selectedCarPreview').addClass('d-none');
    });

    $('#editCarExpenseModal').on('hidden.bs.modal', function() {
        $('#editCarExpenseForm').find('.is-invalid').removeClass('is-invalid');
        $('#editCarExpenseForm').find('.invalid-feedback').text('');
        $('#editSelectedCarPreview').addClass('d-none');
    });
});

// Filter by Country, Manager & Record No

  document.addEventListener('DOMContentLoaded', function () {
    const userIsManager = <?php echo json_encode(auth()->user()->hasRole('manager'), 15, 512) ?>;
    const country = document.getElementById('country-filter');
    const manager = document.getElementById('manager-filter');
    const recNo = document.getElementById('recno-filter');
    const resetBtn = document.getElementById('reset-filters');
    const tableBody = document.querySelector('#carExpensesTable tbody');
    const grandTotalFoot = document.getElementById('car-expense-tfoot');

    function resetDropdown(dropdown, label = "Select") {
        dropdown.innerHTML = `<option value="">${label}</option>`;
        dropdown.disabled = true;
    }

    function fetchFilter(data = {}) {
      fetch("<?php echo e(route('car-expenses.filter')); ?>", {
          method: "POST",
          headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': "<?php echo e(csrf_token()); ?>"
          },
          body: JSON.stringify(data)
      })
      .then(res => res.json())
      .then(res => {
          if (res.managers && manager) {
              resetDropdown(manager, "Select Managers");
              manager.disabled = false;
              res.managers.forEach(m => {
                  manager.innerHTML += `<option value="${m.id}">${m.name}</option>`;
              });
          }
          if (res.rec_nos && recNo) {
             // Store selected value
              const selectedRecNo = recNo.value;
              resetDropdown(recNo, "Select Rec No");
              recNo.disabled = false;

              res.rec_nos.forEach(r => {
                  const selected = r === selectedRecNo ? 'selected' : '';
                  recNo.innerHTML += `<option value="${r}" ${selected}>${r}</option>`;
              });
          }

          if (res.grand_total_usd !== undefined) {
              document.getElementById('car-expense-grand-total').innerHTML = `
                  <strong class="text-success">$${res.grand_total_usd}</strong>
                  <small class="text-muted d-block">Total USD Amount</small>
              `;
          }

          if (res.expenses) {
              tableBody.innerHTML = res.expenses;
          }
      });
    }

    // Role-based behavior
    if (userIsManager) {
        fetchFilter();

        recNo.addEventListener('change', () => {
            fetchFilter({ rec_no: recNo.value });
        });

    } else {
        country.addEventListener('change', () => {
            resetDropdown(manager, "Select Manager");
            resetDropdown(recNo, "Select Rec No");
            fetchFilter({ country_id: country.value });
        });

        manager.addEventListener('change', () => {
            resetDropdown(recNo, "Select Rec No");
            fetchFilter({ country_id: country.value, manager: manager.value });
        });

        recNo.addEventListener('change', () => {
            fetchFilter({
                country_id: country.value,
                manager: manager.value,
                rec_no: recNo.value
            });
        });
    }

    resetBtn.addEventListener('click', () => {
        if (!userIsManager) {
            country.value = '';
            resetDropdown(manager, "Select Manager");
        }
        resetDropdown(recNo, "Select Rec No");
        fetchFilter(); // Reset expenses
    });
  });
  
  //////////// car expense search

  document.addEventListener('DOMContentLoaded', function () {
    if (!window.location.search.includes('search=')) {
        document.querySelector('input[name="search"]').value = '';
    }

    const searchInput = document.getElementById('car-expense-search');
    const resultsContainer = document.getElementById('car-list');
    let timer = null;

    searchInput.addEventListener('keyup', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            $.ajax({
                url: "<?php echo e(route('car-expense.search')); ?>",
                type: "GET",
                data: { search: searchInput.value },
                success: function (data) {
                    document.getElementById('car-list').innerHTML = data.tbody;
                    document.querySelector('#car-expense-tfoot').innerHTML = data.tfoot;
                }
            });
        }, 400);
    });
  });

</script>

<?php echo $__env->make('dashboards.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\wamp64\www\malik_auto\resources\views/dashboards/car_expenses/index.blade.php ENDPATH**/ ?>