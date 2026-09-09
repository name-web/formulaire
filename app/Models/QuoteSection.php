<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteSection extends Model
{
    public const TYPE_TEXT = 'text';

    public const TYPE_BUDGET = 'budget';

    public const TYPE_LIST = 'list';

    public const TYPE_TABLE = 'table';

    public const TYPE_PRODUCT_TABLE = 'product_table';

    public const TYPE_CUSTOM_FIELDS = 'custom_fields';

    protected $fillable = [
        'quote_id',
        'type',
        'title',
        'content',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
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
        return config('quote_section_types.types', [
            self::TYPE_TEXT => 'Texte',
            self::TYPE_BUDGET => 'Lignes du devis (budget)',
            self::TYPE_LIST => 'Liste',
            self::TYPE_TABLE => 'Tableau',
            self::TYPE_PRODUCT_TABLE => 'Tableau de produits',
            self::TYPE_CUSTOM_FIELDS => 'Informations personnalisées',
        ]);
    }
}
