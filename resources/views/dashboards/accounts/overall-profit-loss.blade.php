
@include('dashboards.partials.header')

<div class="container-fluid page-body-wrapper">
  @include('dashboards.partials.sidebar')
  <style>
    .profit-list td {
      padding-top: 0.7rem;
      padding-bottom: 0.7rem;
    }
    .table-header{
      background-color: #6495ed;color:#fff;
    }
    .table-header th{
      background-color: #6495ed;color:#fff;
    }
  </style>
  <div class="main-panel">
      <div class="content-wrapper px-2 py-0">
        <div class="row">
          <div class="col-sm-12">
            <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center table-header py-3">
              <h4 class="card-title text-white mb-0">overall profit and loss</h4>
            </div>
              <div class="card-body" style="border-radius: 4px;">
                <div class="table-responsive">
                  <table class="table table-bordered mb-3">
                      <thead>
                          <tr class="table-header">
                              <th>Heads</th>
                              @foreach ($report as $data)
                                  <th>{{ $data['country'] }}</th>
                              @endforeach
                              <th>Total</th>
                          </tr>
                      </thead>
                      <tbody>
                          {{-- Income --}}
                          <tr><td colspan="{{ count($report) + 2 }}" class="fw-bold text-start" style="background-color: #f0f8ff;">Income</td></tr>
                          <tr>
                              <td>Total Sold Price</td>
                              @foreach ($report as $data)
                                  <td>${{ number_format($data['sold'], 2) }}</td>
                              @endforeach
                              <td><strong>${{ number_format($totals['sold'], 2) }}</strong></td>
                          </tr>

                          {{-- Expenses --}}
                          <tr><td colspan="{{ count($report) + 2 }}" class="fw-bold text-start" style="background-color: #f0f8ff;">Expenses</td></tr>
                          <tr>
                              <td>Total Auction Price</td>
                              @foreach ($report as $data)
                                  <td>${{ number_format($data['auction'], 2) }}</td>
                              @endforeach
                              <td><strong>${{ number_format($totals['auction'], 2) }}</strong></td>
                          </tr>
                          <tr>
                              <td>Total Car Expenses</td>
                              @foreach ($report as $data)
                                  <td>${{ number_format($data['car_expenses'], 2) }}</td>
                              @endforeach
                              <td><strong>${{ number_format($totals['car_expenses'], 2) }}</strong></td>
                          </tr>
                          <tr>
                              <td>Total Office Expenses</td>
                              @foreach ($report as $data)
                                  <td>${{ number_format($data['office_expenses'], 2) }}</td>
                              @endforeach
                              <td><strong>${{ number_format($totals['office_expenses'], 2) }}</strong></td>
                          </tr>
                          <tr class="fw-bold">
                              <td>Total Expenses</td>
                              @foreach ($report as $data)
                                  <td>${{ number_format($data['total_expenses'], 2) }}</td>
                              @endforeach
                              <td><strong>${{ number_format($totals['total_expenses'], 2) }}</strong></td>
                          </tr>

                          {{-- Net Profit/Loss --}}
                          <tr class="fw-bold">
                              <td>Net Profit/Loss</td>
                              @foreach ($report as $data)
                                  @php
                                      $value = $data['profit_loss'];
                                      $formatted = ($value < 0 ? '-$' . number_format(abs($value), 2) : '$' . number_format($value, 2));
                                      $class = $value < 0 ? 'text-danger' : 'text-success';
                                  @endphp
                                  <td class="{{ $class }}">{{ $formatted }}</td>
                              @endforeach
                              @php
                                  $value = $totals['profit_loss'];
                                  $formatted = ($value < 0 ? '-$' . number_format(abs($value), 2) : '$' . number_format($value, 2));
                                  $class = $value < 0 ? 'text-danger' : 'text-success';
                              @endphp
                              <td class="{{ $class }}"><strong>{{ $formatted }}</strong></td>
                          </tr>
                      </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div> <!-- col-sm-12 -->
        </div> <!-- row -->
      </div> <!-- content-wrapper -->

      @include('dashboards.partials.footer')
    </div> <!-- main-panel -->
  </div> <!-- page-body-wrapper -->
</div> <!-- container-scroller -->
