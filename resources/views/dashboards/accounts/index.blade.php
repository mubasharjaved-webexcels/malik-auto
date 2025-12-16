
@include('dashboards.partials.header')

<div class="container-fluid page-body-wrapper">
  @include('dashboards.partials.sidebar')
<style>
  .account-list td {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
}
</style>
<div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-sm-12">
          <div class="card shadow-sm">
            <div class="card-body">
              {{-- success alert --}}
              @if(session('success'))
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
              {{-- success alert --}}
              <div class="d-flex justify-content-between align-items-center">
                <h4 class="card-title">{{ ucfirst($type) }} Accounts</h4>
                @unlessrole('manager|salesperson')
                  <a href="{{ route('accounts.create', $type) }}" class="btn btn-primary mb-3">Add {{ ucfirst($type) }} Account</a>
                @endunlessrole
              </div>
              @if ($accounts->isEmpty())
                  <p>No {{ $type }} accounts found.</p>
              @else
              <table class="table table-bordered table-striped">
                  <thead>
                      <tr>
                          <th>Title</th>
                          <th>Country</th>
                          <th>Currency</th>
                          {{-- <th>Yard</th> --}}
                          <th>Balance</th>
                          <th>Actions</th>
                      </tr>
                  </thead>
                  <tbody>
                      @foreach ($accounts as $acc)
                          <tr class="account-list">
                              <td>{{ $acc->title }}</td>
                              <td>{{ $acc->country->name }}</td>
                              <td>{{ $acc->country->currency_type }}</td>
                              {{-- <td>{{ $acc->yard ?? '—' }}</td> --}}
                              <td>{{ number_format($acc->opening_balance, 2) }}</td>
                              <td>
                                  <a href="{{ route('accounts.statement', ['account' => $acc->id, 'type' => $acc->type]) }}" class="btn btn-sm btn-success">Statement</a>
                                @unlessrole('manager|salesperson')
                                  <a href="{{ route('accounts.edit', $acc->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                  
                                  <form method="POST" action="{{ route('accounts.toggleStatus', $acc->id) }}" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-{{ $acc->status == 'active' ? 'danger' : 'success' }}">
                                      {{ $acc->status == 'active' ? 'Deactivate' : 'Activate' }}
                                    </button>
                                  </form>
                                @endunlessrole
                              </td>
                          </tr>
                      @endforeach
                  </tbody>
              </table>
              @endif
            </div>
          </div>
        </div> <!-- col-sm-12 -->
      </div> <!-- row -->
    </div> <!-- content-wrapper -->

    @include('dashboards.partials.footer')
  </div> <!-- main-panel -->
</div> <!-- page-body-wrapper -->
</div> <!-- container-scroller -->
