{{-- @if($processedExpenses->isEmpty())
  <tr>
      <td colspan="9">
          <div class="alert alert-warning text-center shadow-sm rounded m-0">
              <i class="mdi mdi-alert-circle-outline"></i>
              No office expenses found.
          </div>
      </td>
  </tr>
@else --}}
  @foreach($processedExpenses as $key => $expense)
    <tr>
      <td>{{ $key+1 }}</td>
      <td>{{ $expense->country->name ?? 'N/A' }}</td>
      {{-- <td>{{ $expense->yard }}</td> --}}
      <td>{{ $expense->expense_name }}</td>
      <td>{{ $expense->account->title ?? 'N/A' }}</td>
      <td>{{ number_format($expense->amount, 2) }}</td>
      <td>
        ${{ number_format($expense->usd_amount, 2) }}
        <small class="text-muted d-block">
          ({{ number_format($expense->amount, 2) }} {{ $expense->currency }} ÷ {{ number_format($expense->currencyInfo->currency_rate ?? 1, 2) }})
        </small>
      </td>
      <td>{{ $expense->creator->name ?? 'Unknown' }}</td>
      <td>{{ $expense->created_at->format('d-m-Y') }}</td>
      <td>
        <button class="btn btn-sm btn-warning edit-btn" data-id="{{ $expense->id }}">Edit</button>
        <button class="btn btn-sm btn-danger delete-btn" data-id="{{ $expense->id }}">Delete</button>
      </td>
    </tr>
  @endforeach
{{-- @endif --}}
