<?php
/**
 * Created by PhpStorm.
 * User: kurisu
 * Date: 18-12-25
 * Time: 上午12:19
 */

namespace App\Transformers;

use App\Post;
use League\Fractal\TransformerAbstract;

class PostTransformer extends TransformerAbstract
{
    public function transform(Post $post)
    {
        return [
            'id'               => $post->id,
            'trace_id'         => $post->trace_id,
            'comment_trace_id' => $post->comment_trace_id,
            'created_at'       => $post->created_at->timestamp,
            'updated_at'       => $post->updated_at->timestamp,
        ];
    }
}