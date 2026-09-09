<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteCustomField extends Model
{
    public const TYPE_TEXT = 'text';

    public const TYPE_NUMBER = 'number';

    public const TYPE_AMOUNT = 'amount';

    public const TYPE_DATE = 'date';

    public const TYPE_TEXTAREA = 'textarea';

    protected $fillable = [
        'quote_id',
        'name',
        'type',
        'value',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /** @return array<string, string> */
    public static function getTypes(): array
    {
        return config('quote_field_types.types', [
            self::TYPE_TEXT => 'Texte',
            self::TYPE_NUMBER => 'Nombre',
            self::TYPE_AMOUNT => 'Montant',
            self::TYPE_DATE => 'Date',
            self::TYPE_TEXTAREA => 'Texte long',
        ]);
    }
}
