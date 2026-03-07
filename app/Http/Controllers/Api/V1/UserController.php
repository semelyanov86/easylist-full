<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Data\UserData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Traits\JsonApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserController extends Controller
{
    use JsonApiResponses;

    /**
     * Просмотреть данные авторизованного пользователя.
     */
    public function show(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $data = UserData::from([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_premium' => $user->is_premium,
            'about_me' => $user->about_me,
            'created_at' => $user->created_at?->toISOString() ?? '',
        ]);

        $resource = new UserResource($data);

        return $this->jsonApiSingle($resource->toArray($request));
    }
}
