<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $table = 'company_settings';

    protected $fillable = [
        'name',
        'description',
        'logo_path',
        'phone',
        'email',
        'address',
        'city',
        'country',
        'footer',
        'legal_info',
        'currency',
        'primary_color',
        'secondary_color',
        'commission_threshold',
        'commission_percent',
        'commission_flat',
    ];

    /**
     * Récupère les paramètres de l'entreprise en les créant avec des valeurs
     * par défaut issus de la configuration si absents.
     */
    public static function get(): self
    {
        $company = static::query()->first() ?? static::create([
            'name' => config('admin_brand.app_name'),
            'description' => config('quote.company_default.description'),
            'city' => config('quote.company_default.city'),
            'country' => config('quote.company_default.country'),
            'footer' => config('quote.company_default.footer'),
            'currency' => config('quote.currency'),
            'logo_path' => config('admin_brand.logo'),
            'primary_color' => '#F5A623',
            'secondary_color' => '#1a1a1a',
            'commission_threshold' => 100000,
            'commission_percent' => 20,
            'commission_flat' => 20000,
        ]);

        // Compatibilité des lignes créées avant l'ajout de logo_path.
        if (blank($company->logo_path) && filled(config('admin_brand.logo'))) {
            $company->forceFill(['logo_path' => config('admin_brand.logo')])->save();
        }

        return $company;
    }

    /**
     * Génère le texte de la note de commission selon les règles configurées.
     */
    public function commissionNote(int $amount = 0): string
    {
        $threshold = $this->commission_threshold;
        $percent = $this->commission_percent;
        $flat = $this->commission_flat;

        return "Note : le taux de commission {$this->name} appliqué est de {$percent}% du budget "
            .'pour tout montant à partir de '.number_format($threshold, 0, ',', ' ')." {$this->currency}, "
            .'ou de '.number_format($flat, 0, ',', ' ')." {$this->currency} forfaitaires en dessous de ce seuil"
            .($amount > 0 ? ' — ce qui correspond au présent budget de '.number_format($amount, 0, ',', ' ')." {$this->currency}." : '.');
    }
}
