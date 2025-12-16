@include('dashboards.partials.header')

<div class="container-fluid page-body-wrapper">
  @include('dashboards.partials.sidebar')
  <style>
    .account-statement td {
      padding-top: 0.8rem;
      padding-bottom: 0.8rem;
    }
  </style>
  <div class="main-panel">
    <div class="content-wrapper px-2">
      <div class="row">
        <div class="col-sm-12">
          <div class="card shadow-sm">
            <div class="text-bg-light card-header d-flex justify-content-between align-items-center py-3">
              <h4 class="card-title mb-0">{{ ucfirst($type) }} Account Statement</h4>
            </div>
            
            <div class="max-w-md ml-6 mt-6">
              <div class="bg-white shadow-md rounded-lg p-3 text-sm border">
                <div class="flex mb-2">
                  <div class="w-1/3 font-semibold">Account Title</div>
                  <div class="w-2/3">{{ $account->title }}</div>
                </div>
                <div class="flex mb-2">
                  <div class="w-1/3 font-semibold">Currency</div>
                  <div class="w-2/3">{{ $account->country->currency_type }}</div>
                </div>
                <div class="flex mb-2">
                  <div class="w-1/3 font-semibold">Account Type</div>
                  <div class="w-2/3">{{ ucfirst($type) }} Account</div>
                </div>
                <div class="flex">
                  <div class="w-1/3 font-semibold">Start Date</div>
                  <div class="w-2/3">{{ \Carbon\Carbon::parse($account->created_at)->format('d/m/Y') }}</div>
                </div>
              </div>
            </div>

            <div class="card-body">
              <div class="table-responsive">
                @if ($statements->isEmpty())
                  <div class="alert alert-warning text-center shadow-sm rounded m-0">
                      <i class="mdi mdi-alert-circle-outline"></i>
                      No transactions found for this account.
                  </div>
                @else
                
                  <div class="container mx-auto mb-4 px-0">
                    <form method="GET" action="{{ route('accounts.statement', ['account' => $account->id, 'type' => $type]) }}" class="mb-4">
                      <div class="flex items-center justify-between space-x-4">
                          <div class="flex flex-col w-1/4">
                              <label for="start_date" class="text-lg font-bold text-gray-700 mb-1">Start Date</label>
                              <input type="date" name="start_date" class="p-2 border-2 border-gray-300 rounded-md focus:outline-none focus:border-blue-500" value="{{ request('start_date') }}">
                          </div>

                          <div class="flex flex-col w-1/4">
                              <label for="end_date" class="text-lg font-bold text-gray-700 mb-1">End Date</label>
                              <input type="date" name="end_date" class="p-2 border-2 border-gray-300 rounded-md focus:outline-none focus:border-blue-500" value="{{ request('end_date') }}">
                          </div>

                          <div class="flex items-center justify-center">
                              <button type="submit" class="mt-4 btn btn-primary px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none">
                                  Apply Date Range
                              </button>
                          </div>

                          <div class="flex items-center justify-center mt-4">
                              <a href="{{ route('accounts.statement', ['account' => $account->id, 'type' => $type]) }}" class="btn btn-secondary px-6 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none">
                                  Reset Date Filter
                              </a>
                          </div>
                      </div>
                    </form>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                      <div class="bg-green-100 p-4 rounded-lg border-2 border-green-500 text-center pb-2 pt-3">
                        <h5 class="text-lg font-semibold">Total Credit Amount</h5>
                        <p class="text-2xl text-green-600 mb-0">{{ number_format($totals->total_credit_amount, 2) }}</p>
                        <p class="text-sm text-green-600">${{ number_format($totals->total_credit_amount / $account->country->currency_rate, 2) }}</p>
                      </div>

                      <div class="bg-red-100 p-4 rounded-lg border-2 border-red-500 text-center pb-2 pt-3">
                        <h5 class="text-lg font-semibold">Total Debit Amount</h5>
                        <p class="text-2xl text-red-600 mb-0">{{ number_format($totals->total_debit_amount, 2) }}</p>
                        <p class="text-sm text-red-600">${{ number_format($totals->total_debit_amount / $account->country->currency_rate, 2) }}</p>
                      </div>

                      <div class="bg-blue-100 p-4 rounded-lg border-2 border-blue-500 text-center pb-2 pt-3">
                        <h5 class="text-lg font-semibold">Closing Balance</h5>
                        <p class="text-2xl text-blue-600 mb-0">{{ number_format($closingBalance, 2) }}</p>
                        <p class="text-sm text-blue-600">${{ number_format($closingBalance / $account->country->currency_rate, 2) }}</p>
                      </div>

                      <div class="bg-gray-100 p-4 rounded-lg border-2 border-gray-500 text-center pb-2 pt-3">
                        <h5 class="text-lg font-semibold">Count of Cr. & Dr.</h5>
                        <p class="text-2xl text-gray-600">CR: {{ $totals->credit_count }} / DR: {{ $totals->debit_count }}</p>
                      </div>
                    </div>
                  </div>

                  @if($startDate && $endDate)
                      <div class="alert alert-info text-center mb-4">
                          <strong>Statement for the period:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                      </div>
                  @endif

                  <table class="table table-bordered table-striped w-auto" style="min-width: 100%; overflow-x: visible;">
                    <thead>
                        <tr class="bg-text-light">
                            <th>#</th>
                            <th>Date</th>
                            {{-- <th>Account Title</th> --}}
                            <th>Country</th>
                            <th>Description</th>
                            <th>Opening Balance</th>
                            {{-- <th>Acc Assign To</th> --}}
                            <th>Debit</th>
                            <th>Credit</th>
                            <th>Closing Balance</th>
                            <th>Currency</th>
                            <th>Transaction By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($statements as $key => $statement)
                            <tr class="account-statement">
                                <td>{{ $key+1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($statement->created_at)->format('d/m/Y') }}</td>
                                {{-- <td>{{ $account->title }}</td> --}}
                                <td>{{ $account->country->name ?? 'N/A' }}</td>
                                <td>{{ ucfirst($statement->transaction_type) }}</td>
                                <td>{{ number_format($statement->opening_balance, 2) }}</td>
                                {{-- <td>{!! ucwords($statement->assignedUser?->name ?? '<span style="display: inline-block; width: 100%; text-align: center;">-</span>') !!}</td> --}}
                                {{-- <td>{{ number_format($statement->amount, 2) }}</td> --}}
                                <td class="{{ $statement->flow_type == 'debit' ? 'text-danger' : '' }}">
                                    @if($statement->flow_type == 'debit')
                                        {{ number_format($statement->amount, 2) }}
                                    @else
                                        {{ '' }}
                                    @endif
                                </td>
                                <td class="{{ $statement->flow_type == 'credit' ? 'text-success' : '' }}">
                                    @if($statement->flow_type == 'credit')
                                        {{ number_format($statement->amount, 2) }}
                                    @else
                                        {{ '' }}
                                    @endif
                                </td>
                                <td>{{ number_format($statement->closing_balance, 2) }}</td>
                                <td>{{ $statement->currency_type }}</td>
                                <td>{{ $statement->user->name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                  </table>
                  @if ($statements->hasPages())
                      <div class="mt-4 d-flex justify-content-center">
                          {{ $statements->links('pagination::bootstrap-5') }}
                      </div>
                  @endif
                @endif
              </div> <!-- table-responsive -->
            </div> <!-- card-body -->
          </div> <!-- card -->
        </div> <!-- col-sm-12 -->
      </div> <!-- row -->
    </div> <!-- content-wrapper -->

    @include('dashboards.partials.footer')
  </div> <!-- main-panel -->
</div> <!-- page-body-wrapper -->
