@include('dashboards.partials.header')
<div class="container-fluid page-body-wrapper">
  @include('dashboards.partials.sidebar')
  <style>
    .dropdown-toggle::after {
      display: none !important;
    }
  </style>
@php
 $currencies = \App\Models\Country::select('currency_type')
                ->whereNotNull('currency_type')
                ->distinct()
                ->pluck('currency_type');

 $carProfiles = \App\Models\CarProfile::select('id', 'rec_no', 'chassis', 'car_image')
                ->whereNotNull('rec_no')
                ->orderBy('rec_no')
                ->get();
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

      <div class="row">
        <div class="col-sm-12">
          <div class="card">
           
           
          </div>
        </div> <!-- col-sm-12 -->
      </div> <!-- row -->

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



@include('dashboards.partials.footer')