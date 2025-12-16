{{-- resources/views/office_expenses/index.blade.php --}}

@include('dashboards.partials.header')
<div class="container-fluid page-body-wrapper">
  @include('dashboards.partials.sidebar')
  <style>
    .dropdown-toggle::after {
      display: none !important;
    }
    #expensesTable td {
      padding-top: 8px;
      padding-bottom: 8px;
  }
  #expensesTable .btn.btn-sm, .btn-group-sm > .btn {
    padding-top: 6px;
    padding-bottom: 8px;
}
  </style>

  <div class="main-panel">
    {{-- <div class="content-wrapper"> --}}
    {{-- ****************** success and error messages **************** --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
            {{ session('success') }}
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
    @endif
    
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
            <div class="card-header d-flex justify-content-between align-items-center text-bg-light">
              <h4 class="card-title mb-0">Office Expenses</h4>
              <div class="d-flex gap-2">
                
                <form action="{{ route('office-expense.search') }}" method="GET" class="d-flex align-items-left">
                  <input type="text" 
                    id="office-expense-search"
                    name="search" 
                    value="{{ request('search') }}" 
                    class="form-control form-control-sm" 
                    style="width:300px; height: 34px;" 
                    placeholder="Search by Expense name...">
                </form>

                <form id="export-form" action="{{ route('office-expenses.export') }}" method="POST">
                    @csrf
                    <input type="hidden" name="country_id" id="export-country-id">
                    {{-- <input type="hidden" name="yard" id="export-yard"> --}}
                    <input type="hidden" name="manager" id="export-manager">
                    <button type="submit" class="btn btn-success" id="office-expense-export-btn" style="font-size:12px;border-radius: 5px;padding-bottom: 8px; padding-top:8px;">
                        <i class="mdi mdi-download"></i> Download CSV
                    </button>
                </form>
                <button type="button" class="btn btn-primary" style="font-size:12px;border-radius: 5px;padding-bottom: 7px; padding-top:8px;" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                  <i class="mdi mdi-plus" style="font-size: 12px;"></i> Add Office Expense
                </button>
              </div>
            </div>
            <div class="card-body">
                {{-- Filter dropdowns --}}
                @unlessrole('manager')
                <h6 class="text-muted mb-3">Filter by Country & Manager</h6>
                <div class="row mb-4">
                  {{-- Country --}} 
                  <div class="col-md-3">
                    <select id="country-filter" class="form-select text-dark">
                      <option value="">Countries</option>
                      @foreach ($countries as $country)
                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                      @endforeach
                    </select>
                  </div>

                  {{-- Manager --}}
                  <div class="col-md-3">
                    <select id="manager-filter" class="form-select text-dark" disabled>
                      <option value="">Managers</option>
                    </select>
                  </div>

                  {{-- Yard --}}
                  {{-- <div class="col-md-3">
                    <select id="yard-filter" class="form-select text-dark" disabled>
                      <option value="">Yards</option>
                    </select>
                  </div> --}}

                  {{-- Reset Button --}}
                  <div class="col-md-3 ms-auto text-end">
                    <button id="reset-filters" class="btn btn-secondary w-100">
                      <i class="mdi mdi-refresh"></i> Reset Filters
                    </button>
                  </div>
                </div>
                @endunlessrole

              <div class="table-responsive">
                <table id="expensesTable" class="table table-striped table-hover table-bordered">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Country</th>
                      {{-- <th>Yard</th> --}}
                      <th>Expense Name</th>
                      <th>Paid From</th>
                      <th>Amount</th>
                      <th>Total(USD)</th>
                      <th>Created By</th>
                      <th>Date</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody id="expense-table-body">
                    @include('dashboards.office_expenses.partials.office_expense_rows', ['processedExpenses' => $processedExpenses])
                  </tbody>
                  <tfoot id="office-expense-tfoot">
                    <tr>
                      <th colspan="5" class="text-end"><strong>Grand Total</strong></th>
                      <th id="car-expense-grand-total">
                        <strong class="text-success">
                          ${{ number_format($processedExpenses->sum('usd_amount'), 2) }}
                        </strong>
                        <small class="text-muted d-block">Total USD Amount</small>
                      </th>
                      <th colspan="4"></th>
                    </tr>
                  </tfoot>
                </table>
                @if ($processedExpenses->hasPages())
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $processedExpenses->links('pagination::bootstrap-5') }}
                    </div>
                @endif
              </div>
            </div>
          </div>
        </div> <!-- col-sm-12 -->
      </div> <!-- row -->

    </div>
  {{-- </div> --}}
