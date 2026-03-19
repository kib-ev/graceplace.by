<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'sort', 'keywords'];

    protected $casts = [
        'keywords' => 'array',
    ];

    /**
     * Find category IDs that match the given text (e.g. master description).
     */
    public static function getRecommendedIdsForText(?string $text): array
    {
        if (empty($text)) {
            return [];
        }

        $lower = mb_strtolower($text);
        $categories = self::whereNotNull('keywords')->get();

        return $categories
            ->filter(fn (self $cat) => ! empty($cat->keywords) && collect($cat->keywords)->contains(fn ($kw) => self::keywordMatchesText($kw, $lower)))
            ->pluck('id')
            ->values()
            ->toArray();
    }

    /**
     * Check if keyword matches text. Supports * as wildcard for any characters.
     * E.g. "реконструкц* волос" matches "мастер по реконструкции волос".
     */
    public static function keywordMatchesText(string $keyword, string $text): bool
    {
        $keyword = mb_strtolower(trim($keyword));
        if ($keyword === '') {
            return false;
        }

        if (! str_contains($keyword, '*')) {
            return str_contains($text, $keyword);
        }

        $parts = explode('*', $keyword);
        $pattern = implode('.*', array_map(fn (string $p) => preg_quote($p, '~'), $parts));

        return (bool) preg_match('~' . $pattern . '~u', $text);
    }

    public function masters()
    {
        return $this->belongsToMany(Master::class, 'master_service_category')
            ->withTimestamps();
    }
}
