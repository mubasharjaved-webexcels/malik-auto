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
 $currencies = \App\Models\Country::select('currency_type')
                ->whereNotNull('currency_type')
                ->distinct()
                ->pluck('currency_type');
@endphp
  <div class="main-panel">
    <div class="content-wrapper">
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
                            {{-- <h4 class="card-title mb-2">Car Profiles</h4> --}}
                            <h4 class="card-title pb-2 mb-0">Car Profiles</h4>
                            @unlessrole('manager|salesperson')
                            <a href="{{ route('car-profiles.create') }}" class="btn btn-primary text-white" style="margin-right: 0;border: none;padding: 9px 15px;margin-bottom: 7px;"><i class="mdi mdi-plus" style="font-size: 12px;"></i>Add New Car</a>
                            @endunlessrole
                          </div>
                      <div class="card-body">
                        {{-- filter dropdowns --}}
                        <h6 class="text-muted mb-3">Filter by Country, Yard & Record No</h6>
                        <div class="row mb-4">
                           <div class="col-md-2">
                            <div class="position-relative">
                                <!--<i class="bi bi-chevron-down position-absolute text-muted" style="right: 15px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>-->
                                <form method="GET">
                                    <select name="per_page" id="per_page" class="form-select text-dark  " onchange="this.form.submit()">
                                        @foreach([5, 10, 25, 50, 100] as $size)
                                            <option value="{{ $size }}" {{ request('per_page') == $size ? 'selected' : '' }}>Show Entries - {{$size}}</option>
                                        @endforeach
                                    </select>
                                    @foreach(request()->except('per_page') as $key => $value)
                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endforeach
                                </form>
                            </div>
                          </div>
        
                          <div class="col-md-2">
                          <div class="position-relative">
                              <!--<i class="bi bi-chevron-down position-absolute text-muted" style="right: 15px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>-->
                                <select id="country-filter" class="form-select text-dark">
                                    <option value="">All Countries</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                          </div>
                          <div class="col-md-2">
                            <div class="position-relative">
                                <!--<i class="bi bi-chevron-down position-absolute text-muted" style="right: 15px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>-->
                                <select id="manager-filter" class="form-select text-dark">
                                    <option value="">All Managers</option>
                                </select>
                            </div>
                          </div>
                          {{-- <div class="col-md-2">
                            <div class="position-relative">
                                <!--<i class="bi bi-chevron-down position-absolute text-muted" style="right: 15px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>-->
                                <select id="yard-filter" class="form-select text-dark">
                                    <option value="">All Yards</option>
                                </select>
                            </div>
                          </div> --}}
                          <div class="col-md-2">
                            <div class="position-relative">
                                <!--<i class="bi bi-chevron-down position-absolute text-muted" style="right: 15px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>-->
                                <select id="recno-filter" class="form-select text-dark">
                                    <option value="">All Rec Nos</option>
                                </select>
                            </div>
                          </div>
                          <div class="col-md-2">
                            <div class="position-relative">
                                <!--<i class="bi bi-chevron-down position-absolute text-muted" style="right: 15px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>-->
                                <select id="status-filter" class="form-select text-dark">
                                    <option value="">All Car Status</option>
                                    @foreach ($carStatuses as $status)
                                      <option value="{{ $status }}">{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                                  @endforeach
                                </select>
                            </div>
                          </div>
                          <div class="col-md-2">
                            <button id="reset-filters" class="btn btn-secondary w-100 text-white" style="height: 46px;">
                              <i class="mdi mdi-refresh"></i> Reset Filters
                            </button>
                          </div>
                        </div>
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
                  <label for="transitExpense" class="form-label">Transit Expense</label>
                  <input type="number" min="0" oninput="this.value = this.value.replace(/[^0-9.]/g, '')" step="0.01" class="form-control" id="transitExpense" name="transit_expense" required>
                <div id="transitExpenseError" class="text-danger mt-1" style="font-size: 13px;"></div>
              </div>

              <div class="mb-3">
                  <label for="transitExpense" class="form-label">Pay From Account</label>
                <select id="accountSelect" class="form-select text-dark" required>
                  <option value="">Loading...</option>
                </select>
              </div>

              <div class="mb-3">
                <label for="currencyType" class="form-label">Currency Type</label>
                <select name="currency_type" id="currencyType" class="form-select text-dark" disabled required>
                    <option value="">Select Currency</option>
                    @foreach ($currencies as $currency)
                        <option value="{{ $currency }}">{{ $currency }}</option>
                    @endforeach
                </select>
              </div>
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
      const transferLinks = document.querySelectorAll('.transfer-country');
      const modal = new bootstrap.Modal(document.getElementById('transferModal'));

      transferLinks.forEach(link => {
          link.addEventListener('click', function () {
              const carId = this.dataset.carId;
              const countryId = this.dataset.countryId;
              const countryName = this.dataset.countryName;

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
                      <option value="${acc.id}">
                        ${acc.title} (${acc.currency_type}${acc.yard ? ' - ' + acc.yard : ''})
                      </option>
                    `;
                  });
                });
                // Show Currency Automatically After Account Selection
                const currencyField = document.getElementById('currencyType');
                currencyField.innerHTML = '<option value="">Select Currency</option>';
                currencyField.disabled = true;

                accountSelect.addEventListener('change', function () {
                    const selectedOption = this.options[this.selectedIndex];
                    const currency = selectedOption.textContent.match(/\(([^)]+)\)/);

                    if (currency) {
                        const extractedCurrency = currency[1].split(' - ')[0];

                        // Replace all options with only selected currency
                        currencyField.innerHTML = `<option value="${extractedCurrency}" selected>${extractedCurrency}</option>`;
                        currencyField.disabled = false;
                    } else {
                        currencyField.innerHTML = '<option value="">Select Currency</option>';
                        currencyField.disabled = true;
                    }
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
          });
      });

      // Handle transfer form submit
      document.getElementById('transferForm').addEventListener('submit', async function (e) {
      e.preventDefault();

      const carId = document.getElementById('modalCarId').value;
      const countryId = document.getElementById('modalCountryId').value;
      const managerId = document.getElementById('managerSelect').value;
      const transitExpenseId = document.getElementById('transitExpense').value;
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

      // document.getElementById('transferForm').addEventListener('submit', function (e) {
      //     e.preventDefault();

      //     const carId = document.getElementById('modalCarId').value;
      //     const countryId = document.getElementById('modalCountryId').value;
      //     const managerId = document.getElementById('managerSelect').value;
      //     const transitExpenseId = document.getElementById('transitExpense').value;
      //     const currencyTypeId = document.getElementById('currencyType').value;
      //     const selectedAccountId = document.getElementById('accountSelect').value;

      //     const transferUrl = "{{ url('/car-profiles') }}";
      //     fetch(`${transferUrl}/${carId}/transfer`, {
      //         method: 'POST',
      //         headers: {
      //             'Content-Type': 'application/json',
      //             'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
      //         },
      //         body: JSON.stringify({
      //             country_id: countryId,
      //             assigned_manager_id: managerId,
      //             transit_expense: transitExpenseId,
      //             currency_type: currencyTypeId,
      //             account_id: selectedAccountId 
      //         })
      //     }).then(res => {
      //         if (res.ok) {
      //           modal.hide();

      //           const successAlert = document.createElement('div');
      //           successAlert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-4 shadow';
      //           successAlert.style.zIndex = '9999';
      //           successAlert.role = 'alert';
      //           successAlert.innerHTML = `
      //               Car transferred successfully!
      //               <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      //           `;

      //           document.body.appendChild(successAlert);

      //           setTimeout(() => {
      //               successAlert.classList.remove('show');
      //               successAlert.classList.add('hide');
      //               setTimeout(() => successAlert.remove(), 500);
      //               window.location.reload(); // reload after toast fades
      //           }, 2000);
      //         }else {
      //           const errorData = await res.json();
      //           Swal.fire({
      //               icon: 'warning',
      //               title: 'Transfer Failed',
      //               text: errorData.error || 'Insufficient balance in selected account.',
      //               confirmButtonColor: '#dc3545'
      //           });
      //         }
      //      });
      // });
  });

  // Tooltip Initialize
  document.addEventListener('DOMContentLoaded', function () {
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      tooltipTriggerList.forEach(function (tooltipTriggerEl) {
          new bootstrap.Tooltip(tooltipTriggerEl)
      });
  });
  
// for country, yard and record-no filters
  $(document).ready(function () {
    $('#manager-filter').prop('disabled', true);
    // $('#yard-filter').prop('disabled', true);
    $('#recno-filter').prop('disabled', true);

      function fetchCarProfiles() {
        const countryId = $('#country-filter').val();
        const manager = $('#manager-filter').val();
        // const yard = $('#yard-filter').val();
        const recNo = $('#recno-filter').val();
        const carStatus = $('#status-filter').val();
        const perPage =$('#per_page').val();

        $.ajax({
          url: '{{ route("filter") }}',
          method: 'GET',
          data: {
              country_id: countryId,
              assigned_manager_id: manager,
              // located_yard: yard,
              rec_no: recNo,
              car_status: carStatus,
              per_page : perPage
          },
          success: function (response) {
          $('#car-list').html(response.html);

          const selectedManager = $('#manager-filter').val();
          // const selectedYard = $('#yard-filter').val();
          const selectedRecNo = $('#recno-filter').val();
          
          // Update Manager dropdown
          const $manager = $('#manager-filter');
          $manager.empty().append('<option value="">Select Manager</option>');
          response.managers.forEach(function (manager) {
              const selected = (manager.id == selectedManager) ? 'selected' : '';
              $manager.append(`<option value="${manager.id}" ${selected}>${manager.name}</option>`);
          });

          // Update Yard dropdown
          // const $yard = $('#yard-filter');
          // $yard.empty().append('<option value="">Select Yard</option>');
          // response.yards.forEach(function (yard) {
          //     const selected = (yard === selectedYard) ? 'selected' : '';
          //     $yard.append(`<option value="${yard}" ${selected}>${yard}</option>`);
          // });

          // Update Rec No dropdown
          const $recNo = $('#recno-filter');
          $recNo.empty().append('<option value="">Select Rec No</option>');
          response.recNos.forEach(function (rec) {
              const selected = (rec === selectedRecNo) ? 'selected' : '';
              $recNo.append(`<option value="${rec}" ${selected}>${rec}</option>`);
          });
          
          if (countryId) {
              $('#manager-filter').prop('disabled', false);
              // $('#yard-filter').prop('disabled', false);
          } else {
              $('#manager-filter').prop('disabled', true);
              // $('#yard-filter').prop('disabled', true);
              $('#recno-filter').prop('disabled', true);
          }
          
          // if (yard) {
          if (manager) {
              $('#recno-filter').prop('disabled', false);
          } else {
              $('#recno-filter').prop('disabled', true);
          }
        },
            error: function () {
                console.log('Something went wrong.');
            }
        });
      }

      $('#country-filter').on('change', function () {
          $('#manager-filter').empty().append('<option value=""> </option>');
          // $('#yard-filter').empty().append('<option value=""> </option>');
          $('#recno-filter').empty().append('<option value="">Select Rec No</option>');
          fetchCarProfiles();
      });

      $('#manager-filter').on('change', function () {
          $('#recno-filter').empty().append('<option value="">Select Rec No</option>');
          fetchCarProfiles();
      });

      // $('#yard-filter').on('change', function () {
      //     $('#recno-filter').empty().append('<option value="">Select Rec No</option>');
      //     fetchCarProfiles();
      // });

      $('#recno-filter').on('change', function () {
          fetchCarProfiles();
      });

      $('#status-filter').on('change', function () {
          fetchCarProfiles();
      });

      $('#reset-filters').on('click', function () {
          $('#per_page').empty().append('<option value="5" selected>Show Entries - 5</option>');
          $('#country-filter').val('');
          $('#manager-filter').empty().append('<option value="">Select Manager</option>').prop('disabled', true);
          // $('#yard-filter').empty().append('<option value="">Select Yard</option>');
          $('#recno-filter').empty().append('<option value="">Select Rec No</option>');
          $('#status-filter').val('');
          fetchCarProfiles();
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
