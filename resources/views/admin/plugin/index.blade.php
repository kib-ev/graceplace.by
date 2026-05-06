@extends('admin.layouts.app')

@section('content')
    <div class="row mt-3">
        <div class="col-12 col-lg-8">
            <h1 class="mb-3">Плагин</h1>

            @if(session('generated_now'))
                <div class="alert alert-success">
                    Новый API ключ успешно создан.
                </div>
            @endif

            <div class="card mb-3">
                <div class="card-header">Скачать плагин</div>
                <div class="card-body">
                    <a href="{{ route('admin.download.chrome-extension') }}" class="btn btn-primary">
                        Скачать плагин
                    </a>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">API ключ</div>
                <div class="card-body">
                    @if(!empty($apiToken))
                        <div class="input-group mb-3">
                            <input
                                type="text"
                                id="api-token-input"
                                class="form-control"
                                value="{{ $apiToken }}"
                                readonly
                            >
                            <button class="btn btn-outline-secondary" type="button" id="copy-api-token-btn">
                                Скопировать
                            </button>
                        </div>
                    @else
                        <p class="mb-2 text-danger">API ключ еще не создан.</p>
                    @endif

                    <form method="POST" action="{{ route('admin.plugin.regenerate-api-token') }}">
                        @csrf
                        <button type="submit" class="btn btn-warning">Создать новый API ключ</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            const copyButton = document.getElementById('copy-api-token-btn');
            const input = document.getElementById('api-token-input');

            if (!copyButton || !input) {
                return;
            }

            copyButton.addEventListener('click', async function () {
                try {
                    await navigator.clipboard.writeText(input.value);
                    copyButton.textContent = 'Скопировано';
                    setTimeout(function () {
                        copyButton.textContent = 'Скопировать';
                    }, 1200);
                } catch (e) {
                    input.select();
                    document.execCommand('copy');
                    copyButton.textContent = 'Скопировано';
                    setTimeout(function () {
                        copyButton.textContent = 'Скопировать';
                    }, 1200);
                }
            });
        })();
    </script>
@endsection
