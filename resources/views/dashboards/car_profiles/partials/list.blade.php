@if($carProfiles->isEmpty() && collect(request()->all())->filter()->isNotEmpty())
    <div class="alert alert-warning text-center shadow-sm rounded mt-4">
        <i class="mdi mdi-alert-circle-outline"></i>
        No car profiles found.
    </div>
@else
@foreach($carProfiles as $car)
  <div class="card mb-3 shadow position-relative">
      @unlessrole('manager|salesperson')
          <div class="position-absolute top-0 end-0 text-dark fw-bold px-3 py-2" style="z-index: 10;">
              <span class="text-muted" style="font-size: 12px; cursor: pointer !important;"
                data-bs-toggle="tooltip" 
                data-bs-placement="top" 
                data-bs-html="true"
                title="Current value = Bid price + Current expense <br> Not saleable price."
                style="cursor: pointer;">
                <i class="mdi mdi-information" style="font-size:15px;"></i>
            </span>Auction price: ${{ number_format($car->price + ($car->expenses_sum_usd_amount ?? 0), 2) }}
          </div>
          
          @if($car->sale_price)
            <div class="position-absolute top-10 end-0 text-danger fw-bold px-3 py-1" style="z-index: 10; top:12%;">
              <small class="text-muted" style="font-size:13px;">
                Sale price: ${{ number_format($car->sale_price, 2) }}
              </small>
            </div>
          @endif

          @if($car->sold_price)
            <div class="position-absolute end-0 text-dark fw-bold px-3 py-1" style="z-index: 10; top:20%;">
              <small class="text-green-800" style="font-size:13px;">
                Sold price: ${{ number_format($car->sold_price, 2) }}
              </small>
            </div>
          @endif
          
          @if($car->car_status == 'pending_sold')
            <div class="position-absolute top-10 end-0 text-dark fw-bold px-3 py-2" style="z-index: 10; top:20%;">
              <small class="text-warning" style="font-size:13px;"> Sold price<span style="font-size: 11px;">(pending)</span>: ${{ number_format($car->suggested_sold_price, 2) }} </small>
            </div>
          @endif

          @if($car->car_status == 'rejected')
          <div class="position-absolute top-10 end-0 text-dark fw-bold px-3 py-2" style="z-index: 10; top:20%;">
            <small class="text-red-600 text-decoration-line-through" style="font-size:13px;">Suggested Sold price: ${{ number_format($car->suggested_sold_price, 2) }} </small>
          </div>
          @endif

      @endunlessrole
      
      @unlessrole('admin')
          @if($car->car_status != 'sold')
            <div class="position-absolute top-0 end-0 text-dark fw-bold px-3 py-2" style="z-index: 10;">
              @if($car->sale_price)
                  <small class="text-muted">Sale price:</small> 
                  ${{ number_format($car->sale_price, 2) }} 
                  <small class="text-muted">
                      ({{ $car->country->currency_type }}{{ number_format($car->sale_price * $car->country->currency_rate, 2) }})
                  </small>
              @endif
            </div>
          @endif

          @if($car->car_status == 'rejected')
            <div class="position-absolute top-10 end-0 text-dark fw-bold px-3 py-2" style="z-index: 10; top:12%;">
              <small class="text-red-600 text-decoration-line-through" style="font-size:13px;"> Sold price: ${{ number_format($car->suggested_sold_price, 2) }}
                  ({{ $car->country->currency_type }}{{ number_format($car->suggested_sold_price * $car->country->currency_rate, 2) }})
              </small>
            </div>
          @endif

          @if($car->car_status == 'pending_sold')
            <div class="position-absolute top-10 end-0 text-dark fw-bold px-3 py-2" style="z-index: 10; top:12%;">
              <small class="text-warning" style="font-size:13px;"> Sold price: ${{ number_format($car->suggested_sold_price, 2) }} </small>
              <small class="text-muted" style="font-size:13px;">
                  ({{ $car->country->currency_type }}{{ number_format($car->suggested_sold_price * $car->country->currency_rate, 2) }})
              </small>
            </div>
          @endif
          @if($car->car_status != 'sold')
            @php
                $top = in_array($car->car_status, ['pending_sold', 'rejected']) ? '22%' : '12%';
            @endphp
            @if($car->booking_price != null)
              <div class="position-absolute top-10 end-0 text-dark fw-bold px-3 py-2" style="z-index: 10; top: {{ $top }};">
                <small class="text-warning" style="font-size:13px;"> Booking price: ${{ number_format($car->booking_price, 2) }}</small>
                  <small class="text-muted" style="font-size:13px;">
                      ({{ $car->country->currency_type }}{{ number_format($car->booking_price * $car->country->currency_rate, 2) }})
                  </small>
              </div>
            @endif
          @endif
      @endunlessrole
      <div class="row g-0 align-items-center">
          {{-- Image --}}
          <div class="col-md-3 position-relative">
              @if($car->is_featured)
                  <span class="badge bg-danger position-absolute top-0 start-0 m-2 opacity-50">FEATURED</span>
              @endif
              <img src="{{ asset('storage/' . $car->car_image) }}" class="img-fluid rounded-start" alt="{{ $car->rec_no }}">
          </div>
          <div class="col-md-6">
              <div class="card-body pb-3">
                  <h5 class="card-title fw-bold mb-2">{{ $car->rec_no }}</h5>
                  <ul class="list-inline text-muted small mb-2">
                      <li class="list-inline-item"><strong>Country:</strong> {{ $car->country->name ?? 'N/A' }}</li>
                      <li class="list-inline-item"><strong>Car Name:</strong> {{ $car->car_name ?? 'N/A' }}</li>
                      <li class="list-inline-item"><strong>Yard:</strong> {{ $car->assignedManager->name ?? 'N/A' }}</li>
                      <li class="list-inline-item"><strong>Mileage:</strong> {{ number_format($car->mileage) }} km</li>
                      <li class="list-inline-item"><strong>Engine:</strong> {{ $car->engine_cc }} cc</li>
                      <li class="list-inline-item"><strong>Fuel:</strong> {{ ucfirst($car->fuel) }}</li>
                      <li class="list-inline-item"><strong>Chassis:</strong> {{ ucfirst($car->chassis) }}</li>
                      <li class="list-inline-item"><strong>Model Year:</strong> {{ ucfirst($car->dimension) }}</li>
                  </ul>
                  <p class="text-muted small mb-0">Updated {{ $car->updated_at->diffForHumans() }}</p>
              </div>
              <div class="d-flex justify-content-between align-items-center" style="margin-left: 24px;">
                <div class="text-center">
                  <h6 class="fw-bold mb-1">Current Status</h6>
                  {{-- <button class="btn btn-success text-white mx-0" style="cursor: default">{{ ucwords(str_replace('_', ' ', $car->car_status)) }}</button> --}}
                  <button class="btn text-white mx-0 {{ $car->car_status === 'booked' ? 'btn-warning' : 'btn-success' }}" style="cursor: default">
                      {{ $car->car_status === 'booked'  && $car->is_booked == 0 ? 'Booking Pending' : ucwords(str_replace('_', ' ', $car->car_status)) }}
                  </button>
                </div>

                @unlessrole('manager')
                <div class="text-center">
                  <h6 class="fw-bold mb-1">Current Expenses</h6>
                  <button class="btn btn-success text-white mx-0" style="cursor: default">${{ number_format(($car->expenses_sum_usd_amount ?? 0), 2) }}</button>
                </div>
                @endunlessrole

                <div class="text-center">
                  <h6 class="fw-bold mb-1">Sold Price Request</h6>
                  <button class="btn {{ $car->car_status == 'pending_sold' ? 'btn-success' : ($car->car_status == 'rejected' ? 'btn-danger' : 'bg-success') }} text-white mx-0" style="cursor: default">
                    {{ 
                        in_array($car->car_status, ['sold', 'rejected', 'pending_sold']) 
                        ? (
                            $car->car_status == 'sold' ? 'Approved' :
                            ($car->car_status == 'pending_sold' ? 'Pending' :
                            ucwords(str_replace('_', ' ', $car->car_status)))
                        ) 
                        : 'N/A' 
                    }}
                  </button>
                </div>
              </div>
          </div>
          <div class="col-md-3 d-flex justify-content-end align-items-end pb-0 mt-auto">
            <div class="d-flex align-items-center gap-2">
                <a href="javascript:void(0)"
                   class="btn btn-primary text-white"
                   data-bs-toggle="modal"
                   data-bs-target="#galleryModal{{ $car->id }}">
                   <i class="mdi mdi-image-multiple" style="margin-left:-2px;"></i> Gallery
                </a>
                <!-- Car Gallery Modal -->
                <div class="modal fade" id="galleryModal{{ $car->id }}" tabindex="-1" aria-labelledby="galleryModalLabel{{ $car->id }}" aria-hidden="true">
                  <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="galleryModalLabel{{ $car->id }}">Gallery - {{ $car->rec_no }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        @if($car->galleryImages->isEmpty())
                            <p class="text-center text-muted">No gallery images available.</p>
                        @else
                            <div class="row g-3">
                              @foreach($car->galleryImages as $image)
                                  <div class="col-md-3">
                                    <div class="card shadow-sm border-0">
                                      <img src="{{ asset($image->path) }}" class="card-img-top img-fluid rounded" alt="Gallery Image">
                                    </div>
                                  </div>
                              @endforeach
                            </div>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>
              <div class="dropdown">
                <a href="javascript:void(0)" id="actionDropdown{{ $car->id }}" data-bs-toggle="dropdown" aria-expanded="false" class="dropdown-toggle btn btn-primary text-white">
                    <i class="mdi mdi-eye" style="margin-right: 0px;"></i>
                </a>

                <ul class="dropdown-menu" aria-labelledby="actionDropdown{{ $car->id }}" style="min-width: 8rem; font-size:14px;">
                    @role('manager')
                      @if ($car->car_status === 'sold')
                          <li>
                              <a class="dropdown-item border-bottom" href="{{ route('car-profiles.show', $car) }}">View</a>
                          </li>
                      @elseif($car->car_status === 'in_transit' || $car->car_status === 'rejected')
                          <li class="px-3 py-2">
                              <div class="alert alert-warning mb-2">
                                  {{ucwords(str_replace('_', ' ', $car->car_status))}}, Click bellow button to continue.
                              </div>
                              <form method="POST" action="{{ route('markCarReceived', $car) }}">
                                  @csrf
                                  @method('PATCH')
                                  <button type="submit" class="btn btn-sm btn-primary w-100 text-white">
                                      {{ $car->car_status == 'rejected' ? 'Confirm Rejection' : ($car->car_status == 'in_transit' ? 'Confirm Received' : '') }}
                                  </button>
                              </form>
                          </li>
                      @else
                          {{-- Manager: Normal options --}}
                          @foreach ($countries as $country)
                              <li>
                                  <a href="javascript:void(0)"
                                    class="dropdown-item transfer-country border-bottom"
                                    data-car-id="{{ $car->id }}"
                                    data-country-id="{{ $country->id }}"
                                    data-country-name="{{ $country->name }}">
                                      Transfer to {{ $country->name }}
                                  </a>
                              </li>
                          @endforeach
                          <li><a class="dropdown-item border-bottom" href="{{ route('car-profiles.edit', $car) }}">Edit</a></li>
                          <li><a class="dropdown-item border-bottom" href="{{ route('car-profiles.show', $car) }}">View</a></li>
                          <li>
                              <button type="button" class="dropdown-item text-danger w-100 text-start" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $car->id }}">
                                  Delete
                              </button>
                          </li>
                      @endif
                  @elserole('admin')
                      {{-- Admin always sees full options, regardless of car status --}}
                      @foreach ($countries as $country)
                          <li>
                              <a href="javascript:void(0)"
                                class="dropdown-item transfer-country border-bottom"
                                data-car-id="{{ $car->id }}"
                                data-country-id="{{ $country->id }}"
                                data-country-name="{{ $country->name }}">
                                  Transfer to {{ $country->name }}
                              </a>
                          </li>
                      @endforeach
                      <li><a class="dropdown-item border-bottom" href="{{ route('car-profiles.edit', $car) }}">Edit</a></li>
                      <li><a class="dropdown-item border-bottom" href="{{ route('car-profiles.show', $car) }}">View</a></li>
                      <li>
                          <button type="button" class="dropdown-item text-danger w-100 text-start" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $car->id }}">
                              Delete
                          </button>
                      </li>
                  @else
                      {{-- For salesperson and other roles: only View --}}
                      <li><a class="dropdown-item border-bottom" href="{{ route('car-profiles.show', $car) }}">View</a></li>
                  @endrole
                </ul>
              </div>
            </div>
        </div>
      </div>
    </div>
    @php
      $activeExpenses = $car->expenses->whereNull('deleted_at');
      $hasExpenses = $activeExpenses->isNotEmpty();
      $totalUsd = $activeExpenses->sum('usd_amount');
    @endphp
    <!-- Delete Confirmation Modal -->
      <div class="modal fade" id="deleteModal{{ $car->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $car->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="deleteModalLabel{{ $car->id }}">Confirm Deletion</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              @if ($hasExpenses)
              <i class="mdi mdi-alert text-danger"></i>
                Deleting <strong>{{ $car->rec_no }}</strong> will also remove <strong>${{ number_format($totalUsd, 2) }}</strong> in expenses.<br><br>
                Still want to delete?
              @else
                Are you sure you want to delete <strong>{{ $car->rec_no ?? 'this car profile' }}</strong>?
              @endif
            </div>
            <div class="modal-footer">
              {{-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button> --}}
              <form action="{{ route('car-profiles.destroy', $car->id) }}" method="POST">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger">Yes, Delete</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    <!-- Delete Confirmation Modal -->
  @endforeach
  @endif
  @if ($carProfiles->hasPages())
        <div class="mt-4 d-flex justify-content-center">
           {{ $carProfiles->appends(['per_page' => $per_page_value, 'country_id' => request('country_id'),  'assigned_manager_id'=> request('assigned_manager_id'), 'rec_no' => request('rec_no'),  'car_status'=> request('car_status') ])->links('pagination::bootstrap-5') }}
        </div>
  @endif
  
  
  
