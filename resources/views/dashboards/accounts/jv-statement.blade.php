@include('dashboards.partials.header')

<div class="container-fluid page-body-wrapper">
  @include('dashboards.partials.sidebar')
  <style>
    .account-statement td {
      padding-top: 0.8rem;
      padding-bottom: 0.8rem;
    }
    .table-header th{
      background-color: #6495ed;color:#fff;
    }
  </style>
  <div class="main-panel">
    <div class="content-wrapper px-3 py-0">
      <div class="row">
        <div class="col-sm-12">
          <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #6495ed;">
              <h4 class="card-title text-white mb-0">JV Statement</h4>
                    <a href="{{ route('accounts.journal-voucher') }}" class="btn btn-primary py-2">Back</a>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div>
                      <label class="text-lg font-bold text-gray-700 mb-1">JV Number:</label>
                      {{ $jvStatements->first()->jv_no }}
                  </div>
                  <div>
                      <label class="text-lg font-bold text-gray-700 mb-1">Date:</label>
                      {{ $jvStatements->first()->created_at->format('d/m/Y H:i') }}
                  </div>
                </div>
                @php
                  $fromAccount = null;
                  $toAccount = null;

                  foreach ($jvStatements as $statement) {
                      if ($statement->flow_type == 'debit') {
                          $fromAccount = $statement->account->title ?? 'N/A';
                      } elseif ($statement->flow_type == 'credit') {
                          $toAccount = $statement->account->title ?? 'N/A';
                      }
                  }
                @endphp
                <p> <strong>Particulars:</strong>Internal transfer from Account <strong>{{ $fromAccount }}</strong> to Account <strong>{{ $toAccount }}</strong></p>
                  <table class="table table-bordered w-auto" style="min-width: 100%; overflow-x: visible;">
                    <thead>
                        <tr class="table-header">
                            <th>Account Head/Ledger</Head></th>
                            <th>Debit</th>
                            <th>Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jvStatements as $statement)
                            <tr class="account-statement">
                                <td>{{ $statement->account->title ?? 'N/A' }}</td>
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
                            </tr>
                        @endforeach
                    </tbody>
                  </table>
                <p class="mt-2"> <strong>Narration:</strong> {{ $jvStatements->first()->jv_description}}</p>
              </div> <!-- table-responsive -->
            </div> <!-- card-body -->
          </div> <!-- card -->
        </div> <!-- col-sm-12 -->
      </div> <!-- row -->
    </div> <!-- content-wrapper -->

    @include('dashboards.partials.footer')
  </div> <!-- main-panel -->
</div> <!-- page-body-wrapper -->
