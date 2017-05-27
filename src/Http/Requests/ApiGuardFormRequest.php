<?php

namespace Chrisbjr\ApiGuard\Http\Requests;

use EllipseSynergie\ApiResponse\Laravel\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use League\Fractal\Manager;

class ApiGuardFormRequest extends FormRequest
{
    public function expectsJson()
    {
        return true;
    }

    /**
     * Format the errors from the given Validator instance.
     *
     * @param  \Illuminate\Contracts\Validation\Validator $validator
     * @return array
     */
    protected function formatErrors(Validator $validator)
    {
        return $validator->getMessageBag()->toArray();
    }

    public function response(array $errors)
    {
        $response = new Response(new Manager());

        return $response->errorUnprocessable($errors);
    }
}
