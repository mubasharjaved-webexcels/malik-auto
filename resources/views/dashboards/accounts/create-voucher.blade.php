@include('dashboards.partials.header')

<div class="container-fluid page-body-wrapper">
  @include('dashboards.partials.sidebar')
  <style>
    .table-header{
      background-color: #6495ed;
    }
  </style>
  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-sm-8 offset-sm-2">
          <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center table-header">
              <h4 class="card-title text-white mb-0">Transfer Amount</h4>
                <a href="{{ route('accounts.journal-voucher') }}" class="btn btn-primary py-2">Back</a>
            </div>
            <div class="card-body">

              @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <script>
                    setTimeout(function () {
                        let alert = document.getElementById('error-alert');
                        if (alert) {
                            alert.classList.remove('show');
                            alert.classList.add('fade');
                            alert.style.display = 'none';
                        }
                    }, 4000);
                </script>
              @endif

              @if ($errors->any())
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Account Error',
                        text: "{{ $errors->first() }}",
                    });
                </script>
              @endif
              
              <form method="POST" action="{{ route('accounts.store-voucher') }}">
                @csrf
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label>Transfer From</label>
                    <select name="from_account" id="from_account" class="form-control text-dark" required>
                      <option value="">-- Select Account --</option>
                      @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" data-balance="{{ $acc->opening_balance }}">{{ $acc->title }} [{{ $acc->country->currency_type }}] ({{ $acc->country->name }})</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label>Available Balance</label>
                    <input type="text" id="available_balance" class="form-control" disabled>
                  </div>
                </div>

                <div class="mb-3">
                  <label>Receive In</label>
                  <select name="to_account" class="form-control text-dark" required>
                    <option value="">-- Select Account --</option>
                    @foreach($accounts as $acc)
                      <option value="{{ $acc->id }}">{{ $acc->title }} [{{ $acc->country->currency_type }}] ({{ $acc->country->name }})</option>
                    @endforeach
                  </select>
                </div>

                <div class="mb-3">
                  <label>Amount <small>(In transfer from account currency)</small></label>
                  <input type="number" step="0.01" oninput="this.value = this.value.replace(/[^0-9.]/g, '')" name="amount" class="form-control" required>
                </div>

                <div class="mb-3">
                  <label>Description</label>
                  <textarea name="jv_description" class="form-control" rows="3" required></textarea>
                </div>

                <button type="submit" class="btn btn-success">Add</button>
                <a href="{{ route('accounts.journal-voucher') }}" class="btn btn-danger">Cancel</a>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    @push('scripts')
      <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fromAccount = document.getElementById('from_account');
            const balanceField = document.getElementById('available_balance');

            fromAccount.addEventListener('change', function () {
                const selectedOption = fromAccount.options[fromAccount.selectedIndex];
                const balance = selectedOption.dataset.balance || '';
                balanceField.value = balance;
            });
        });
      </script>
    @endpush
    @include('dashboards.partials.footer')
  </div>
</div>