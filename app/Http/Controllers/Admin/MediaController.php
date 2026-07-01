<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
            'collection' => 'nullable|string',
        ]);

        $modelClass = $request->model_type;
        $model = $modelClass::findOrFail($request->model_id);

        $media = $model->addMediaFromRequest('file')
            ->toMediaCollection($request->collection ?? 'default');

        return back()->with('success', 'File uploaded successfully.');
    }

    public function destroy(Media $media)
    {
        $model = $media->model;
        $media->delete();

        if ($model instanceof \App\Models\Task) {
            // Notify Super Admins and Assigned User
            $recipients = \App\Models\User::where('type', \App\Enums\UserType::SUPER_ADMIN)->get();

            if ($model->assignedToUser) {
                $recipients->push($model->assignedToUser);
            }

            // Filter out current user
            $recipients = $recipients->reject(function ($user) {
                return $user->id === auth()->id();
            });

            if ($recipients->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($recipients, new \App\Notifications\TaskNotification($model, 'media_deleted'));
            }
        }

        return back()->with('success', 'File deleted successfully.');
    }
}