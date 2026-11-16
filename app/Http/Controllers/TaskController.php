<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Mail\TaskCompleted;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(array_keys(Task::STATUSES))],
            'q' => ['nullable', 'string', 'max:150'],
        ]);
        $tasks = Task::active()
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->where('title', 'like', '%'.$term.'%'))
            ->orderBy('due_date')->orderBy('id')->paginate(9)->withQueryString();

        return view('tasks.index', compact('tasks', 'filters') + [
            'totalCount' => Task::active()->count(),
            'pendingCount' => Task::active()->where('status', 'pending')->count(),
            'doneCount' => Task::active()->where('status', 'done')->count(),
        ]);
    }

    public function create()
    {
        return view('tasks.create');
    }

    public function store(TaskRequest $request)
    {
        $task = Task::create($request->validated());

        return redirect()->route('tasks.show', $task)->with('status', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        abort_if($task->is_archived, 404);

        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        abort_if($task->is_archived, 404);

        return view('tasks.edit', compact('task'));
    }

    public function update(TaskRequest $request, Task $task)
    {
        $notified = DB::transaction(function () use ($request, $task) {
            // Read the latest status under a lock, not the model bound before the update.
            $current = Task::whereKey($task->id)->lockForUpdate()->firstOrFail();
            abort_if($current->is_archived, 404);
            $wasDone = $current->status === 'done';
            $current->fill($request->validated());
            $becameDone = ! $wasDone && $current->status === 'done';
            if ($becameDone && ! filter_var(config('tasks.notification_recipient'), FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages(['mail' => 'Set a valid TASK_NOTIFICATION_RECIPIENT address before completing tasks.']);
            }
            $current->save();
            if ($becameDone) {
                // Synchronous mail needs no worker. A transport error rolls back this update.
                Mail::to(config('tasks.notification_recipient'))->send(new TaskCompleted($current));
            }

            return $becameDone;
        });

        return redirect()->route('tasks.show', $task)->with('status', $notified
            ? 'Task completed. A completion email was generated.' : 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        abort_if($task->is_archived, 404);
        $task->is_archived = true;
        $task->save();

        return redirect()->route('tasks.index')->with('status', 'Task archived. Its record remains in the database.');
    }
}
