
@include('dashboards.partials.header')

<div class="container-fluid page-body-wrapper">
  @include('dashboards.partials.sidebar')

  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-sm-12">
          <div class="home-tab">
            <div class="tab-content tab-content-basic">
              <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="row">
                  <div class="col-lg-8 flex-column">

                    <!-- Card 1 -->
                    <div class="container mt-4 mb-4 px-0">
                      <div class="row g-3">
                        @php $i = 1; @endphp
                        @foreach($countries as $country)
                          <div class="col-6 col-md-3 mt-0">
                            <div class="card text-center shadow-sm rounded-4">
                              <a class="text-decoration-none" href="{{ route('car-profiles.index') }}">
                              <div class="card-body">
                                <p class="card-title fw-semibold mb-1">{{ $country->name  }}</p>
                                <p class="card-title fw-semibold mb-1">Total Cars</p>
                                <h4 class="text-primary fw-bold">{{ $country->car_profiles_count }}</h4>
                                <img src="{{ asset("admin-assets/images/cars/c$i.png") }}" class="img-fluid mt-2" alt="Car {{ $i }}">
                              </div>
                              </a>
                            </div>
                          </div>
                          @php $i++ @endphp
                        @endforeach
                      </div>
                    </div>

                    <!-- Investment Overview -->
                    <div class="row flex-grow">
                      <div class="col-12 grid-margin stretch-card">
                        <div class="card card-rounded">
                          <div class="card-body">
                            <div class="d-sm-flex justify-content-between align-items-start">
                              <h4 class="card-title card-title-dash">Total Car Investment</h4>
                            </div>
                              <div class="d-sm-flex align-items-center mt-1 justify-content-between">
                                <div class="d-sm-flex align-items-center mt-4 justify-content-between">
                                  <h2 class="me-2 fw-bold">$36,2531.00</h2>
                                  <h4 class="me-2">USD</h4>
                                  <h4 class="text-success">(+1.37%)</h4>
                                </div>
                                <div class="me-3">
                                  <div id="marketingOverview-legend"></div>
                                </div>
                              </div>
                            <div class="chartjs-bar-wrapper mt-3">
                              <canvas id="marketingOverview"></canvas>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Country Cards -->
                    <div class="row flex-grow">
                      <div class="col-12 grid-margin stretch-card">
                        <div class="container my-4 px-0">
                          <div class="card shadow rounded-4 p-3">
                            <div class="row text-center align-items-center">
                              @php
                                $countries = ['UAE' => 118, 'USA' => 203, 'RSA' => 313];
                                $flag = 1;
                              @endphp
                              @foreach($countries as $country => $count)
                              <div class="col-md-4 {{ $loop->last ? '' : 'border-end' }}">
                                <p class="mb-1 fw-semibold">Total Cars In <span class="text-primary">{{ $country }}</span></p>
                                <h3 class="fw-bold text-primary">{{ $count }}</h3>
                                <img src="{{ asset("admin-assets/images/cars/cf$flag.png") }}" alt="{{ $country }} Car" class="img-fluid mt-2" style="max-height: 100px;">
                              </div>
                              @php $flag++; @endphp
                              @endforeach
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>

                  <!-- Right Column -->
                  <div class="col-lg-4 d-flex flex-column">
                    <div class="card mb-2" style="width: 20.5rem;">
                      <img src="{{ asset('admin-assets/images/cars/c-right.png') }}" class="card-img-top" alt="...">
                    </div>

                    <!-- Quick Links -->
                    <div class="row flex-grow">
                      <div class="col-12 grid-margin stretch-card">
                        <div class="card card-rounded">
                          <div class="card-body">
                            <h4 class="card-title card-title-dash text-center border-bottom pb-2">Quick Links</h4>
                            @for($i = 0; $i < 4; $i++)
                            <div class="d-flex justify-content-between mb-2">
                              <a href="#" class="card-link">Profile</a>
                              <a href="#" class="card-link">Another link</a>
                            </div>
                            @endfor
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Last Login -->
                    <div class="row flex-grow">
                      <div class="col-12 grid-margin stretch-card">
                        <div class="card card-rounded">
                          <div class="card-body">
                            <h3 class="text-center border-bottom pb-2">Last Login</h3>
                            <div class="d-flex justify-content-between mb-3">
                              <h4 class="card-title-dash">Login Name</h4>
                              <h4 class="card-title-dash">Login Time</h4>
                            </div>
                            <div class="mt-3 d-flex justify-content-between">
                              <p>Alex Halex</p>
                              <p>00:00:00</p>
                            </div>
                            <div class="mt-3 d-flex justify-content-between">
                              <p>chris johns</p>
                              <p>00:00:00</p>
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
