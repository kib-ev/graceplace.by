@extends('public.layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>История уведомлений</span>
                        <a href="{{ route('user.notices.show') }}" class="btn btn-sm btn-outline-primary">К непотвержденным</a>
                    </div>
                    <div class="card-body p-0">
                        @if($notices->count() === 0)
                            <p class="m-3 text-muted">Подтвержденных уведомлений пока нет.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead>
                                    <tr>
                                        <th style="width:40%">Заголовок</th>
                                        <th>Краткое содержание</th>
                                        <th style="width:180px">Подтверждено</th>
                                        <th style="width:180px">Истекает</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($notices as $notice)
                                        <tr>
                                            <td class="align-middle">{{ $notice->title }}</td>
                                            <td class="align-middle text-muted">
                                                {{ \Illuminate\Support\Str::limit($notice->body, 120) }}
                                            </td>
                                            <td class="align-middle">
                                                {{ optional($notice->pivot->confirmed_at)->format('d.m.Y H:i') }}
                                            </td>
                                            <td class="align-middle">
                                                {{ optional($notice->expires_at)->format('d.m.Y H:i') ?? '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-3">
                                {{ $notices->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
