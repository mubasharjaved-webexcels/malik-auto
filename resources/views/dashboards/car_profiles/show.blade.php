@include('dashboards.partials.header')

<div class="container-fluid page-body-wrapper">
  @include('dashboards.partials.sidebar')

  <div class="main-panel">
    <div class="content-wrapper">
      <div class="container">
        <h2 class="text-2xl font-semibold mb-4">Car Profile Details</h2>
          <div class="row">
              <!-- Rec No -->
              <div class="col-md-6 mb-3">
                  <label>Rec No:</label>
                  <p>{{ $carProfile->rec_no }}</p>
              </div>

              <!-- Located Yard -->
              <div class="col-md-6 mb-3">
                  <label>Located Yard:</label>
                  <p>{{ $carProfile->located_yard }}</p>
              </div>
          </div>

          <div class="row">
              <!-- Grade -->
              <div class="col-md-6 mb-3">
                  <label>Grade:</label>
                  <p>{{ $carProfile->grade }}</p>
              </div>

              <!-- Seats -->
              <div class="col-md-6 mb-3">
                  <label>Seats:</label>
                  <p>{{ $carProfile->seats }}</p>
              </div>
          </div>

          <div class="row">
              <!-- Chassis -->
              <div class="col-md-6 mb-3">
                  <label>Chassis:</label>
                  <p>{{ $carProfile->chassis }}</p>
              </div>

              <!-- Shift -->
              <div class="col-md-6 mb-3">
                  <label>Shift:</label>
                  <p>{{ $carProfile->shift }}</p>
              </div>
          </div>

          <div class="row">
              <!-- Mileage -->
              <div class="col-md-6 mb-3">
                  <label>Mileage:</label>
                  <p>{{ $carProfile->mileage }}</p>
              </div>

              <!-- Engine CC -->
              <div class="col-md-6 mb-3">
                  <label>Engine CC:</label>
                  <p>{{ $carProfile->engine_cc }}</p>
              </div>
          </div>

          <div class="row">
              <!-- Dimension -->
              <div class="col-md-6 mb-3">
                  <label>Model Year:</label>
                  <p>{{ $carProfile->dimension }}</p>
              </div>

              <!-- M3 -->
              <div class="col-md-6 mb-3">
                  <label>M3:</label>
                  <p>{{ $carProfile->m3 }}</p>
              </div>
          </div>

            @unlessrole('manager|salesperson')
            <div class="row">
              <!-- Price -->
              <div class="col-md-6 mb-3">
                  <label>Price:</label>
                  <p>{{ $carProfile->price }}</p>
              </div>

              <div class="col-md-6 mb-3">
                  <label>Sale Price:</label>
                  <p>{{ $carProfile->sale_price }}</p>
              </div>
            </div>
            @endunlessrole

          <div class="row">
            @unlessrole('manager|salesperson')
              <div class="col-md-6 mb-3">
                  <label>Sold Price:</label>
                  <p>{{ $carProfile->sold_price }}</p>
              </div>
            @endunlessrole

              <!-- Fuel -->
              <div class="col-md-6 mb-3">
                  <label>Fuel Type:</label>
                  <p>{{ $carProfile->fuel }}</p>
              </div>
          </div>

          <div class="row">
              <!-- Max Loading -->
              <div class="col-md-6 mb-3">
                  <label>Max Loading:</label>
                  <p>{{ $carProfile->max_loading }}</p>
              </div>

              <!-- Country -->
              <div class="col-md-6 mb-3">
                  <label>Country:</label>
                  <p>{{ $carProfile->country->name ?? 'N/A' }}</p>
              </div>
          </div>

          <div class="row">
              <!-- Car Status -->
              <div class="col-md-6 mb-3">
                  <label>Car Status:</label>
                  <p>{{ ucfirst(str_replace('_', ' ', $carProfile->car_status)) }}</p>
              </div>
              <!-- Transit Expense -->
              <div class="col-md-6 mb-3">
                  <label>Transit Expense:</label>
                  <p>{{ ucfirst(str_replace('_', ' ', $carProfile->transit_expense)) }}</p>
              </div>
          </div>

          <div class="row">
              <!-- Currency Type -->
              <div class="col-md-6 mb-3">
                  <label>Currency Type:</label>
                  <p>{{ ucfirst(str_replace('_', ' ', $carProfile->currency_type)) }}</p>
              </div>
          </div>

          <div class="row">
              <!-- Car Image -->
              <div class="col-md-6 mb-3">
                  <label>Car Image:</label>
                  @if(!empty($carProfile->car_image))
                      <img src="{{ asset('storage/'.$carProfile->car_image) }}" alt="Car Image" style="max-height: 200px;">
                  @else
                      <p>No image available</p>
                  @endif
              </div>

              <!-- Car Video -->
              <div class="col-md-6 mb-3">
                  <label>Car Video:</label>
                  @if(!empty($carProfile->car_video))
                      <video width="200" height="150" controls>
                          <source src="{{ asset('storage/'.$carProfile->car_video) }}" type="video/mp4">
                          Your browser does not support the video tag.
                      </video>
                  @else
                      <p>No video available</p>
                  @endif
              </div>
          </div>

          <div class="col-md-12 mt-3">
              <a href="{{ route('car-profiles.edit', $carProfile) }}" class="btn btn-primary">Edit</a>
              <a href="{{ route('car-profiles.index') }}" class="btn btn-secondary">Back to List</a>
          </div>
      </div>
    </div>

    @include('dashboards.partials.footer')
  </div> <!-- main-panel -->
</div> <!-- page-body-wrapper -->
