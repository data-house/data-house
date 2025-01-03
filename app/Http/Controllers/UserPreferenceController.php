<?php

namespace App\Http\Controllers;

use App\Models\Preference;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\Enum;

class UserPreferenceController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        /**
         * @var \Illuminate\Support\Collection
         */
        $cases = collect(Preference::cases())->keyBy->name;

        $validator = $this->getValidationFactory()->make(
            $request->all(),
            [
                'preference' => ['required', function (string $attribute, mixed $value, Closure $fail) use ($cases): void {
                    if(!$cases->has($value)){
                        $fail("Invalid preference.");
                    }
                    
                },],
                'value' => ['required'],
            ]
        )
        ->after(function($validator) use ($cases): void{

            $validated = $validator->validated();

            /**
             * @var \App\Models\Preference
             */
            $preference = $cases[$validated['preference']];

            if($preference->acceptableValues()->doesntContain($validated['value'])){
                $validator->errors()->add(
                    'value', 'Invalid preference value.'
                );
            }

        });


        $validated = $validator->validate();

        /**
         * @var \App\Models\Preference
         */
        $preference = $cases[$validated['preference']];

        $request->user()->setPreference($preference, $validated['value']);
        
        return redirect()->back();
    }
}
