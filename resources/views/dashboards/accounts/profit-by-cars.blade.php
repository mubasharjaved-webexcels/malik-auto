
@include('dashboards.partials.header')

<div class="container-fluid page-body-wrapper">
  @include('dashboards.partials.sidebar')
  <style>
    .profit-list td {
      padding: 1rem;
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
              <h4 class="card-title text-white mb-0">Profit by Cars</h4>
            </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped mb-3">
                      <thead>
                          <tr class="table-header">
                              <th>#</th>
                              <th>Car Stock No</th>
                              <th>Auction Price</th>
                              <th>Expense</th>
                              <th>Sold price</th>
                              <th>Sold By</th>
                              <th>Profit & Loss</th>
                          </tr>
                      </thead>
                      <tbody>
                          
                        @forelse ($profitByCars as $key => $profitByCar)
                          <tr class="profit-list">
                            <td>{{ $key+1 }}</td>
                            <td>{{ $profitByCar->rec_no }}</td>
                            <td>${{ number_format($profitByCar->price,2) }}</td>
                            <td>${{ number_format(($profitByCar->expenses_sum_usd_amount ?? 0), 2) }}</td>
                            <td>${{ number_format($profitByCar->sold_price,2) }}</td>
                            <td>{{ $profitByCar->assignedManager->name ?? 'Admin'}}</td>
                            @php
                              $totalAmount = $profitByCar->price + ($profitByCar->expenses_sum_usd_amount ?? 0);
                              $profitOrLoss = $profitByCar->sold_price - $totalAmount;
                              $textClass = $totalAmount < $profitByCar->sold_price ? 'text-success' : 'text-danger';
                            @endphp

                            <td class="{{ $textClass }}">
                              {{ ($profitOrLoss < 0 ? '-$' : '$') . number_format(abs($profitOrLoss), 2) }}
                            </td>
                          </tr>
                        @empty
                          <tr><td colspan="7" class="text-center">No vouchers found</td></tr>
                        @endforelse
                      </tbody>
                  </table>
                    @if ($profitByCars->hasPages())
                        <div class="mt-4 d-flex justify-content-center">
                            {{ $profitByCars->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
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