</div>

{{-- Add Expense Modal --}}
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addExpenseModalLabel">Add New Office Expense</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addExpenseForm">
        @csrf
        <div class="modal-body">
          @unlessrole('manager')
          <div class="mb-3">
            <label for="country_id" class="form-label">Country <span class="text-danger">*</span></label>
            <select class="form-select text-dark" id="country_id" name="country_id" required>
              <option value="">Select Country</option>
              @foreach($countries as $country)
                <option value="{{ $country->id }}">{{ $country->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label for="manager" class="form-label">Managers <span class="text-danger">*</span></label>
            <select class="form-select text-dark" id="assigned_manager_id" name="manager" required disabled>
              <option value="">First select country</option>
            </select>
          </div>
          @endunlessrole

          <div class="mb-3">
            <label for="expense_name" class="form-label">Expense Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="expense_name" name="expense_name" placeholder="Office rent" required>
          </div>

          <div class="mb-3">
            <label for="accountSelect" class="form-label">Pay From Account <span class="text-danger">*</span></label>
              <select class="form-select text-dark" id="accountSelect" name="account_id" required>
                <option value="">Select Account</option>
              </select>
          </div>
          
          {{-- /////// start amount field split by Abdul Rauf //////// --}}
                  
          {{--
          <div class="mb-3">
            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
            <div id="account-error" class="text-danger small mt-1"></div>
          </div>
          --}}

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
          {{-- /////// end amount field split by Abdul Rauf //////// --}}
          <div class="col-md-6">
            <div class="mb-3">
              <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
              <select class="form-select text-dark" id="currency" name="currency" required>
                <option value="">Select Currency</option>
                @foreach($currencies as $currency)
                  <option value="{{ $currency }}">{{ $currency }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            Save Expense
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Edit Expense Modal --}}
<div class="modal fade" id="editExpenseModal" tabindex="-1" aria-labelledby="editExpenseModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editExpenseModalLabel">Edit Office Expense</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editExpenseForm">
        @csrf
        @method('PUT')
        <input type="hidden" id="edit_expense_id" name="expense_id">
        <div class="modal-body">
          @unlessrole('manager')
          <div class="mb-3">
            <label for="edit_country_id" class="form-label">Country <span class="text-danger">*</span></label>
            <select class="form-select text-dark" id="edit_country_id" name="country_id" required>
              <option value="">Select Country</option>
              @foreach($countries as $country)
                <option value="{{ $country->id }}">{{ $country->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="edit_manager" class="form-label">Manager <span class="text-danger">*</span></label>
            <select class="form-select text-dark" id="edit_manager" name="manager" required>
              <option value="">Select manager</option>
            </select>
          </div>
          @endunlessrole
          {{-- <div class="mb-3">
            <label for="edit_yard" class="form-label">Yard <span class="text-danger">*</span></label>
            <select class="form-select text-dark" id="edit_yard" name="yard" required>
              <option value="">Select Yard</option>
            </select>
          </div> --}}
          <div class="mb-3">
            <label for="edit_expense_name" class="form-label">Expense Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control text-dark" id="edit_expense_name" name="expense_name" required>
          </div>
          
          <div class="mb-3">
            <label for="payFrom" class="form-label">Pay From Account <span class="text-danger">*</span></label>
              <select class="form-select text-dark" id="account_id" name="account_id" required>
                <option value="">Select Account</option>
                  @foreach($accounts as $account)
                    <option value="{{ $account->id }}">
                      [{{ ucfirst($account->type) }}] {{ $account->title }} ({{ $account->country->currency_type }})
                    </option>
                  @endforeach              
              </select>
          </div>
          
          {{-- /////// start amount field split by Abdul Rauf //////// --}}
                  
          {{--
          <div class="mb-3">
            <label for="edit_amount" class="form-label">Amount <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control text-dark" id="edit_amount" name="amount" required>
          </div>
          --}}

          <div class="row">
            <!-- Left: User Input in Selected Currency -->
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label for="edit_amount" class="form-label text-dark">
                  Amount in your currency <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text" id="edit_currency_type_in_amount">(JPY)</span>
                  <input type="number" step="0.01" class="form-control" id="edit_amount" name="amount" placeholder="Enter local amount">
                </div>
              </div>
            </div>


            <!-- Right: USD Amount -->
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label for="editUsdAmount" class="form-label text-dark">
                  Amount in USD
                </label>
                <div class="input-group">
                  <span class="input-group-text">$</span>
                  <input type="number" step="0.01" class="form-control" id="editUsdAmount" name="usdAmount" placeholder="Enter USD amount">
                </div>
              </div>
            </div>
          </div>

          {{-- /////// end amount field split by Abdul Rauf //////// --}}
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_currency" class="form-label">Currency <span class="text-danger">*</span></label>
                <select class="form-select text-dark" id="edit_currency" name="currency" required>
                  <option value="">Select Currency</option>
                  @foreach($currencies as $currency)
                    <option value="{{ $currency }}">{{ $currency }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            Update Expense
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Include jQuery and DataTables --}}
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

{{-- Add DataTables CSS to head if not already included --}}
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

<script>
$(document).ready(function() {
    // Initialize DataTable without AJAX (using server-side data)
    // var table = $('#expensesTable').DataTable({
    //     order: [[0, 'desc']],
    //     responsive: true,
    //     pageLength: 10,
    //     lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
    // });

    // Function to load yards based on country
    // function loadYards(countryId, yardSelectId, selectedYard = null) {
    //     if (!countryId) {
    //         $(yardSelectId).html('<option value="">First select country</option>').prop('disabled', true);
    //         return;
    //     }

    //     $.ajax({
    //         url: '{{ route('office-expenses.getYards') }}',
    //         method: 'GET',
    //         data: { country_id: countryId },
    //         success: function(response) {
    //             var options = '<option value="">Select Yard</option>';
    //             if (response.success && response.data.length > 0) {
    //                 response.data.forEach(function(yard) {
    //                     var selected = selectedYard === yard ? 'selected' : '';
    //                     options += `<option value="${yard}" ${selected}>${yard}</option>`;
    //                 });
    //             } else {
    //                 options = '<option value="">No yards available</option>';
    //             }
    //             $(yardSelectId).html(options).prop('disabled', false);
    //         },
    //         error: function() {
    //             $(yardSelectId).html('<option value="">Error loading yards</option>').prop('disabled', true);
    //         }
    //     });
    //   }

    // Function to load managers based on country
    function loadManagers(countryId, selector = '#assigned_manager_id', selectedManagerId = null) {
      let managerSelect = $(selector);

      if (!countryId) {
          managerSelect.html('<option value="">First select country</option>').prop('disabled', true);
          return;
      }

      $.ajax({
          url: '{{ url("/get-managers") }}/' + countryId,
          method: 'GET',
          dataType: 'json',
          success: function (data) {

              managerSelect.empty();

              if (data && data.length > 0) {
                  managerSelect.append('<option value="">Select Manager</option>');
                  data.forEach(function (manager) {
                    let selected = (manager.id == selectedManagerId) ? 'selected' : '';
                      managerSelect.append(`<option value="${manager.id}" ${selected}>${manager.name}</option>`);
                  });
                  managerSelect.prop('disabled', false);
              } else {
                  managerSelect.append('<option value="">No managers available</option>').prop('disabled', false);
              }
          },
          error: function () {
              managerSelect.html('<option value="">Error loading managers</option>').prop('disabled', true);
          }
      });
    }

    $('#country_id').on('change', function () {
        let countryId = $(this).val();
        loadManagers(countryId, '#assigned_manager_id');
    });

    // Country change event for Edit modal
    $('#edit_country_id').on('change', function() {
        var countryId = $(this).val();
        // loadYards(countryId, '#edit_yard');
        let selectedManagerId = $('#edit_manager').data('selected'); 
        loadManagers(countryId, '#edit_manager', selectedManagerId);
    });

    // Country change event for Add modal
    // $('#country_id').on('change', function() {
    //     var countryId = $(this).val();
    //     loadYards(countryId, '#yard');
    // });


    // Add Expense Form Submit
    $('#addExpenseForm').on('submit', function(e) {
        e.preventDefault();
        
        var submitBtn = $(this).find('button[type="submit"]');
        var spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        $.ajax({
            url: '{{ route('office-expenses.store') }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#addExpenseModal').modal('hide');
                    $('#addExpenseForm')[0].reset();
                    $('#managers').html('<option value="">First select country</option>').prop('disabled', true);
                    // $('#yard').html('<option value="">First select country</option>').prop('disabled', true);
                    // Reload the page to refresh the table data
                    location.reload();
                    showAlert('success', response.message);
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                $('.text-danger').text('');
                
                    if (xhr.status === 422) {
                      var errors = xhr.responseJSON.errors;
                      if (errors.amount) {
                          $('#amount-error').text(errors.amount[0]);
                      }
                      if (errors.currency) {
                          $('#currency-error').text(errors.currency[0]);
                      }
                      if (errors.account_id) {
                          $('#account-error').text(errors.account_id[0]);
                      }
                  } else if (xhr.status === 400 && xhr.responseJSON.error) {
                      // Show custom error under account field
                      $('#account-error').text(xhr.responseJSON.error);
                  } else {
                      showAlert('danger', xhr.responseJSON.message || 'An error occurred');
                  }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    /////////////////// Bank and cash accounts ///////////////////
    const getAccountsUrl = "{{ url('accounts/get-accounts') }}";

    $('#addExpenseModal').on('show.bs.modal', function () {
      $('#country_id').val('').trigger('change');
        $.get(getAccountsUrl, function (data) {
            console.log('Accounts data loaded:', data); 
            const accountSelect = $('#accountSelect');
            const currencyField = $('#currency');

            accountSelect.html('<option value="">Select Account</option>');
            currencyField.html('<option value="">Select Currency</option>').prop('disabled', true);

            data.forEach(acc => {
                accountSelect.append(`
                    <option value="${acc.id}" data-currency="${acc.currency_type}">
                        ${acc.title} (${acc.currency_type})
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
              const currencyRates = @json($currencyRates);
              const conversionRate = parseFloat(currencyRates[selectedCurrency]);
              const localVal = parseFloat(localAmount.val());
              
              if (!isNaN(localVal) && (conversionRate)) {
                usdAmount.val((localVal / conversionRate).toFixed(2));
              } 
            });

            usdAmount.off('input').on('input', function () {
              const selectedOption = accountSelect.find('option:selected');
              const selectedCurrency = selectedOption.data('currency');
              const currencyRates = @json($currencyRates);
              const conversionRate = parseFloat(currencyRates[selectedCurrency]);
              const localVal = parseFloat(usdAmount.val());
              
              if (!isNaN(localVal) && (conversionRate)) {
                localAmount.val((localVal * conversionRate).toFixed(2));
              } 
            });

            /////// end amount field split by Abdul Rauf ////////


            // Currency field update
            accountSelect.off('change').on('change', function () {
                const selectedOption = $(this).find('option:selected');
                const selectedCurrency = selectedOption.data('currency');

                if (selectedCurrency) {
                    currencyField.html(`<option value="${selectedCurrency}" selected>${selectedCurrency}</option>`);
                    currencyField.removeAttr('disabled'); // important to ensure it's sent with form
                    currencyField.css({
                        'pointer-events': 'none',
                        'background-color': '#e9ecef'
                    });

                  /////// start around view change by Abdul Rauf ////////
                  currency_type_in_amount.text(`(${selectedCurrency})`);
                  localAmount.prop('disabled', false);
                  usdAmount.prop('disabled', false);

                  const currencyRates = @json($currencyRates);
                  const conversionRate = parseFloat(currencyRates[selectedCurrency]);
                  const localVal = parseFloat(localAmount.val());
                  
                  if (!isNaN(localVal) && (conversionRate)) {
                    usdAmount.val((localVal / conversionRate).toFixed(2));
                  } 
                  /////// end  view change by Abdul Rauf ////////

                } else {
                    currencyField.html('<option value="">Select Currency</option>');
                    currencyField.prop('disabled', true);

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

    // Edit Button Click
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        
        $.ajax({
            url: '{{ route('office-expenses.show', ':id') }}'.replace(':id', id),
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    var expense = response.data;
                    // for currency select
                    const accounts = response.accounts;
                    const account_id = $('#account_id');
                    const currencyField = $('#edit_currency');
                    // Reset both fields
                    account_id.html('<option value="">Select Account</option>');
                    currencyField.html('<option value="">Select Currency</option>').prop('disabled', true);
                    // Loop through accounts to populate options
                    accounts.forEach(acc => {
                      const currencyType = acc.country?.currency_type || 'N/A';
                        account_id.append(`
                            <option value="${acc.id}" data-currency="${currencyType}">
                                ${acc.title} (${currencyType})
                            </option>
                        `);
                    });
                    
                    $('#edit_expense_id').val(expense.id);
                    $('#edit_country_id').val(expense.country_id);
                    $('#edit_expense_name').val(expense.expense_name);
                    $('#account_id').val(expense.account_id);
                    $('#edit_amount').val(expense.amount);
                    $('#edit_currency').val(expense.currency);
                    
                    account_id.val(expense.account_id).trigger('change');
                    const selectedOption = account_id.find('option:selected');
                    const selectedCurrency = selectedOption.data('currency');


                    /////// start amount field split by Abdul Rauf ////////
                    const localAmount = $('#edit_amount');
                    const usdAmount = $('#editUsdAmount');
                    const currency_type_in_amount = $('#edit_currency_type_in_amount');
                    const currencyRates = @json($currencyRates);
                    const conversionRate = parseFloat(currencyRates[selectedCurrency]);
                    const localVal1 = parseFloat(localAmount.val());
                    if (!isNaN(localVal1) && (conversionRate)) {
                      usdAmount.val((localVal1 / conversionRate).toFixed(2));
                    }

                    // localAmount.prop('disabled', true);
                    // usdAmount.prop('disabled', true);
                    currency_type_in_amount.text(`(${selectedCurrency})`);

                    localAmount.off('input').on('input', function () {
                      const localVal = parseFloat(localAmount.val());
                      
                      if (!isNaN(localVal) && (conversionRate)) {
                        usdAmount.val((localVal / conversionRate).toFixed(2));
                      } 
                    });

                    usdAmount.off('input').on('input', function () {
                      const localVal = parseFloat(usdAmount.val());
                      
                      if (!isNaN(localVal) && (conversionRate)) {
                        localAmount.val((localVal * conversionRate).toFixed(2));
                      } 
                    });

                    /////// end amount field split by Abdul Rauf ////////



                    if (selectedCurrency) {
                        currencyField.html(`<option value="${selectedCurrency}" selected>${selectedCurrency}</option>`);
                        currencyField.prop('disabled', false).css({
                            'pointer-events': 'none',
                            'background-color': '#e9ecef'
                        });



                    }

                    account_id.off('change').on('change', function () {
                      const selected = $(this).find('option:selected');
                      const currency = selected.data('currency');

                      if(currency) {
                          currencyField.html(`<option value="${currency}" selected>${currency}</option>`);
                          currencyField.prop('disabled', false).css({
                              'pointer-events': 'none',
                              'background-color': '#e9ecef'
                          });

                          /////// start around view change by Abdul Rauf ////////
                          currency_type_in_amount.text(`(${currency})`);
                          // localAmount.prop('disabled', false);
                          // usdAmount.prop('disabled', false);

                          const currencyRates = @json($currencyRates);
                          const conversionRate = parseFloat(currencyRates[currency]);
                          const localVal = parseFloat(localAmount.val());
                          
                          if (!isNaN(localVal) && (conversionRate)) {
                            usdAmount.val((localVal / conversionRate).toFixed(2));
                          } 
                          /////// end  view change by Abdul Rauf ////////
                      }else {
                          currencyField.html('<option value="">Select Currency</option>').prop('disabled', true);

                          /////// start amount field split by Abdul Rauf ////////
                          currencyField.prop('disabled', true);
                          localAmount.prop('disabled', true);
                          usdAmount.prop('disabled', true);
                          /////// end amount field split Abdul Rauf ////////
                      }
                    });
                    // Load yards and then set selected yard
                    // loadYards(expense.country_id, '#edit_yard', expense.yard);
                    loadManagers(expense.country_id, '#edit_manager', expense.assigned_manager_id);
                    
                    $('#editExpenseModal').modal('show');
                }
            },
            error: function(xhr) {
                // var errors = xhr.responseJSON.errors;
                // $('.text-danger').text('');
                
                //     if (xhr.status === 422) {
                //       var errors = xhr.responseJSON.errors;
                //       if (errors.amount) {
                //           $('#amount-error').text(errors.amount[0]);
                //       }
                //       if (errors.currency) {
                //           $('#currency-error').text(errors.currency[0]);
                //       }
                //       if (errors.account_id) {
                //           $('#account-error').text(errors.account_id[0]);
                //       }
                //   } else if (xhr.status === 400 && xhr.responseJSON.error) {
                //       // Show custom error under account field
                //       $('#account-error').text(xhr.responseJSON.error);
                //   } else {
                //       showAlert('danger', xhr.responseJSON.message || 'An error occurred');
                //   }
                showAlert('danger', 'Error loading expense data');
            }
        });
    });

    // Edit Expense Form Submit
    $('#editExpenseForm').on('submit', function(e) {
        e.preventDefault();
        
        var id = $('#edit_expense_id').val();
        var submitBtn = $(this).find('button[type="submit"]');
        var spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        $.ajax({
            url: '{{ route('office-expenses.update', ':id') }}'.replace(':id', id),
            method: 'PUT',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#editExpenseModal').modal('hide');
                    // Reload the page to refresh the table data
                    location.reload();
                    showAlert('success', response.message);
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                if (errors) {
                    var errorMessage = Object.values(errors).flat().join('\n');
                    showAlert('danger', errorMessage);
                } else {
                    showAlert('danger', xhr.responseJSON.message || 'An error occurred');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Delete Button Click
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        
        if (confirm('Are you sure you want to delete this expense?')) {
            $.ajax({
                url: '{{ route('office-expenses.destroy', ':id') }}'.replace(':id', id),
                method: 'DELETE',
                data: {
                    '_token': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        // Reload the page to refresh the table data
                        location.reload();
                        showAlert('success', response.message);
                    }
                },
                error: function(xhr) {
                    showAlert('danger', xhr.responseJSON.message || 'Error deleting expense');
                }
            });
        }
    });

    // Show Alert Function
    function showAlert(type, message) {
        var alertDiv = type === 'success' ? '#dynamic-success' : '#dynamic-danger';
        var messageSpan = type === 'success' ? '#dynamic-success-message' : '#dynamic-danger-message';
        
        $(messageSpan).text(message);
        $(alertDiv).removeClass('d-none');
        
        setTimeout(function() {
            $(alertDiv).addClass('d-none');
        }, 5000);
    }
});

  /////////////////// filter code for office expense//////////////////////////
  $(document).ready(function () {
    function applyFilters() {
      const countryId = $('#country-filter').val();
      // const yard = $('#yard-filter').val();
      const manager = $('#manager-filter').val();

      // Set hidden inputs for export
      $('#export-country-id').val(countryId);
      // $('#export-yard').val(yard);
      $('#export-manager').val(manager);
      
      $.ajax({
        url: "{{ route('office-expenses.filter') }}",
        method: 'POST',
        data: {
          country_id: countryId,
          assigned_manager_id: manager,
          _token: '{{ csrf_token() }}'
        },
        beforeSend: function () {
          $('#expense-table-body').html(`
            <tr>
              <td colspan="9" class="text-center py-3">
                <div class="spinner-border" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </td>
            </tr>
          `);
        },
        success: function (response) {
          if (response.html.trim() === '') {
              $('#expense-table-body').html(`
                  <tr>
                      <td colspan="100%">
                          <div class="alert alert-warning text-center shadow-sm rounded mt-4">
                              <i class="mdi mdi-alert-circle-outline"></i>
                              No office expenses found.
                          </div>
                      </td>
                  </tr>
              `);
          } else {
            $('#expense-table-body').html(response.html);
          }

          const grandTotal = response.grand_total ?? '0.00';
          $('#car-expense-grand-total').html(`
            <strong class="text-success">$${grandTotal}</strong>
            <small class="text-muted d-block">Total USD Amount</small>
          `);
        // Count filtered rows excluding the 'no data' alert
        // filteredCount = $('#expense-table-body').find('tr').not(':has(.alert-warning)').length;
        }
      });
    }
    
    const userIsManager = @json(auth()->user()->hasRole('manager'));
    const managerCountryId = @json(auth()->user()->country_id);

    if (userIsManager) {
      // $(window).on('load', function () {
        $('#country-filter').val(managerCountryId); 
        $('#manager-filter').val(@json(auth()->id()));
          applyFilters();
      // });
    }

      // Update managers on country change
    $('#country-filter').change(function () {
      let countryId = $(this).val();
      $('#manager-filter').prop('disabled', true).html('<option value="">Loading...</option>');

      if (countryId) {
        $.get("{{ url('get-managers') }}/" + countryId, function (response) {
            let options = '<option value="">Managers</option>';
            $.each(response, function (_, manager) {
                options += `<option value="${manager.id}">${manager.name}</option>`;
            });
            $('#manager-filter').html(options).prop('disabled', false);
            applyFilters();
        });
      } else {
        $('#manager-filter').html('<option value="">Managers</option>').prop('disabled', true);
        applyFilters();
      }
    });

    $('#manager-filter').change(applyFilters);

    // Reset Filters Button
      $('#reset-filters').click(function (e) {
        e.preventDefault();
        $('#country-filter, #manager-filter').val('');
        $('#manager-filter').html('<option value="">Managers</option>').prop('disabled', true);
        applyFilters();
      });
    });

    // Sync filter values to hidden export form [ for csv file]
    $('#country-filter, #manager-filter').on('change', function () {
      $('#export-country-id').val($('#country-filter').val());
      $('#export-manager').val($('#manager-filter').val());
    });

    // Also trigger this on page load to sync defaults [ for csv file]
      $('#export-country-id').val($('#country-filter').val());
      $('#export-manager').val($('#manager-filter').val());

    // Download Button Protection
    $('#office-expense-export-btn').on('click', function (e) {
      if (filteredCount === 0) {
        e.preventDefault();

        Swal.fire({
          icon: 'warning',
          title: 'No data to export',
          text: 'Please add data before exporting.',
          confirmButtonColor: '#3085d6',
        });
      }
  });
  //////////end csv file

//////////// office expense search
  document.addEventListener('DOMContentLoaded', function () {
      if (!window.location.search.includes('search=')) {
          document.querySelector('input[name="search"]').value = '';
      }

      const searchInput = document.getElementById('office-expense-search');
      const tableBody = document.getElementById('expense-table-body');
      const tableFooter = document.getElementById('office-expense-tfoot');
      const paginationWrapper = document.querySelector('.mt-4.d-flex');
      let timer = null;

      searchInput.addEventListener('keyup', function () {
          clearTimeout(timer);
          timer = setTimeout(function () {
              $.ajax({
                  url: "{{ route('office-expense.search') }}",
                  type: "GET",
                  data: { search: searchInput.value },
                  success: function (data) {
                      tableBody.innerHTML = data.tbody;
                      tableFooter.innerHTML = data.tfoot;
                      if (paginationWrapper) {
                          paginationWrapper.innerHTML = data.pagination;
                      }
                  },
                  error: function (xhr) {
                      console.error("Error fetching search data:", xhr.responseText);
                  }
              });
          }, 400);
      });
  });

</script>

@include('dashboards.partials.footer')