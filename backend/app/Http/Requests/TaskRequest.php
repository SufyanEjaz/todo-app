<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => $this->isMethod('post') ? 'required|string|min:3|max:255' : 'sometimes|string|min:3|max:255',
            'description' => $this->isMethod('post') ? 'required|min:3|string' : 'sometimes|min:3|string',
            'due_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:today',
            'priority' => 'nullable|in:Urgent,High,Normal,Low',
            'completed' => 'nullable|boolean',
            'archived' => 'nullable|boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpeg,png,svg,mp4,csv,txt,doc,docx',
            'attachmentsToDelete' => $this->isMethod('put') || $this->isMethod('patch') ? 'nullable|array' : 'prohibited',
            'attachmentsToDelete.*' => $this->isMethod('put') || $this->isMethod('patch') ? 'integer|exists:attachments,id' : 'prohibited',
        ];
    }
}
