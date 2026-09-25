<?php

namespace App\Ai\Tools;

use App\Models\Announcement;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetRecentAnnouncements implements Tool
{
    /**
     * Get the description shown to the model.
     *
     * @return \Stringable|string
     */
    public function description(): Stringable|string
    {
        return 'Fetches the most recent school announcements to answer parent questions about school news.';
    }

    /**
     * Fetch the latest announcements for the model to quote.
     *
     * @param \Laravel\Ai\Tools\Request $request
     *
     * @return \Stringable|string
     */
    public function handle(Request $request): Stringable|string
    {
        $limit = min(
            $request['limit'] ?? config('constant.ai.announcements.default_limit'),
            config('constant.ai.announcements.max_limit'),
        );

        $announcements = Announcement::query()
            ->latest('created_at')
            ->limit($limit)
            ->get(['title', 'message', 'target_group', 'created_at']);

        if ($announcements->isEmpty()) {
            return 'No recent announcements found.';
        }

        return $announcements->map(function (Announcement $item) {
            $createdAt = $item->created_at?->format('M d') ?? '';

            return "- [{$createdAt}] {$item->title}: {$item->message}";
        })->implode("\n");
    }

    /**
     * Get the arguments the model may pass.
     *
     * Untyped because Ollama passes JsonSchemaTypeFactory.
     *
     * @param mixed $schema
     *
     * @return array<string, mixed>
     */
    public function schema($schema): array
    {
        return [
            'limit' => $schema->integer()
                ->min(config('constant.ai.announcements.min_limit'))
                ->max(config('constant.ai.announcements.max_limit'))
                ->required(),
        ];
    }
}
