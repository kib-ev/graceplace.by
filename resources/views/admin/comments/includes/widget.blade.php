@if($model)
    <div class="widget">
        <div class="widget-title">
            <h4>{{ $title ?? 'Комментарии' }}</h4>
        </div>

        <div class="widget-body">
            @foreach($model->comments->where('type', $type)->load(['user']) as $comment)

                <div class="comment__top d-flex justify-content-between" style="color: #333; font-size: 0.7em;">
                    <div class="d-flex">
                        <span>{{ date('Y-m-d H:i', strtotime($comment->created_at)) }} - {{ $comment->user->nickname }}</span>
                    </div>

                    <div class="d-flex">
                        <form action="/admin/comments/{{ $comment->id }}" method="post">
                            @csrf
                            @method('delete')
                            <button type="submit" style="background: none; border: none;">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="show-comment">{{ $comment->text }}</div>

                <br>
            @endforeach

            <form action="/admin/comments" method="post">
                @csrf
                <input type="hidden" name="model_id" value="{{ $model->id }}">
                <input type="hidden" name="model_class" value="{{ get_class($model) }}">
                <input type="hidden" name="type" value="{{ $type }}">
                <textarea class="mt-3" name="text" style="width: 100%; height: 70px;"></textarea><br>
                <input type="submit" value="Добавить">
            </form>
        </div>
    </div>
@endif
