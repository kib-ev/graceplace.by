@extends('public.layouts.app')

@section('master-menu')
    <!-- EMPTY -->
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <strong>{{ $notice->title }}</strong>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            {!! nl2br(e($notice->body)) !!}
                        </div>

                        @if($notice->created_at)
                            <div class="text-muted mb-3">
                                Дата создания: {{ $notice->created_at->format('d.m.Y H:i') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('user.notices.confirm') }}">
                            @csrf
                            <input type="hidden" name="notice_id" value="{{ $notice->id }}">
                            <button type="submit" class="btn btn-primary">
                                Ознакомлен(а)
                            </button>
                        </form>
                    </div>
                </div>

                <p class="mt-3 text-muted">
                    Для продолжения работы необходимо подтвердить ознакомление с сообщением.
                </p>
            </div>
        </div>
    </div>
@endsection
