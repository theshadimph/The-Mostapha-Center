<?php

namespace App\Http\Responses;

use App\Http\Responses\Concerns\RedirectsBasedOnRole;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;
use Symfony\Component\HttpFoundation\Response;

class VerifyEmailResponse implements VerifyEmailResponseContract
{
    use RedirectsBasedOnRole;

    public function toResponse($request): Response
    {
        return $request->wantsJson()
            ? new JsonResponse('', 204)
            : redirect()->intended($this->defaultRedirectPath($request).'?verified=1');
    }
}
