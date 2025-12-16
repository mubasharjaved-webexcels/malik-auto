@if($carExpenses->isEmpty())
  <tr>
      <td colspan="8">
          <div class="alert alert-warning text-center shadow-sm rounded m-0">
              <i class="mdi mdi-alert-circle-outline"></i>
              No car expenses found.
          </div>
      </td>
  </tr>
@else
  @foreach($carExpenses as $expense)
  <tr>
    <td style="padding-top: 0px;padding-bottom: 0px;">
      {{-- <div class="d-flex align-items-center"> --}}
        @if($expense->carProfile && $expense->carProfile->car_image)
          <img src="{{ asset('storage/' . $expense->carProfile->car_image) }}" style="width: 40px; height: 30px; margin-left:30px;" class="me-2">
        @else
          <img src="{{ asset('images/no-image.png') }}" style="width: 40px; height: 30px;" class="me-2">
        @endif
        <span>{{ $expense->carProfile->rec_no ?? 'N/A' }}</span>
      {{-- </div> --}}
    </td>
    <td>{{ $expense->expenses_for }}</td>
    <td>{{ $expense->account->title ?? 'N/A' }}</td>
    <td>{{ number_format($expense->amount, 2) }}</td>
    <td>{{ $expense->currency }}</td>
    <td>
      ${{ number_format($expense->usd_amount, 2) }}
      <small class="text-muted d-block">
        ({{ number_format($expense->amount, 2) }} {{ $expense->currency }} ÷ {{ number_format($expense->currencyInfo->currency_rate ?? 1, 2) }})
      </small>
    </td>
    <td>{{ $expense->creator->name ?? 'N/A' }}</td>
    <td>{{ $expense->created_at->format('Y-m-d H:i') }}</td>
    <td>...</td>
  </tr>
  @endforeach
@endif