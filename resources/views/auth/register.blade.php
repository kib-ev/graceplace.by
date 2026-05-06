@extends('admin.layouts.app')


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}" autocomplete="off">
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
                            <label class="col-md-4 col-form-label text-md-end">Категории</label>

                            <div class="col-md-6">
                                <div class="border rounded p-2">
                                    @foreach(($categoriesTree ?? collect()) as $parent)
                                        <div class="mb-2">
                                            <label class="form-check-label">
                                                <input class="form-check-input me-1" type="checkbox" name="category_ids[]" value="{{ $parent->id }}"
                                                    {{ in_array($parent->id, old('category_ids', [])) ? 'checked' : '' }}>
                                                {{ $parent->name }}
                                            </label>

                                            @foreach($parent->children as $child)
                                                <div class="ms-4 mt-1">
                                                    <label class="form-check-label">
                                                        <input class="form-check-input me-1" type="checkbox" name="category_ids[]" value="{{ $child->id }}"
                                                            {{ in_array($child->id, old('category_ids', [])) ? 'checked' : '' }}>
                                                        {{ $child->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>

                                @error('category_ids')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                                @error('category_ids.*')
                                <span class="invalid-feedback d-block" role="alert">
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
                            <label for="description" class="col-md-4 col-form-label text-md-end">Подробнее об оказываемых услугах</label>

                            <div class="col-md-6">
                                <textarea id="description" type="description"
                                          placeholder="Опишите услуги подробно"
                                          class="form-control @error('description') is-invalid @enderror"
                                          name="description" required>{{ old('description') }}</textarea>


                                @error('description')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    Регистрация
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

