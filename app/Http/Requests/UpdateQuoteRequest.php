<?php

namespace App\Http\Requests;

class UpdateQuoteRequest extends StoreQuoteRequest
{
    public function rules(): array
    {
        return parent::rules();
    }
}
