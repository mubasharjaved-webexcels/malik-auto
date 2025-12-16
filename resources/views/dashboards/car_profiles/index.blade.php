@include('dashboards.partials.header')

<div class="container-fluid page-body-wrapper">
  @include('dashboards.partials.sidebar')

  <style>
    .dropdown-toggle::after {
      display: none !important;
    }

    .btn-primary:hover, .btn-primary:focus{
        background: #1F3BB3;
    }
  </style>
@php
 $currencyRates = \App\Models\Country::whereNotNull('currency_type')
                ->select('currency_type', 'currency_rate')
                ->distinct()->pluck('currency_rate', 'currency_type');
@endphp
  <div class="main-panel">
    <div class="content-wrapper px-3">
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

    {{-- ****************** success and error messages **************** --}}
      <div class="row">
        <div class="col-sm-12">
          <div class="home-tab">
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
              <div class="row">
                <div class="col-lg-12 flex-column">
                  <div class="col-lg-12 grid-margin stretch-card">
                    <div class="card">
                        {{-- Add New Car Profiles --}}
                          <div class="text-bg-light pt-2 px-3 pb-0 card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title pb-2 mb-0">Car Profiles</h4>
                            <div class="d-flex align-items-center">
                              <form action="{{ route('car-profiles.search') }}" method="GET" class="d-flex align-items-left" style="margin-bottom: 7px;">
                                <input type="text" 
                                  id="car-search"
                                  name="search" 
                                  value="{{ request('search') }}" 
                                  class="form-control form-control-sm" 
                                  style="width:300px; height: 31px; margin-right:17px;" 
                                  placeholder="Search by Rec no, Chassis, or Car status...">
                              </form>
                              @unlessrole('manager|salesperson')
                              <a href="{{ route('car-profiles.create') }}" class="btn btn-primary text-white" style="margin-right: 0;border: none;padding: 9px 15px;margin-bottom: 7px;"><i class="mdi mdi-plus" style="font-size: 12px;"></i>Add New Car</a>
                              @endunlessrole
                            </div>
                          </div>
                      <div class="card-body">
                        {{-- filter dropdowns --}}
                        <h6 class="text-muted mb-3">Filter by Country, Manager, Record No & Car status</h6>
                        <form method="GET">
                          <div class="row mb-4">
                            <div class="col-md-2">
                              <select name="per_page" id="per_page" class="form-select text-dark" onchange="this.form.submit()">
                                @foreach([5, 10, 25, 50, 100] as $size)
                                  <option value="{{ $size }}" {{ $per_page_value == $size ? 'selected' : '' }}>Range - {{ $size }}</option>
                                @endforeach
                              </select>
                            </div>
                        
                            <div class="col-md-2 px-1">
                              <select name="country_id" id="country_id" class="form-select text-dark" onchange="this.form.submit()">
                                <option value="">All Countries</option>
                                @foreach ($countries as $country)
                                  <option value="{{ $country->id }}" {{ request('country_id') == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                                @endforeach
                              </select>
                            </div>
                        
                            <div class="col-md-2 px-1">
                              <select name="assigned_manager_id" id="assigned_manager_id" class="form-select text-dark" {{ request('country_id') ? '' : 'disabled' }} onchange="this.form.submit()">
                                <option value="">All Managers</option>
                                @foreach ($managers as $manager)
                                  <option value="{{ $manager->id }}" {{ request('assigned_manager_id') == $manager->id ? 'selected' : '' }}>{{ $manager->name }}</option>
                                @endforeach
                              </select>
                            </div>
                        
                            <div class="col-md-2 px-1">
                              <select name="rec_no" id="rec_no" class="form-select text-dark" {{ request('assigned_manager_id') ? '' : 'disabled' }} onchange="this.form.submit()">
                                <option value="">All Rec Nos</option>
                                @foreach ($recNos as $recNo)
                                  <option value="{{ $recNo }}" {{ request('rec_no') == $recNo ? 'selected' : '' }}>{{ $recNo }}</option>
                                @endforeach
                              </select>
                            </div>
                        
                            <div class="col-md-2 px-1">
                              <select name="car_status" id="car_status" class="form-select text-dark" onchange="this.form.submit()">
                                <option value="">All Car Status</option>
                                @foreach ($carStatuses as $status)
                                  <option value="{{ $status }}" {{ request('car_status') == $status ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $status)) }}
                                  </option>
                                @endforeach
                              </select>
                            </div>
                        
                            <div class="col-md-2">
                              <button id="reset-filters" class="btn btn-secondary w-100 text-white" style="height:46px;" type="button" onclick="window.location='{{ route('car-profiles.index') }}'">
                                <i class="mdi mdi-refresh"></i> Reset Filters
                              </button>
                            </div>
                          </div>
                        </form>

                        {{-- car profiles listing --}}
                        <div id="car-list">
                          @include('dashboards.car_profiles.partials.list')
                        </div>
                      </div> <!-- card-body -->
                    </div> <!-- card -->
                  </div> <!-- stretch-card -->
                </div> <!-- flex-column -->
              </div> <!-- row -->
            </div> <!-- tab-pane -->
          </div> <!-- home-tab -->
        </div> <!-- col-sm-12 -->
      </div> <!-- row -->
    </div> <!-- content-wrapper -->
    <!-- Transfer Modal -->
    <div class="modal fade" id="transferModal" tabindex="-1" aria-labelledby="transferModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form id="transferForm">
          @csrf
          @method('PUT')
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="transferModalLabel">Transfer Car</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" id="modalCarId">
              <input type="hidden" id="modalCountryId">

              <div class="mb-3">
                <label for="managerSelect" class="form-label">Select Manager</label>
                <select id="managerSelect" class="form-select text-dark" required>
                  <option value="">Loading...</option>
                </select>
              </div>

              <div class="mb-3">
                  <label for="transitExpense" class="form-label">Pay From Account</label>
                <select id="accountSelect" class="form-select text-dark" required>
                  <option value="">Loading...</option>
                </select>
              </div>

              {{-- <div class="mb-3">
                  <label for="transitExpense" class="form-label">Transit Expense</label>
                  <input type="number" min="0" oninput="this.value = this.value.replace(/[^0-9.]/g, '')" step="0.01" class="form-control" id="transitExpense" name="transit_expense" required>
                <div id="transitExpenseError" class="text-danger mt-1" style="font-size: 13px;"></div>
              </div> --}}

              <div id="transit-expense-wrapper" class="mb-3">
                <label for="transit-expense" class="form-label">Transit Amount</label>
                <div class="input-group">
                    <span class="input-group-text" id="local-currency-label">JPY</span>
                    <input
                        type="number"
                        min="0"
                        step="0.01"
                        oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                        id="transit_expense_local"
                        class="form-control">

                    <span class="input-group-text">$</span>
                    <input
                        type="number"
                        min="0"
                        step="0.01"
                        oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                        name="transit_expense"
                        id="transit_expense_usd" class="form-control">
                </div>
                <div id="transitExpenseError" class="text-danger mt-1" style="font-size: 13px;"></div>
              </div>

              <input type="hidden" id="currencyType" name="currency_type" />
              {{-- <div class="mb-3">
                <label for="currencyType" class="form-label">Currency Type</label>
                <select name="currency_type" id="currencyType" class="form-select text-dark" disabled required>
                    <option value="">Select Currency</option>
                    @foreach ($currencies as $currency)
                        <option value="{{ $currency }}">{{ $currency }}</option>
                    @endforeach
                </select>
              </div> --}}
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">Transfer</button>
            </div>
          </div>
        </form>
      </div>
    </div>
    <!-- Transfer Modal -->
    @include('dashboards.partials.footer')
  </div> <!-- main-panel -->
