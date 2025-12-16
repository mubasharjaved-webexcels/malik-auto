
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
              <h4>{{ $isEdit ? 'Edit' : 'Add' }} {{ ucfirst($type) }} Account</h4>

              <form method="POST" action="{{ $isEdit ? route('accounts.update', $account->id) : route('accounts.store') }}">
                  @csrf
                  @if ($isEdit)
                      @method('PUT')
                  @endif

                  <input type="hidden" name="type" value="{{ $type }}">

                  <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="mb-1">Title</label>
                        <input type="text" name="title" class="form-control" required value="{{ old('title', $account->title ?? '') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="mb-1">Opening Balance</label>
                        <input type="number" name="opening_balance" min="0" oninput="this.value = this.value.replace(/[^0-9]/g, '')" step="0.01" class="form-control" required
                              value="{{ old('opening_balance', $account->opening_balance ?? '') }}">
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="mb-1">Country</label>
                        <select name="country_id" id="country" class="form-control text-dark" required>
                            <option value="">Select Country</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}"
                                    data-currency="{{ $country->currency_type }}"
                                    {{ old('country_id', $account->country_id ?? '') == $country->id ? 'selected' : '' }}>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="mb-1">Currency Type</label>
                          
                        <input type="text" id="currency_display" class="form-control" 
                            value="{{ old('currency_type_display', $account->country->currency_type ?? '') }}" readonly>

                        <input type="hidden" name="currency_type" id="currency_type"
                            value="{{ old('currency_type', $account->currency_type ?? '') }}">
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="mb-1">Assign To</label>
                        <select name="assigned_to" id="assigned_to" class="form-control text-dark">
                            <option value="">Select Manager</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}"
                                    {{ old('assigned_to', $account->assigned_to ?? '') == $manager->id ? 'selected' : '' }}>
                                    {{ $manager->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                  </div>

                  <button class="btn btn-success">{{ $isEdit ? 'Update' : 'Create' }}</button>
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

<script>
    const countrySelect = document.getElementById('country');
    const currencyDisplay = document.getElementById('currency_display');
    const managerSelect = document.getElementById('assigned_to');

    countrySelect.addEventListener('change', async function () {
      const countryId = this.value;
      const currency = this.options[this.selectedIndex].dataset.currency;
      const preselectedManager = @json(old('assigned_to', $account->assigned_to ?? ''));
      currencyDisplay.value = currency;

      
      if (countryId) {
        const managerRouteTemplate = `/get-managers/${countryId}`;
        const managerResponse = await fetch(managerRouteTemplate);
        const managerData = await managerResponse.json();

        managerSelect.innerHTML = '<option value="">Select Manager</option>';
        managerData.forEach(manager => {
            let selected = (manager.id == preselectedManager) ? 'selected' : '';
            managerSelect.innerHTML += `<option value="${manager.id}" ${selected}>${manager.name}</option>`;
        });
      }else {
        managerSelect.innerHTML = '<option value="">Select Manager</option>';
      }
  });
  
  window.addEventListener('DOMContentLoaded', () => {
    if ("{{ $account->assigned_to ?? '' }}") {
        countrySelect.dispatchEvent(new Event('change'));
    }
  });
  
</script>