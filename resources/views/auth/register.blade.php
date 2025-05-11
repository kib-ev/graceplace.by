@extends('admin.layouts.app')


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">Фамилия</label>

                            <div class="col-md-6">
                                <input id="lastName" type="text"
                                       placeholder="Ваша фамилия"
                                       class="form-control @error('last_name') is-invalid @enderror"
                                       name="last_name" value="{{ old('last_name') }}" required autocomplete="last_name">

                                @error('last_name')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">Имя</label>

                            <div class="col-md-6">
                                <input id="firstName" type="text"
                                       placeholder="Ваше имя"
                                       class="form-control @error('first_name') is-invalid @enderror"
                                       name="first_name" value="{{ old('first_name') }}" required autocomplete="first_name" autofocus>

                                @error('first_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>



                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">Отчество</label>

                            <div class="col-md-6">
                                <input id="patronymic" type="text"
                                       placeholder="Ваше отчество"
                                       class="form-control @error('patronymic') is-invalid @enderror"
                                       name="patronymic" value="{{ old('patronymic') }}" required autocomplete="patronymic">

                                @error('patronymic')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="phone" class="col-md-4 col-form-label text-md-end">Номер телефона</label>

                            <div class="col-md-6">
                                <input id="phone" type="phone"
                                       placeholder="+375..."
                                       class="form-control @error('phone') is-invalid @enderror"
                                       name="phone" value="{{ old('phone') }}" required autocomplete="phone">

                                @error('phone')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="instagram" class="col-md-4 col-form-label text-md-end">Ссылка на профиль Instagram</label>

                            <div class="col-md-6">
                                <input id="instagram" type="instagram"
                                       placeholder="https://instagram/your_name"
                                       class="form-control @error('instagram') is-invalid @enderror" name="instagram" value="{{ old('instagram') }}" required>

                                @error('instagram')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>


                        <div class="row mb-3">
                            <label for="description" class="col-md-4 col-form-label text-md-end">Ваша специализация</label>

                            <div class="col-md-6">
                                <textarea id="description" type="description"
                                          placeholder="Перечистите все оказываемые вами услуги"
                                          class="form-control @error('description') is-invalid @enderror"
                                          name="description" required>{{ old('description') }}</textarea>


                                @error('instagram')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>


{{--                        <div class="row mb-3">--}}
{{--                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>--}}

{{--                            <div class="col-md-6">--}}
{{--                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">--}}

{{--                                @error('password')--}}
{{--                                    <span class="invalid-feedback" role="alert">--}}
{{--                                        <strong>{{ $message }}</strong>--}}
{{--                                    </span>--}}
{{--                                @enderror--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                        <div class="row mb-3">--}}
{{--                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>--}}

{{--                            <div class="col-md-6">--}}
{{--                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">--}}
{{--                            </div>--}}
{{--                        </div>--}}

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
