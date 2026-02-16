<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'type' => 'required|string|in:deposit',
      'destination' => 'required|integer',
      'amount' => 'required|numeric|min:0.01',
    ];
  }
}
