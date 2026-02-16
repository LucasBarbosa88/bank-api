<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'type' => 'required|string|in:transfer',
      'origin' => 'required|integer',
      'destination' => 'required|integer|different:origin',
      'amount' => 'required|numeric|min:0.01',
    ];
  }
}