</div> <!-- page-body-wrapper -->

<script>
  
// Transfer car script
  document.addEventListener('DOMContentLoaded', function () {
    // const transferLinks = document.querySelectorAll('.transfer-country');
    const modal = new bootstrap.Modal(document.getElementById('transferModal'));
    // transferLinks.forEach(link => {
    //     link.addEventListener('click', function () {
    //         const carId = this.dataset.carId;
    //         const countryId = this.dataset.countryId;
    //         const countryName = this.dataset.countryName;

      document.addEventListener('click', function (e) {
        const link = e.target.closest('.transfer-country');
        if (!link) return;

        e.preventDefault();

        const carId = link.dataset.carId;
        const countryId = link.dataset.countryId;
        const countryName = link.dataset.countryName;

        // Set modal title
        document.getElementById('transferModalLabel').innerText = `Transfer to ${countryName}`;
        document.getElementById('modalCarId').value = carId;
        document.getElementById('modalCountryId').value = countryId;
              
              // Fetch Accounts
              const getAccountsUrl = "{{ url('accounts/get-accounts') }}";

              fetch(`${getAccountsUrl}/${countryId}`)
                .then(res => res.json())
                .then(data => {
                  const accountSelect = document.getElementById('accountSelect');
                  accountSelect.innerHTML = '<option value="">Select Account</option>';

                  data.forEach(acc => {
                    accountSelect.innerHTML += `
                      <option value="${acc.id}" data-currency="${acc.currency_type}">
                        ${acc.title} (${acc.currency_type}${acc.yard ? ' - ' + acc.yard : ''})
                      </option>
                    `;
                  });
                });
                // Show Currency Automatically After Account Selection
                // const currencyField = document.getElementById('currencyType');
                // currencyField.innerHTML = '<option value="">Select Currency</option>';
                // currencyField.disabled = true;

                accountSelect.addEventListener('change', function () {
                    const selectedOption = this.options[this.selectedIndex];
                    const extractedCurrency = selectedOption.getAttribute('data-currency');
                    // const currency = selectedOption.textContent.match(/\(([^)]+)\)/);
                    document.getElementById('currencyType').value = extractedCurrency

                    if (extractedCurrency) {
                        // const extractedCurrency = currency[1].split(' - ')[0];

                        // Replace all options with only selected currency
                        // currencyField.innerHTML = `<option value="${extractedCurrency}" selected>${extractedCurrency}</option>`;
                        // currencyField.disabled = false;

                        const localCurrencySpan = document.querySelector('#transit-expense-wrapper .input-group-text:first-child');
                        if (localCurrencySpan) {
                            localCurrencySpan.textContent = extractedCurrency;
                            document.getElementById('transit_expense_local').disabled = false;
                            document.getElementById('transit_expense_usd').disabled = false;
                            document.getElementById('transit_expense_local').value = '';
                            document.getElementById('transit_expense_usd').value = '';
                        }
                    } else {
                        // currencyField.innerHTML = '<option value="">Select Currency</option>';
                        // currencyField.disabled = true;
                        document.getElementById('currencyType').value = '';
                        document.getElementById('transit_expense_local').disabled = true;
                        document.getElementById('transit_expense_usd').disabled = true;
                    }
                });

                const transferModalEl = document.getElementById('transferModal');
                transferModalEl.addEventListener('hidden.bs.modal', function () {
                    $('#transit_expense_local').val('').prop('disabled', true);
                    $('#transit_expense_usd').val('').prop('disabled', true);
                    $('#currencyType').val('');
                    const localCurrencySpan = document.querySelector('#transit-expense-wrapper .input-group-text:first-child');
                    if (localCurrencySpan) {
                        localCurrencySpan.textContent = 'JPY';
                    }
                    
                    // For no manager error message and active manager dropdown
                    $('#noManagerMessage').addClass('d-none');
                    document.getElementById('managerSelect').style.display = 'block';
                    document.querySelector('label[for="managerSelect"]').style.display = 'block';
                });

              // Fetch managers
              const getManagersUrl = "{{ url('/get-managers') }}";
              fetch(`${getManagersUrl}/${countryId}`)
                .then(res => res.json())
                .then(data => {
                    const managerSelect = document.getElementById('managerSelect');
                    const selectLabel = document.querySelector('label[for="managerSelect"]');
                    const transferBtn = document.querySelector('#transferForm button[type="submit"]');
                    const selectWrapper = managerSelect.parentElement;

                    // Remove any old message
                    const oldMessage = document.getElementById('noManagerMessage');
                    if (oldMessage) oldMessage.remove();

                    if (data.length > 0) {
                        // Show elements
                        selectLabel.style.display = 'block';
                        managerSelect.style.display = 'block';
                        transferBtn.style.display = 'inline-block';

                        // Populate dropdown
                        managerSelect.innerHTML = '<option value="">Select Manager</option>';
                        data.forEach(manager => {
                            managerSelect.innerHTML += `<option value="${manager.id}">${manager.name}</option>`;
                        });

                    } else {
                        // Hide dropdown, label and button
                        selectLabel.style.display = 'none';
                        managerSelect.style.display = 'none';
                        transferBtn.style.display = 'none';

                        // Show alert message
                        const message = document.createElement('div');
                        message.id = 'noManagerMessage';
                        message.className = 'alert alert-warning mt-2';
                        message.innerHTML = `
                            No manager available for <strong>${countryName}</strong>. 
                            Please create one first.
                        `;
                        selectWrapper.appendChild(message);
                    }
                });
              modal.show();
          // });
      });

      // Handle transfer form submit
      document.getElementById('transferForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const carId = document.getElementById('modalCarId').value;
        const countryId = document.getElementById('modalCountryId').value;
        const managerId = document.getElementById('managerSelect').value;
        const transitExpenseId = document.getElementById('transit_expense_local').value;
        const currencyTypeId = document.getElementById('currencyType').value;
        const selectedAccountId = document.getElementById('accountSelect').value;

        const transferUrl = "{{ url('/car-profiles') }}";

        // Clear any old error message
        const errorDiv = document.getElementById('transitExpenseError');
        errorDiv.textContent = '';

        try {
            const res = await fetch(`${transferUrl}/${carId}/transfer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                },
                body: JSON.stringify({
                    country_id: countryId,
                    assigned_manager_id: managerId,
                    transit_expense: transitExpenseId,
                    currency_type: currencyTypeId,
                    account_id: selectedAccountId
                })
            });

            if (res.ok) {
                modal.hide();

                const successAlert = document.createElement('div');
                successAlert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-4 shadow';
                successAlert.style.zIndex = '9999';
                successAlert.role = 'alert';
                successAlert.innerHTML = `
                    Car transferred successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;

                document.body.appendChild(successAlert);

                setTimeout(() => {
                    successAlert.classList.remove('show');
                    successAlert.classList.add('hide');
                    setTimeout(() => successAlert.remove(), 500);
                    window.location.reload();
                }, 2000);
            } else {
                const errorData = await res.json();
                if (errorData.error) {
                    errorDiv.textContent = errorData.error;
                } else {
                    errorDiv.textContent = 'Something went wrong. Please try again.';
                }
            }
        } catch (error) {
            errorDiv.textContent = 'Unexpected error occurred. Try again.';
        }
      });
  });

  // Tooltip Initialize
  document.addEventListener('DOMContentLoaded', function () {
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      tooltipTriggerList.forEach(function (tooltipTriggerEl) {
          new bootstrap.Tooltip(tooltipTriggerEl)
      });
  });
  
  document.addEventListener('DOMContentLoaded', function () {
    if (!window.location.search.includes('search=')) {
        document.querySelector('input[name="search"]').value = '';
    }

    const searchInput = document.getElementById('car-search');
    const resultsContainer = document.getElementById('car-list');
    let timer = null;

    searchInput.addEventListener('keyup', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            $.ajax({
                url: "{{ route('car-profiles.search') }}",
                type: "GET",
                data: { search: searchInput.value, per_page: {{ $per_page_value }} },
                success: function (data) {
                    resultsContainer.innerHTML = data;
                }
            });
        }, 400);
    });
  });

  ///////////////// transit amount split field conversion /////////////
    document.addEventListener('DOMContentLoaded', function () {
      const localAmount = $('#transit_expense_local');
      const usdAmount = $('#transit_expense_usd');
      const currencyField = $('#currencyType');
      const localLabel = $('#local-currency-label');
      const accountSelect = $('#accountSelect');

      const currencyRates = @json($currencyRates);

      localAmount.prop('disabled', true);
      usdAmount.prop('disabled', true);

      accountSelect.on('change', function () {
          const selectedText = $(this).find('option:selected').text();
          const match = selectedText.match(/\(([^)]+)\)/);
          if (!match) return;

          const extractedCurrency = match[1].split(' - ')[0];

          localLabel.text(extractedCurrency);
          currencyField.html(`<option value="${extractedCurrency}" selected>${extractedCurrency}</option>`);

          localAmount.prop('disabled', false);
          usdAmount.prop('disabled', false);

          localAmount.val('');
          usdAmount.val('');
      });

      // Local -> USD
      localAmount.on('input', function () {
          const selectedCurrency = localLabel.text();
          const conversionRate = parseFloat(currencyRates[selectedCurrency]);
          const localVal = parseFloat(localAmount.val());

          if (!isNaN(localVal) && conversionRate) {
              usdAmount.val((localVal / conversionRate).toFixed(2));
          } else {
              usdAmount.val('');
          }
      });

      // USD -> Local
      usdAmount.on('input', function () {
          const selectedCurrency = localLabel.text();
          const conversionRate = parseFloat(currencyRates[selectedCurrency]);
          const usdVal = parseFloat(usdAmount.val());

          if (!isNaN(usdVal) && conversionRate) {
              localAmount.val((usdVal * conversionRate).toFixed(2));
          } else {
              localAmount.val('');
          }
      });
  });

  // Data TAble
  // $(document).ready(function() {
  //   $('#carProfilesTable').DataTable({
  //     "paging": true,
  //     "searching": true,
  //     "ordering": true,
  //     "lengthChange": false, // set true if want to change page size
  //     "pageLength": 10, // number of rows per page
  //     "language": {
  //       "search": "Search car profiles:", // Custom search label
  //       "emptyTable": "No data available"
  //     }
  //   });
  // });

</script>
