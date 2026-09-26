<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_OPERATOR = 'operator';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'email',
        'password',
        'last_seen_version',
        'role',
        'cnes',
        'facility_name',
    ];

    /** @var list<string> */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isOperator(): bool
    {
        return $this->role === self::ROLE_OPERATOR;
    }

    public function assignedCnes(): ?string
    {
        return $this->isOperator() ? $this->cnes : null;
    }

    /**
     * Retorna a relação de estabelecimentos de saúde (UBS) municipais disponíveis.
     *
     * @return Collection<int, object{cnes: string, facility_name: string|null}>
     */
    public static function availableFacilities(): Collection
    {
        $units = collect();

        if (Schema::hasTable('cvat_team_evaluations')) {
            $units = CvatTeamEvaluation::query()
                ->whereNotNull('cnes')
                ->where('cnes', '!=', '')
                ->select(['cnes', 'facility_name'])
                ->distinct()
                ->orderBy('facility_name')
                ->get();
        }

        if ($units->isEmpty() && Schema::hasTable('cvat_nominal_citizens')) {
            $units = CvatNominalCitizen::query()
                ->whereNotNull('cnes')
                ->where('cnes', '!=', '')
                ->select(['cnes', 'facility_name'])
                ->distinct()
                ->orderBy('facility_name')
                ->get();
        }

        return $units;
    }
}
