<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTaskOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Loop through route parameters to identify a resource model (e.g., 'task' or 'tag')
        foreach ($request->route()->parameters() as $modelName => $id) {
            $modelClass = "App\\Models\\" . $modelName;
            if (class_exists($modelClass) && is_subclass_of($modelClass, 'Illuminate\Database\Eloquent\Model')) {
                // Retrieve the model instance by id
                $modelInstance = $modelClass::findOrFail($id);
                if($modelInstance->user_id != auth()->user()->id){
                    return response()->json(['error' => "Unauthorized access to this user."], 403);
                }
            } else {
                // Handle the case where the model class does not exist
                return response()->json(['error' => "Model {$modelName} does not exist."], 404);
            }
        }

        return $next($request);
    
    }
}
