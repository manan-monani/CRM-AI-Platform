<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TaskService
{
    public function createTask(array $data, User $creator, ?Model $taskable = null): Task
    {
        $task = Task::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'taskable_type' => $taskable ? get_class($taskable) : null,
            'taskable_id' => $taskable ? $taskable->id : null,
            'priority' => $data['priority'] ?? 'medium',
            'due_date' => $data['due_date'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? null,
            'created_by' => $creator->id,
            'status' => 'pending',
        ]);

        if (isset($data['attachments']) && is_array($data['attachments'])) {
            foreach ($data['attachments'] as $file) {
                $task->addMedia($file)->toMediaCollection('default');
            }
        }

        return $task;
    }

    public function completeTask(Task $task): void
    {
        $this->updateTask($task, ['status' => 'completed']);
    }

    public function updateTask(Task $task, array $data): void
    {
        if (isset($data['status']) && $data['status'] === 'completed') {
            $data['completed_at'] = now();
        }

        $task->update($data);

        if (isset($data['attachments']) && is_array($data['attachments'])) {
            foreach ($data['attachments'] as $file) {
                $task->addMedia($file)->toMediaCollection('default');
            }
        }
    }
}