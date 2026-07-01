<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class DealService
{
    public function createDeal(array $data, Model $dealable, User $creator): Deal
    {
        $deal = Deal::create([
            'title' => $data['title'],
            'dealable_type' => get_class($dealable),
            'dealable_id' => $dealable->id,
            'value' => $data['value'] ?? 0,
            'stage' => $data['stage'] ?? 'new',
            'status' => 'open',
            'probability' => $data['probability'] ?? 0,
            'expected_close_date' => $data['expected_close_date'] ?? null,
            'description' => $data['description'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? null,
            'created_by' => $creator->id,
        ]);

        if (isset($data['attachments']) && is_array($data['attachments'])) {
            foreach ($data['attachments'] as $file) {
                $deal->addMedia($file)->toMediaCollection('default');
            }
        }

        return $deal;
    }

    public function updateDeal(Deal $deal, array $data): Deal
    {
        if (isset($data['dealable_type']) && isset($data['dealable_id'])) {
            $deal->dealable_type = $data['dealable_type'];
            $deal->dealable_id = $data['dealable_id'];
        }

        $deal->fill($data);
        $deal->save();

        if (isset($data['attachments']) && is_array($data['attachments'])) {
            foreach ($data['attachments'] as $file) {
                $deal->addMedia($file)->toMediaCollection('default');
            }
        }

        return $deal;
    }

    public function updateStage(Deal $deal, string $stage): void
    {
        $deal->update(['stage' => $stage]);

        if ($stage === 'won') {
            $this->markAsWon($deal);
        }
        elseif ($stage === 'lost') {
            $this->markAsLost($deal);
        }
    }

    public function markAsWon(Deal $deal): void
    {
        $deal->update([
            'status' => 'closed',
            'won_at' => now(),
            'probability' => 100,
        ]);
    }

    public function markAsLost(Deal $deal): void
    {
        $deal->update([
            'status' => 'closed',
            'lost_at' => now(),
            'probability' => 0,
        ]);
    }
}