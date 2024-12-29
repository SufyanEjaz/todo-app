<?php

namespace App\Repositories\Task;

use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Storage;

class TaskRepository implements TaskRepositoryInterface
{
    public function create(array $data, array $tags = [], array $attachments = [])
    {
        DB::beginTransaction();

        try {
            if (!empty($data['completed'])) {
                $data['completed_at'] = now();
            }
            
            if (!empty($data['archived'])) {
                $data['archived_at'] = now();
            }

            $task = auth()->user()->tasks()->create($data);

            // Handle tags: create if new, attach if existing
            if (!empty($tags)) {
                $tagIds = [];
                foreach ($tags as $tagName) {
                    // Check if the tag already exists; create only if it doesn’t
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                    $tagIds[] = $tag->id;
                }
                $task->tags()->sync($tagIds);
            }

            // Store attachments if provided
            foreach ($attachments as $file) {
                $path = $file->store("uploaded_files/" .  auth()->id() . "/attachments", 'public');
                $task->attachments()->create(['file_path' => $path, 'original_file_name' => $file->getClientOriginalName()]);
            }

            DB::commit();
            return $task->fresh(['tags', 'attachments']);
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception( $e->getMessage());
        }
    }

    public function find(int $id)
    {
        return auth()->user()->tasks()->with(['attachments', 'tags'])->findOrFail($id);
    }

    public function update(int $id, array $data, array $tags = [], array $attachments = [], array $attachmentsToDelete = [])
    {
        DB::beginTransaction();
        try {
            $task =  auth()->user()->tasks()->findOrFail($id);
            if (array_key_exists('completed', $data)) {
                if ($data['completed']) {
                    $data['completed_at'] = $data['completed_at'] ?? now();
                } else {
                    $data['completed_at'] = null;
                }
            }

            if (array_key_exists('archived', $data)) {
                if ($data['archived']) {
                    $data['archived_at'] = $data['archived_at'] ?? now();
                } else {
                    $data['archived_at'] = null;
                }
            }

            $task->update($data);
            
            if (!empty($tags)) {
                $tagIds = [];
                foreach ($tags as $tagName) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                    $tagIds[] = $tag->id;
                }
                $task->tags()->sync($tagIds);
            }
            
             // Handle deleting old attachments
            if (!empty($attachmentsToDelete)) {
                $attachmentsToRemove = $task->attachments()->whereIn('id', $attachmentsToDelete)->get();
                foreach ($attachmentsToRemove as $attachment) {
                    if ($attachment) {
                        Storage::disk('public')->delete($attachment->file_path);
                        $attachment->delete();
                    }
                }
            }

            foreach ($attachments as $file) {
                $path = $file->store("uploaded_files/" . auth()->id() . "/attachments", 'public');
                $task->attachments()->create(['file_path' => $path, 'original_file_name' => $file->getClientOriginalName()]);
            }

            DB::commit();
            return $task->fresh(['tags', 'attachments']);
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to update task: " . $e->getMessage());
        }
    }

    public function delete(int $id)
    {
        DB::beginTransaction();

        try {
            $task = $this->find($id);
            $task->delete();
            
            DB::commit();
            return response()->json(null, 204);
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to delete task: " . $e->getMessage());
        }
    }

    public function toggleComplete(int $id)
    {
        DB::beginTransaction();

        try {
            $task = $this->find($id);
            $newCompletedStatus = !$task->completed;
    
            $task->update([
                'completed' => $newCompletedStatus,
                'completed_at' => $newCompletedStatus ? Carbon::now() : null,
            ]);
            
            DB::commit();
            return $task->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to toggle task completion status: " . $e->getMessage());
        }
    }

    public function toggleArchiveRestore(int $id)
    {
        DB::beginTransaction();

        try {
            $task = $this->find($id);
            $isArchived = !$task->archived;
    
            $task->update([
                'archived' => $isArchived,
                'archived_at' => $isArchived ? Carbon::now() : null,
            ]);
            
            DB::commit();
            return $task->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to archive task: " . $e->getMessage());
        }
    }

    public function getAll(array $filters)
    {
        $query = auth()->user()->tasks()->with(['tags:name','attachments']);

        if (empty($filters['showArchived']) || $filters['showArchived'] == "false") {
            $query->whereNull('archived_at');
        }

        if (isset($filters['priority']) && in_array($filters['priority'], ['Urgent', 'High', 'Normal', 'Low'])) {
            $query->where('priority', $filters['priority']);
        }

        // Filter by due date range
        if (isset($filters['due_date_from']) && isset($filters['due_date_to'])) {
            $query->whereBetween('due_date', [$filters['due_date_from'], $filters['due_date_to']]);
        } elseif (isset($filters['due_date_from'])) {
            $query->where('due_date', '>=', $filters['due_date_from']);
        } elseif (isset($filters['due_date_to'])) {
            $query->where('due_date', '<=', $filters['due_date_to']);
        }

        // Filter by completed date range
        if (isset($filters['completed_date_from']) && isset($filters['completed_date_to'])) {
            $query->whereBetween('completed_at', [$filters['completed_date_from'], $filters['completed_date_to']]);
        } elseif (isset($filters['completed_date_from'])) {
            $query->where('completed_at', '>=', $filters['completed_date_from']);
        } elseif (isset($filters['completed_date_to'])) {
            $query->where('completed_at', '<=', $filters['completed_date_to']);
        }

        // Filter by archived date range
        if (isset($filters['archived_date_from']) && isset($filters['archived_date_to'])) {
            $query->whereBetween('archived_at', [$filters['archived_date_from'], $filters['archived_date_to']]);
        } elseif (isset($filters['archived_date_from'])) {
            $query->where('archived_at', '>=', $filters['archived_date_from']);
        } elseif (isset($filters['archived_date_to'])) {
            $query->where('archived_at', '<=', $filters['archived_date_to']);
        }

        // Search query for title or description
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }


        // Apply sorting if specified in the filters
        if (isset($filters['sort_by']) && isset($filters['sort_order'])) {
            $sortBy = $filters['sort_by'];
            $sortOrder = strtolower($filters['sort_order']) === 'desc' ? 'desc' : 'asc';

            // Allow sorting only on specific fields for security
            $allowedSortFields = ['title', 'description', 'due_date', 'created_at', 'completed_at', 'priority'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

        $perPage = 10;
        return $query->paginate($perPage);
    }
}
