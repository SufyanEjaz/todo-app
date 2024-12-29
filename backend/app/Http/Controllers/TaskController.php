<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Repositories\Task\TaskRepositoryInterface;
use Illuminate\Http\Request;
/**
 * @group Task Management
 *
 * APIs for managing tasks.
 */
class TaskController extends Controller
{
    protected $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * List all tasks with filtering, sorting, and pagination.
     *
     * @queryParam showArchived boolean Optional. If true, includes archived tasks. Default: false. Example: false
     * @queryParam priority string Optional. Filter by priority level (Urgent, High, Normal, Low). Example: High
     * @queryParam due_date_from date Optional. Filter tasks due from this date (inclusive). Example: 2023-11-01
     * @queryParam due_date_to date Optional. Filter tasks due up to this date (inclusive). Example: 2023-11-30
     * @queryParam completed_date_from date Optional. Filter tasks completed from this date (inclusive). Example: 2023-11-01
     * @queryParam completed_date_to date Optional. Filter tasks completed up to this date (inclusive). Example: 2023-11-30
     * @queryParam archived_date_from date Optional. Filter tasks archived from this date (inclusive). Example: 2023-11-01
     * @queryParam archived_date_to date Optional. Filter tasks archived up to this date (inclusive). Example: 2023-11-30
     * @queryParam search string Optional. Search for tasks by title or description. Example: project
     * @queryParam sort_by string Optional. Sort by field (title, description, due_date, created_at, completed_at, priority). Example: due_date
     * @queryParam sort_order string Optional. Sort order (asc, desc). Default: asc. Example: asc
     * @queryParam page int Optional. Page number for pagination. Default: 1. Example: 2
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "My Task",
     *       "description": "This is a task description.",
     *       "completed": false,
     *       "priority": "High",
     *       "due_date": "2023-11-10",
     *       "tags": [
     *         {
     *           "name": "urgent"
     *         }
     *       ],
     *       "attachments": [
     *         {
     *           "id": 1,
     *           "file_path": "uploaded_files/1/attachments/sample.jpg"
     *         }
     *       ]
     *     }
     *   ],
     *   "links": {
     *     "first": "http://example.com/api/tasks?page=1",
     *     "last": "http://example.com/api/tasks?page=10",
     *     "prev": null,
     *     "next": "http://example.com/api/tasks?page=2"
     *   },
     *   "meta": {
     *     "current_page": 1,
     *     "from": 1,
     *     "last_page": 10,
     *     "links": [
     *       { "url": null, "label": "&laquo; Previous", "active": false },
     *       { "url": "http://example.com/api/tasks?page=1", "label": "1", "active": true },
     *       { "url": "http://example.com/api/tasks?page=2", "label": "2", "active": false },
     *       { "url": null, "label": "Next &raquo;", "active": false }
     *     ],
     *     "path": "http://example.com/api/tasks",
     *     "per_page": 10,
     *     "to": 10,
     *     "total": 100
     *   }
     * }
     */
    public function index(Request $request)
    {
        $tasks = $this->taskRepository->getAll($request->all());
        return response()->json($tasks, 200);
    }

    /**
     * Create a new task
     *
     * @bodyParam title string required The title of the task. Example: My Task
     * @bodyParam description string required The description of the task. Example: This is a task description.
     * @bodyParam due_date date The due date for the task. Example: 2023-11-10
     * @bodyParam priority string The priority of the task. Must be one of: Urgent, High, Normal, Low. Example: High
     * @bodyParam archived boolean Optional. Whether the task is archived. Example: false
     * @bodyParam tags array Optional. An array of tags associated with the task. Example: ["work", "important"]
     * @bodyParam tags.* string A tag associated with the task. Example: important
     * @bodyParam attachments array Optional. An array of files to attach to the task.
     * @bodyParam attachments.* file A file to attach to the task. Must be of type jpeg, png, svg, mp4, csv, txt, doc, or docx.
     *
     * @response 201 {
     *   "id": 1,
     *   "title": "My Task",
     *   "description": "This is a task description.",
     *   "due_date": "2023-11-10",
     *   "priority": "High",
     *   "completed": false,
     *   "archived": false,
     *   "tags": [
     *     {
     *       "id": 1,
     *       "name": "work"
     *     },
     *     {
     *       "id": 2,
     *       "name": "important"
     *     }
     *   ],
     *   "attachments": [
     *     {
     *       "id": 1,
     *       "file_path": "uploaded_files/1/attachments/file1.jpeg"
     *     }
     *   ],
     *   "created_at": "2023-11-06T00:00:00.000000Z",
     *   "updated_at": "2023-11-06T00:00:00.000000Z"
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "title": ["The title field is required."],
     *     "description": ["The description field is required."]
     *   }
     * }
     */
    public function store(TaskRequest $request)
    {
        try{
            $taskData = $request->only(['title', 'description', 'due_date', 'priority', 'completed', 'archived']);
            $tags = $request->input('tags', []);
            $attachments = $request->file('attachments', []);

            $task = $this->taskRepository->create($taskData, $tags, $attachments);
            
            return response()->json($task, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'message' => 'Failed to create new task'], 422);
        }
    }

    /**
     * Show a single task
     *
     * @urlParam id integer required The ID of the task. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "title": "My Task",
     *   "description": "This is a task description.",
     *   "due_date": "2023-11-10",
     *   "priority": "High",
     *   "completed": false
     * }
     * @response 404 {
     *   "error": "Task not found"
     * }
     */
    public function show($id)
    {
        $task = $this->taskRepository->find($id);
        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        return response()->json($task, 200);
    }


    /**
     * Update a task
     *
     * @urlParam id integer required The ID of the task. Example: 1
     * @bodyParam title string The title of the task. Example: Updated Task Title
     * @bodyParam description string The description of the task. Example: Updated task description.
     * @bodyParam due_date date The due date for the task. Example: 2023-11-15
     * @bodyParam priority string The priority of the task. Must be one of: Urgent, High, Normal, Low. Example: Normal
     * @bodyParam completed boolean Optional. Whether the task is completed. Example: true
     * @bodyParam archived boolean Optional. Whether the task is archived. Example: false
     * @bodyParam tags array Optional. An array of tags to associate with the task. Example: ["work", "urgent"]
     * @bodyParam tags.* string A tag to associate with the task. Example: urgent
     * @bodyParam attachments array Optional. An array of files to attach to the task.
     * @bodyParam attachments.* file A file to attach to the task. Must be of type jpeg, png, svg, mp4, csv, txt, doc, or docx.
     *
     * @response 200 {
     *   "id": 1,
     *   "title": "Updated Task Title",
     *   "description": "Updated task description.",
     *   "due_date": "2023-11-15",
     *   "priority": "Normal",
     *   "completed": true,
     *   "archived": false,
     *   "tags": [
     *     {
     *       "id": 1,
     *       "name": "work"
     *     },
     *     {
     *       "id": 2,
     *       "name": "urgent"
     *     }
     *   ],
     *   "attachments": [
     *     {
     *       "id": 1,
     *       "file_path": "uploaded_files/1/attachments/file1.jpeg"
     *     }
     *   ],
     *   "created_at": "2023-11-06T00:00:00.000000Z",
     *   "updated_at": "2023-11-06T00:00:00.000000Z"
     * }
     *
     * @response 404 {
     *   "error": "Task not found or update failed"
     * }
     */
    public function update(TaskRequest $request, $id)
    {
        try {
            $taskData = $request->only(['title', 'description', 'due_date', 'priority', 'completed', 'archived']);
            $tags = $request->input('tags', []);
            $attachments = $request->file('attachments', []);
            $attachmentsToDelete = $request->input('attachmentsToDelete', []);

            $task = $this->taskRepository->update($id, $taskData, $tags, $attachments, $attachmentsToDelete);

            if (!$task) {
                return response()->json(['error' => 'Task not found'], 404);
            }
            return response()->json($task, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Task not found or update failed'], 404);
        }
    }

    /**
     * Delete a task
     *
     * @urlParam id integer required The ID of the task. Example: 1
     *
     * @response 204 null
     * @response 404 {
     *   "error": "Task not found or deletion failed"
     * }
     */
    public function destroy($id)
    {
        try {
            $this->taskRepository->delete($id);
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Task not found or deletion failed'], 404);
        }
    }


    /**
     * Mark a task as complete
     *
     * @urlParam id integer required The ID of the task. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "title": "My Task",
     *   "completed": true,
     *   "completed_at": "2023-11-06T12:00:00Z"
     * }
     * @response 404 {
     *   "error": "Task not found"
     * }
     */
    public function toggleComplete($id)
    {
        try {
            $task = $this->taskRepository->toggleComplete($id);
            return response()->json($task, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Task not found'], 404);
        }
    }

    /**
     * Archive a task
     *
     * @urlParam id integer required The ID of the task. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "title": "My Task",
     *   "archived": true,
     *   "archived_at": "2023-11-06T12:00:00Z"
     * }
     * @response 404 {
     *   "error": "Task not found"
     * }
     */
    public function toggleArchiveRestore($id)
    {
        try {
            $task = $this->taskRepository->toggleArchiveRestore($id);
            return response()->json($task, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Task not found'], 404);
        }
    }
}
