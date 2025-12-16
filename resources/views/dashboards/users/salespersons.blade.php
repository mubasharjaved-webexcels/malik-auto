
@include('dashboards.partials.header')

<div class="container-fluid page-body-wrapper">
  @include('dashboards.partials.sidebar')

<div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-sm-12">
          
          <div class="container-fluid">
            <h3 class="mb-4">Salespersons</h3>

            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Country</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $index => $user)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $user->country->name ?? 'N/A' }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No salespersons found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        </div> <!-- col-sm-12 -->
      </div> <!-- row -->
    </div> <!-- content-wrapper -->

    @include('dashboards.partials.footer')
  </div> <!-- main-panel -->
</div> <!-- page-body-wrapper -->
</div> <!-- container-scroller -->
