<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Traits\ScopedByWorkspace;

/**
 * @property int $id
 * @property string $searchable_type
 * @property int $searchable_id
 * @property string $title
 * @property string|null $content
 * @property string|null $excerpt
 * @property array<string, mixed>|null $meta
 * @property string|null $url
 * @property string|null $type
 * @property int $relevance_score
 * @property int|null $workspace_id
 * @property-read \Illuminate\Database\Eloquent\Model $searchable
 */
class SearchIndex extends Model
{
    protected $table = 'core_search_indexes';


    use ScopedByWorkspace;


    protected $fillable = [
        'workspace_id',
        'searchable_type',
        'searchable_id',
        'title',
        'content',
        'excerpt',
        'meta',
        'url',
        'type',
        'relevance_score',
    ];

    protected $casts = [
        'meta' => 'array',
        'relevance_score' => 'integer',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<\Illuminate\Database\Eloquent\Model, $this>
     */
    public function searchable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array<string, mixed>  $data
     */
    public static function index($model, $data = []): self
    {
        $searchableType = get_class($model);
        /** @var int $searchableId */
        $searchableId = $model->getAttribute('id');
        
        // Get workspace ID from model if available, otherwise from context
        $workspaceId = $model->getAttribute('workspace_id') ?? $model->getAttribute('workspace_id') ?? \Illuminate\Support\Facades\Context::get('workspace_id');

        // Build searchable content
        /** @var string $title */
        $title = $data['title'] ?? $model->getAttribute('title') ?? $model->getAttribute('name') ?? '';
        /** @var string|null $content */
        $content = $data['content'] ?? $model->getAttribute('body') ?? $model->getAttribute('description') ?? '';
        $excerpt = $data['excerpt'] ?? $model->getAttribute('excerpt') ?? null;
        $url = $data['url'] ?? null;
        $type = $data['type'] ?? null;

        // Calculate relevance score
        $relevanceScore = self::calculateRelevance($title, (string) $content);

        return self::updateOrCreate(
            [
                'searchable_type' => $searchableType,
                'searchable_id' => $searchableId,
            ],
            [
                'workspace_id' => $workspaceId,
                'title' => $title,
                'content' => $content,
                'excerpt' => $excerpt,
                'meta' => $data['meta'] ?? [],
                'url' => $url,
                'type' => $type,
                'relevance_score' => $relevanceScore,
            ]
        );
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public static function remove($model): ?bool
    {
        $deleted = self::where('searchable_type', get_class($model))
            ->where('searchable_id', $model->getAttribute('id'))
            ->delete();

        return $deleted > 0;
    }

    protected static function calculateRelevance(string $title, string $content): int
    {
        $score = 0;
        $score += strlen($title) * 10;
        $score += strlen($content) * 1;

        return $score;
    }
}
