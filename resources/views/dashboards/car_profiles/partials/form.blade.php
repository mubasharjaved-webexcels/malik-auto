<div class="row">
    <!-- Country -->
    <div class="col-md-6 mb-3">
        <label for="country_id" class="form-label">Country</label>
        <div class="position-relative">
            <i class="bi bi-chevron-down position-absolute text-muted" style="right: 15px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
            <select name="country_id" id="country_id" class="form-select text-dark" required>
                @role('admin')
                    @if(isset($carProfile))
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}"
                                {{ old('country_id', $carProfile->country_id) == $country->id ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    @else
                        @php
                            $defaultCountry = $countries->firstWhere('id', 1);
                        @endphp
                        <option value="{{ $defaultCountry->id }}" selected>{{ $defaultCountry->name }}</option>
                    @endif
                @endrole

                @role('manager')
                    @foreach ($countries as $country)
                        <option value="{{ $country->id }}"
                            {{ old('country_id', $carProfile->country_id ?? $country->id) == $country->id ? 'selected' : '' }}>
                            {{ $country->name }}
                        </option>
                    @endforeach
                @endrole
            </select>
        </div>
    </div>
    
    <!-- Car Status -->
    <div class="col-md-6 mb-3">
        <label for="car_status" class="form-label">Car Status</label>
        <div class="position-relative">
            <i class="bi bi-chevron-down position-absolute text-muted" style="right: 15px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
            <select name="car_status" id="car_status" class="form-select text-dark" required>
                <option value="">Select Status</option>
                    @php
                        $statuses = [
                            'new_arrival' => 'New Arrival',
                            'ready_for_sale' => 'Ready For Sale',
                            'sold' => 'Sold',
                            'in_transit' => 'In Transit',
                            'under_maintenance' => 'Under Maintenance',
                            'pending_sold' => 'Pending Sold',
                            'rejected' => 'Rejected',
                            'booked' => 'Booked',
                        ];
                        $selectedStatus = old('car_status', $carProfile->car_status ?? 'new_arrival');
                    @endphp
                    @foreach ($statuses as $value => $label)
                    {{-- @continue($value === 'pending_sold') --}}
                        @if ($value === 'ready_for_sale' && auth()->user()->hasRole('manager'))
                            <option value="{{ $value }}" {{ $selectedStatus == $value ? 'selected' : '' }}>
                                Available For Sale
                            </option>
                        @else
                            <option value="{{ $value }}" {{ $selectedStatus == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endif
                    @endforeach
            </select>
        </div>
    </div>

    @php $status = old('car_status', $carProfile->car_status ?? null); @endphp

    @php $localCurrency = $currencyType ?? 'JPY';  @endphp

    {{-- SALE PRICE (Admins Only) --}}
    @unlessrole('manager')
    <div id="sale-price-wrapper" class="col-md-6 mb-3 {{ $status === 'ready_for_sale' ? '' : 'd-none' }}">
        <label for="sale_price_usd" class="form-label">Sale Price</label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input
                type="number"
                min="0"
                step="0.01"
                oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                name="sale_price"
                id="sale_price_usd"
                value="{{ old('sale_price', $carProfile->sale_price ?? '') }}"
                class="form-control @error('sale_price') is-invalid @enderror"
            >

            <span class="input-group-text">{{ $localCurrency }}</span>
            <input
                type="number"
                min="0"
                step="0.01"
                oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                id="sale_price_local"
                class="form-control"
            >
        </div>
        @error('sale_price')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div id="booking-price-wrapper" class="col-md-6 mb-3 {{ $status === 'ready_for_sale' ? '' : 'd-none' }}">
        <label for="booking_price_usd" class="form-label">Booking Price</label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input
                type="number"
                min="0"
                step="0.01"
                oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                name="booking_price"
                id="booking_price_usd"
                value="{{ old('booking_price', $carProfile->booking_price ?? '') }}"
                class="form-control @error('booking_price') is-invalid @enderror"
            >

            <span class="input-group-text">{{ $localCurrency }}</span>
            <input
                type="number"
                min="0"
                step="0.01"
                oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                id="booking_price_local"
                class="form-control"
            >
        </div>
        @error('booking_price')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    @endunlessrole

    {{-- SOLD PRICE (Admins + Managers) conditionally shown --}}
    <div id="sold-price-wrapper" class="col-md-6 mb-3 {{ $status === 'sold' ? '' : 'd-none' }}">
        <label for="sold_price_usd" class="form-label">Sold Price</label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input
                type="number"
                min="0"
                step="0.01"
                oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                name="sold_price"
                id="sold_price_usd"
                value="{{ old('sold_price', $carProfile->sold_price ?? '') }}"
                class="form-control @error('sold_price') is-invalid @enderror"
                @role('manager') required @endrole
            >

            <span class="input-group-text">{{ $localCurrency }}</span>
            <input
                type="number"
                min="0"
                step="0.01"
                oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                id="sold_price_local"
                class="form-control"
            >
        </div>
        @error('sold_price')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    @unlessrole('admin')
    <div id="booking-price-wrapper" class="col-md-6 mb-3 {{ $status === 'booked' ? '' : 'd-none' }}">
        <label for="booking_price_usd" class="form-label">Booking Price</label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input
                type="number"
                name="booking_price"
                id="booking_price_usd"
                value="{{ old('booking_price', $carProfile->booking_price ?? '') }}"
                class="form-control @error('booking_price') is-invalid @enderror" disabled
            >

            <span class="input-group-text">{{ $localCurrency }}</span>
            <input
                type="number"
                id="booking_price_local"
                class="form-control" disabled
            >
        </div>
        @error('booking_price')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    @endunlessrole
    @unlessrole('manager')
    <div id="receive-account-wrapper" class="col-md-6 mb-3 d-none">
        <label for="account_id" class="form-label">Receive in Account</label>
        <div class="position-relative">
            <i class="bi bi-chevron-down position-absolute text-muted" style="right: 15px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
            <select name="account_id" id="account_id" class="form-select text-dark">
                <option value="">Select Account</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}" {{ old('account_id', $carProfile->account_id ?? '') == $account->id ? 'selected' : '' }}>
                        [{{ ucfirst($account->type) }}] {{ $account->title }} ({{ $account->country->currency_type }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    @endunlessrole

    <!-- Confirmation Flags -->
    <input type="hidden" name="confirm_clear_sale" id="confirm_clear_sale" value="0">
    <input type="hidden" name="confirm_clear_both" id="confirm_clear_both" value="0">

    <div class="col-md-6 mb-3">
        <label for="located_yard" class="form-label">Located Yard</label>
        <div class="position-relative">
            <i class="bi bi-chevron-down position-absolute text-muted" style="right: 15px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
            <select name="located_yard" id="located_yard" class="form-select text-dark" required>
                <option value="">Select Yard</option>
            </select>
        </div>
        {{-- @unlessrole('manager') --}}
        <div id="add-yard-wrapper" class="mt-2 d-none">
            <input type="text" id="new_yard" name="new_yard" class="form-control mt-2" placeholder="Enter New Yard">
        </div>
        {{-- @endunlessrole --}}
    </div>

    <!-- Car name -->
    <div class="col-md-6 mb-3">
        <label for="car_name" class="form-label">Car name</label>
        <input type="text" name="car_name" class="form-control" value="{{ old('car_name', $carProfile->car_name ?? '') }}">
    </div>

    <!-- Grade -->
    <div class="col-md-6 mb-3">
        <label for="grade" class="form-label">Grade</label>
        <input type="text" name="grade" class="form-control" value="{{ old('grade', $carProfile->grade ?? '') }}" required>
    </div>

    <!-- Seats -->
    <div class="col-md-6 mb-3">
        <label for="seats" class="form-label">Seats</label>
        <input type="number" name="seats" class="form-control" value="{{ old('seats', $carProfile->seats ?? '') }}" min="0" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
    </div>

    <!-- Chassis -->
    <div class="col-md-6 mb-3">
        <label for="chassis" class="form-label">Chassis</label>
        <input type="text" name="chassis" id="chassis" data-id="{{ $carProfile->id ?? '' }}" class="form-control" value="{{ old('chassis', $carProfile->chassis ?? '') }}" required>
        <small id="chassis-error" class="text-danger"></small>
    </div>

    <!-- Shift -->
    <div class="col-md-6 mb-3">
        <label for="shift" class="form-label">Shift</label>
        <input type="text" name="shift" class="form-control" value="{{ old('shift', $carProfile->shift ?? '') }}" required>
    </div>

    <!-- Mileage -->
    <div class="col-md-6 mb-3">
        <label for="mileage" class="form-label">Mileage</label>
        <input type="number" name="mileage" class="form-control" value="{{ old('mileage', $carProfile->mileage ?? '') }}" min="0" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
    </div>

    <!-- Engine CC -->
    <div class="col-md-6 mb-3">
        <label for="engine_cc" class="form-label">Engine CC</label>
        <input type="number" name="engine_cc" class="form-control" value="{{ old('engine_cc', $carProfile->engine_cc ?? '') }}" min="0" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
    </div>

    <!-- Dimension -->
    <div class="col-md-6 mb-3">
        <label for="dimension" class="form-label">Model Year</label>
        <input type="text" name="dimension" class="form-control" value="{{ old('dimension', $carProfile->dimension ?? '') }}" >
    </div>

    <!-- M3 -->
    {{-- <div class="col-md-6 mb-3">
        <label for="m3" class="form-label">M3</label>
        <input type="text" name="m3" class="form-control" value="{{ old('m3', $carProfile->m3 ?? '') }}" >
    </div> --}}
    
    @unlessrole('manager')
    <!-- Price -->
    <div class="col-md-6 mb-3">
        <label for="price_usd" class="form-label">Price</label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" min="0" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')" step="0.01" name="price" id="price_usd"
                class="form-control" value="{{ old('price', $carProfile->price ?? '') }}" required>

            <span class="input-group-text">(JPY)</span>
            <input type="number" min="0" oninput="this.value = this.value.replace(/[^0-9]/g, '')" step="0.01" id="price_jpy"
                class="form-control" value="" required>
        </div>
    </div>
    @endunlessrole

    <!-- Fuel -->
    <div class="col-md-6 mb-3">
        <label for="fuel" class="form-label">Fuel Type</label>
        <input type="text" oninput="this.value = this.value.replace(/[^a-zA-Z]/g, '')" name="fuel" class="form-control" value="{{ old('fuel', $carProfile->fuel ?? '') }}" required>
    </div>

    <!-- Max Loading -->
    <div class="col-md-6 mb-3">
        <label for="max_loading" class="form-label">Max Loading</label>
        <input type="text" name="max_loading" class="form-control" value="{{ old('max_loading', $carProfile->max_loading ?? '') }}" required>
    </div>

    <!-- Transit Expense -->
    {{-- <div class="col-md-6 mb-3">
        <label for="transit_expense" class="form-label">Transit Expense</label>
        <input type="number" name="transit_expense" class="form-control" value="{{ old('transit_expense', $carProfile->transit_expense ?? '') }}" step="0.01">
    </div>

    <div class="col-md-6 mb-3">
        <label for="currency_type" class="form-label">Currency Type</label>
        <select name="currency_type" id="currency_type" class="form-select text-dark">
            <option value="">Select Currency</option>
            @foreach ($countries as $currency)
                <option value="{{ $currency->currency_type }}" {{ (old('currency_type', $carProfile->currency_type ?? '') == $currency->currency_type) ? 'selected' : '' }}>
                    {{ $currency->currency_type }}
                </option>
            @endforeach
        </select>
    </div> --}}

    <!-- Car Image -->
    <div class="row mb-3">
        <div class="col-md-3 mb-3">
            <label for="car_image" class="form-label">Car Image</label>
            <input type="file" name="car_image" class="form-control" accept=".jpg,.jpeg,.png" @if(!isset($carProfile)) required @endif>
            <small class="text-muted" style="font-size: 12px;">Accepted formats: .jpg, .jpeg, .png (Max: 2MB)</small>
            @if(!empty($carProfile->car_image))
                <img src="{{ asset('storage/'.$carProfile->car_image) }}" alt="Car Image" class="mt-2" style="max-height: 100px;">
            @endif
        </div>
        <div class="col-md-3 mb-3">
            <label for="car_gallery" class="form-label">Car Gallery</label>
            <input 
                type="file" 
                name="car_gallery[]" 
                class="form-control" 
                accept=".jpg,.jpeg,.png" 
                multiple 
                @if(!isset($carProfile)) required @endif
            >
            <small class="text-muted" style="font-size: 12px;">
                Accepted formats: .jpg, .jpeg, .png (Max: 2MB each)
            </small>
        
            @if(isset($carProfile) && $carProfile->galleryImages)
                <div class="mt-2 d-flex flex-wrap gap-2">
                    @foreach($carProfile->galleryImages as $image)
                        <img src="{{ asset('storage/'.$image->path) }}" alt="Car Image" style="max-height: 80px;">
                    @endforeach
                </div>
            @endif
        </div>


        <!-- Car Video -->
        <div class="col-md-6 mb-3">
            <label for="car_video" class="form-label">Car Video</label>
            <input type="file" name="car_video" class="form-control" @if(!isset($carProfile)) @endif>
            @if(!empty($carProfile->car_video))
                <video width="200" height="150" controls class="mt-2">
                    <source src="{{ asset('storage/'.$carProfile->car_video) }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            @endif
        </div>
    </div>
</div>

<div class="mt-3 d-flex">
    <button type="submit" class="btn btn-primary">
        {{ isset($carProfile) ? 'Update' : 'Add' }} Car
    </button>

    <a href="{{ route('car-profiles.index') }}" class="btn btn-secondary ms-2">
        Back to List
    </a>
</div>
