<?php

namespace App\Rules;

use App\Models\PmksCategory;
use App\Models\Resident;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PmksAgeRule implements ValidationRule
{
    private const AGE_RULES = [
        'PMKS-01' => ['min' => 0,  'max' => 5,   'label' => '0-5 tahun'],
        'PMKS-02' => ['min' => 6,  'max' => 18,  'label' => '6-18 tahun'],
        'PMKS-03' => ['min' => 6,  'max' => 18,  'label' => '6-18 tahun'],
        'PMKS-04' => ['min' => 6,  'max' => 18,  'label' => '6-18 tahun'],
        'PMKS-05' => ['min' => 6,  'max' => 18,  'label' => '6-18 tahun'],
        'PMKS-06' => ['min' => 6,  'max' => 18,  'label' => '6-18 tahun'],
        'PMKS-07' => ['min' => 6,  'max' => 18,  'label' => '6-18 tahun'],
        'PMKS-08' => ['min' => 60, 'max' => 999, 'label' => '60 tahun ke atas'],
    ];

    private const GENDER_RULES = [
        'PMKS-23' => ['gender' => 'P', 'label' => 'Perempuan'],
    ];

    public function __construct(
        private readonly ?int $residentId,
        private readonly ?int $categoryId,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->residentId || !$this->categoryId) return;

        $resident = Resident::find($this->residentId);
        $category = PmksCategory::find($this->categoryId);

        if (!$resident || !$category) return;

        // Validasi usia
        $ageRule = self::AGE_RULES[$category->code] ?? null;
        if ($ageRule) {
            $age = $resident->birth_date->age;
            if ($age < $ageRule['min'] || $age > $ageRule['max']) {
                $fail("Kategori {$category->name} hanya untuk penduduk usia {$ageRule['label']}. Penduduk ini berusia {$age} tahun.");
                return;
            }
        }

        // Validasi gender
        $genderRule = self::GENDER_RULES[$category->code] ?? null;
        if ($genderRule) {
            if ($resident->gender !== $genderRule['gender']) {
                $genderLabel = $resident->gender === 'L' ? 'Laki-laki' : 'Perempuan';
                $fail("Kategori {$category->name} hanya untuk {$genderRule['label']}. Penduduk ini berjenis kelamin {$genderLabel}.");
            }
        }
    }

    public static function getAgeRulesForCategory(string $categoryCode): ?array
    {
        return self::AGE_RULES[$categoryCode] ?? null;
    }

    public static function getGenderRuleForCategory(string $categoryCode): ?array
    {
        return self::GENDER_RULES[$categoryCode] ?? null;
    }

    public static function getRulesForCategory(string $categoryCode): ?array
    {
        return self::AGE_RULES[$categoryCode] ?? null;
    }
}
