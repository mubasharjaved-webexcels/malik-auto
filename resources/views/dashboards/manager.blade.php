
@include('dashboards.partials.header')

<div class="container-fluid page-body-wrapper">
  @include('dashboards.partials.sidebar')

  <div class="main-panel">
    <div class="content-wrapper px-3">
      <div class="row">
        <div class="col-sm-12">
          <div class="home-tab">
            <div class="tab-content-basic">
              <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="row">
                  <div class="col-lg-8 flex-column px-0">
                    <div class="container pe-0">
                      <div class="row g-4">
                        {{-- Country total count card --}}
                        <div class="col-md-3 col-sm-6">
                          <div class="card h-100 shadow-sm border-0 rounded-3 d-flex flex-column justify-content-between text-center">
                            <a class="text-decoration-none" href="{{ $totalAssignedCars > 0 ? route('car-profiles.index', ['country_id' => $country->id]) : 'javascript:void(0)' }}">
                              <div class="card-body px-2">
                                <h6 class="fw-semibold text-dark mb-2">{{ $country->name  }}</h6>
                                <p class="mb-1 small">Total Cars</p>
                                <h3 class="fw-bold text-primary mb-3">{{ $totalAssignedCars }}</h3>
                                <img src="{{ asset("admin-assets/images/cars/c1.png") }}" class="img-fluid mt-2" alt="Car1">
                              </div>
                            </a>
                          </div>
                        </div>
                        {{-- statuses total count card --}}
                          @php $imageIndex = 5; @endphp
                          @foreach ($statuses as $statusKey => $statusLabel)
                            <div class="col-md-3 col-sm-6">
                                <div class="card h-100 shadow-sm border-0 rounded-3 d-flex flex-column justify-content-between text-center">
                                    <a class="text-decoration-none"
                                        href="{{ ($statusCounts[$statusKey] ?? 0) > 0 
                                            ? route('car-profiles.index', [
                                                'per_page'   => $per_page_value,
                                                'car_status' => $statusKey
                                              ]) 
                                            : 'javascript:void(0)' }}">
                                        <div class="card-body px-2">
                                            <h6 class="fw-semibold text-dark mb-2">{{ $statusLabel }}</h6>
                                            <p class="mb-1 small">Total Cars</p>
                                            <h3 class="fw-bold text-primary mb-3">{{ $statusCounts[$statusKey] ?? 0 }}</h3>
                                            <img src="{{ asset("admin-assets/images/cars/c{$imageIndex}.png") }}?v={{ time() }}" class="img-fluid" alt="Car Image">
                                        </div>
                                    </a>
                                </div>
                            </div>
                              @php
                                  $imageIndex++;
                                  if ($imageIndex > 12) $imageIndex = 1;
                              @endphp
                          @endforeach
                      </div>
                    </div>
                  </div>

                  <!-- Right Column -->
                  <div class="col-lg-4 d-flex flex-column">
                    <div class="card mb-2" style="width: 21.5rem;">
                      <img src="{{ asset('admin-assets/images/cars/c-right.png') }}" class="card-img-top" alt="...">
                    </div>

                    <!-- Last Login -->
                    <div class="row mt-3">
                      <div class="col-12 grid-margin">
                        <div class="card shadow rounded-lg">
                          <div class="card-body">
                            <h3 class="text-center border-b pb-3 text-gray-800 font-semibold text-lg">
                              Last Login
                            </h3>

                            <div class="d-flex justify-content-between mt-4 mb-3">
                              <h4 class="card-title-dash text-gray-700 font-medium"> Name</h4>
                              <h4 class="card-title-dash text-gray-700 font-medium"> Login at</h4>
                            </div>

                            <div class="d-flex justify-content-between">
                              <p class="text-gray-900 font-medium">{{ ucwords(auth()->user()->name) }}</p>
                              <p class="text-gray-900 font-medium">{{ now()->format('d-m-Y') }}</p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="row">
                      <div class="col-12 grid-margin">
                        <div class="card shadow rounded-lg">
                          <div class="card-body">
                            <h4 class="card-title text-center border-b pb-3 text-gray-800 font-semibold text-lg">
                              Quick Links
                            </h4>

                            <div class="d-flex justify-content-between mt-4 mb-3">
                              <a href="{{ route('car-expenses.index') }}" class="card-link text-primary hover:text-blue-700 transition duration-200 font-medium">
                                Car Expenses
                              </a>
                              <a href="{{ route('office-expenses.index') }}" class="card-link text-primary hover:text-blue-700 transition duration-200 font-medium">
                                Office Expenses
                              </a>
                            </div>

                            <div class="d-flex justify-content-between mb-3">
                              <a href="{{ route('accounts.index', ['type' => 'bank']) }}" class="card-link text-primary hover:text-blue-700 transition duration-200 font-medium">
                                Bank Accounts
                              </a>
                              <a href="{{ route('accounts.index', ['type' => 'cash']) }}" class="card-link text-primary hover:text-blue-700 transition duration-200 font-medium">
                                Cash Accounts
                              </a>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div> <!-- tab-pane -->
            </div> <!-- tab-content -->
          </div> <!-- home-tab -->
        </div> <!-- col-sm-12 -->
      </div> <!-- row -->
    </div> <!-- content-wrapper -->

    @include('dashboards.partials.footer')

  </div> <!-- main-panel -->
</div> <!-- page-body-wrapper -->
</div> <!-- container-scroller -->
