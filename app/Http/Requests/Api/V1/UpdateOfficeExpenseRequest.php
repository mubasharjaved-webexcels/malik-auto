<?php
// app/Http/Requests/OfficeExpense/UpdateOfficeExpenseRequest.php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateOfficeExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = auth()->user();
        $isManager = $user->hasRole('manager');

        return [
            'country_id' => $isManager ? 'sometimes|exists:countries,id' : 'required|exists:countries,id',
            'assigned_manager_id' => $isManager ? 'sometimes|exists:users,id' : 'nullable|exists:users,id',
            'expense_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|max:10',
            'account_id' => 'nullable|exists:bank_cash_accounts,id',
        ];
    }

    public function messages(): array
    {
        return [
            'country_id.required' => 'The country field is required.',
            'country_id.exists' => 'The selected country is invalid.',
            'assigned_manager_id.exists' => 'The selected manager is invalid.',
            'expense_name.required' => 'The expense name is required.',
            'expense_name.max' => 'The expense name must not exceed 255 characters.',
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a number.',
            'amount.min' => 'The amount must be at least 0.01.',
            'currency.required' => 'The currency is required.',
            'currency.max' => 'The currency must not exceed 10 characters.',
            'account_id.exists' => 'The selected account is invalid.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
