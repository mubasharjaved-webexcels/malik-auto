@include('dashboards.partials.header')

<div class="container-fluid page-body-wrapper">
  @include('dashboards.partials.sidebar')

  <div class="main-panel">
    <div class="content-wrapper">
      <div class="container">
        <h2 class="text-2xl font-semibold mb-4">Edit Car Profile</h2>

        <form action="{{ route('car-profiles.update', $carProfile->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          @include('dashboards.car_profiles.partials.form', ['carProfile' => $carProfile])
        </form>
      </div>
    </div>

    @php
      $jpyRate = optional(collect($countries)->firstWhere('currency_type', 'JPY'))->currency_rate;
    @endphp

    @include('dashboards.car_profiles.partials.yard-script')
    @include('dashboards.car_profiles.partials.status-script') 
    @include('dashboards.car_profiles.partials.price-conversion-script', ['jpyRate' => number_format($jpyRate, 6, '.', '')]) 
    @include('dashboards.partials.footer')
  </div> <!-- main-panel -->
</div> <!-- page-body-wrapper -->
