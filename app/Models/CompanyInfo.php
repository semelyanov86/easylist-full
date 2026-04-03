<?php

declare(strict_types=1);

namespace App\Models;

use App\Data\CompanyInfoDetailsData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\CompanyInfoFactory;

class CompanyInfo extends Model
{
    /** @use HasFactory<CompanyInfoFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'city',
        'info',
    ];

    /** @return array<string, string> */
    #[\Override]
    protected function casts(): array
    {
        return [
            'info' => CompanyInfoDetailsData::class,
        ];
    }
}
