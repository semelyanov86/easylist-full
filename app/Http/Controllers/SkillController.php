<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\SkillData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SkillController extends Controller
{
    /**
     * Поиск навыков пользователя по подстроке.
     */
    public function search(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $query = $request->query('q', '');

        $skills = $user->skills()
            ->where('title', 'like', '%' . $query . '%')
            ->orderBy('title')
            ->limit(20)
            ->get();

        return response()->json(SkillData::collect($skills));
    }

    /**
     * Создать новый навык для пользователя.
     */
    public function store(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        /** @var array{title: string} $data */
        $data = $request->validate([
            'title' => ['required', 'string', 'max:50'],
        ]);

        $skill = $user->skills()->firstOrCreate(['title' => $data['title']]);

        return response()->json(SkillData::from($skill), 201);
    }
}
