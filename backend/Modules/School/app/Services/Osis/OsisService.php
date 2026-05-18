<?php

namespace Modules\School\Services\Osis;

use Illuminate\Support\Collection;
use Modules\School\Models\Osis\OsisFinance;
use Modules\School\Models\Osis\OsisMember;
use Modules\School\Models\Osis\OsisProgram;
use Modules\School\Models\Osis\OsisSuggestion;

class OsisService
{
    // --- Programs ---

    // --- Programs ---

    /**
     * @return Collection<int, OsisProgram>
     */
    public function getPrograms(?string $schoolId = null): Collection
    {
        /** @var Collection<int, OsisProgram> $programs */
        $programs = OsisProgram::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->latest()
            ->get();

        return $programs;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createProgram(array $data): OsisProgram
    {
        /** @var OsisProgram $program */
        $program = OsisProgram::create($data);

        return $program;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateProgram(OsisProgram $program, array $data): OsisProgram
    {
        $program->update($data);

        return $program;
    }

    public function deleteProgram(OsisProgram $program): bool
    {
        return (bool) $program->delete();
    }

    // --- Members ---

    /**
     * @return Collection<int, OsisMember>
     */
    public function getMembers(?string $schoolId = null): Collection
    {
        /** @var Collection<int, OsisMember> $members */
        $members = OsisMember::with('student')
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->latest()
            ->get();

        return $members;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function addMember(array $data): OsisMember
    {
        /** @var OsisMember $member */
        $member = OsisMember::create($data);

        return $member;
    }

    // --- Finances ---

    /**
     * @return Collection<int, OsisFinance>
     */
    public function getFinances(?string $schoolId = null): Collection
    {
        /** @var Collection<int, OsisFinance> $finances */
        $finances = OsisFinance::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->latest()
            ->get();

        return $finances;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createFinance(array $data): OsisFinance
    {
        /** @var OsisFinance $finance */
        $finance = OsisFinance::create($data);

        return $finance;
    }

    // --- Suggestions ---

    /**
     * @return Collection<int, OsisSuggestion>
     */
    public function getSuggestions(?string $schoolId = null): Collection
    {
        /** @var Collection<int, OsisSuggestion> $suggestions */
        $suggestions = OsisSuggestion::with('student')
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->latest()
            ->get();

        return $suggestions;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateSuggestion(OsisSuggestion $suggestion, array $data): OsisSuggestion
    {
        $suggestion->update($data);

        return $suggestion;
    }
}
