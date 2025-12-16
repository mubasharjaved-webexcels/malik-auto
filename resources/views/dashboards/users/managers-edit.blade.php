
@include('dashboards.partials.header')

<div class="container-fluid page-body-wrapper">
  @include('dashboards.partials.sidebar')

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
              <h4>Edit Manager Account</h4>
              <br>

                <form method="POST" action="{{ isset($manager) ? route('managers.update', $manager->id) : route('managers.store') }}">
                    @csrf
                    @if(isset($manager))
                        @method('PUT')
                    @endif
                
                    <input type="hidden" name="id" value="{{ $manager->id ?? '' }}">
                
                    <div class="mb-3">
                        <label for="name" class="form-label">Manager Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $manager->name ?? '') }}" required>
                    </div>
                
                    <div class="mb-3">
                        <label for="email" class="form-label">Manager Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $manager->email ?? '') }}" required>
                    </div>
                
                    <div class="mb-3">
                        <label for="phone" class="form-label">Manager Phone</label>
                        <input type="number" name="phone" id="phone" class="form-control" value="{{ old('phone', $manager->phone ?? '') }}" required>
                    </div>
                
                    <div class="mb-3">
                        <label for="password" class="form-label">Manager Password</label>
                        @php
                            try {
                                $tempPassword = $manager->temp_pass ? decrypt($manager->temp_pass) : '';
                            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                                $tempPassword = '';
                            }
                        @endphp
                        <input type="text" name="password" id="password" class="form-control" value="{{ old('password', $tempPassword) }}" required>
                    </div>
                
                    <button type="submit" class="btn btn-success">{{ isset($manager) ? 'Update' : 'Create' }}</button>
                </form>
                
            </div>
          </div>
        </div> <!-- col-sm-12 -->
      </div> <!-- row -->
    </div> <!-- content-wrapper -->

    @include('dashboards.partials.footer')
  </div> <!-- main-panel -->
</div> <!-- page-body-wrapper -->
</div> <!-- container-scroller -->
